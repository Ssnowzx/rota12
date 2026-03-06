<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\{ControllerBase, Auth, ACL, AlertSystem, CSRF};
use App\Models\{AlertModel, AuditModel};

/**
 * Alert Dashboard Controller
 *
 * Provides views for monitoring security alerts in real-time.
 * Accessible only to super-admins with proper permissions.
 */
class AlertDashboardController extends ControllerBase
{
    private const PERM = 'core.alerts.view';

    /**
     * GET /administrator/alerts
     * Main alerts dashboard with recent alerts and statistics.
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;

        // Get paginated alerts with optional filters
        $filters = [];
        if (!empty($_GET['event'])) {
            $filters['event'] = trim($_GET['event']);
        }
        if (!empty($_GET['severity'])) {
            $filters['severity'] = trim($_GET['severity']);
        }
        if (!empty($_GET['ip'])) {
            $filters['ip'] = trim($_GET['ip']);
        }

        $pager = AlertModel::listPaginated($page, $perPage, $filters);

        // Get statistics (last 24 hours)
        $statsBySeverity = AlertModel::countBySeverity(1440);
        $statsByEvent = AlertModel::countByEvent(1440);
        $topIPs = AlertModel::topSuspiciousIPs(10, 1440);
        $topUsers = AlertModel::topSuspiciousUsers(10, 1440);

        // Calculate totals
        $totalAlerts24h = array_sum($statsBySeverity);

        $this->renderAdmin('alerts/index', [
            'alerts'           => $pager['data'],
            'total'            => $pager['total'],
            'pages'            => $pager['pages'],
            'page'             => $pager['current'],
            'perPage'          => $perPage,
            'filters'          => $filters,
            'statsBySeverity'  => $statsBySeverity,
            'statsByEvent'     => $statsByEvent,
            'topIPs'           => $topIPs,
            'topUsers'         => $topUsers,
            'totalAlerts24h'   => $totalAlerts24h,
        ]);
    }

    /**
     * GET /administrator/alerts/:id
     * View detailed information about a specific alert.
     *
     * @param int $id Alert ID
     */
    public function show(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $id = (int) ($params['id'] ?? 0);
        $alert = AlertModel::find($id);
        if ($alert === null) {
            $this->flash('error', 'Alert not found.');
            $this->redirect('/administrator/alerts');
        }

        // Decode context JSON
        $context = [];
        if (!empty($alert['context_json'])) {
            try {
                $context = json_decode(
                    (string) $alert['context_json'],
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (\Throwable $e) {
                error_log('[AlertDashboard] Failed to decode context: ' . $e->getMessage());
            }
        }

        // Get related alerts from same IP
        $relatedByIP = AlertModel::byIP((string) $alert['ip_address'], 10);

        // Get related alerts from same user
        $relatedByUser = [];
        if (!empty($alert['admin_user_id'])) {
            $relatedByUser = AlertModel::byUser((int) $alert['admin_user_id'], 10);
        }

        $this->renderAdmin('alerts/show', [
            'alert'         => $alert,
            'context'       => $context,
            'relatedByIP'   => $relatedByIP,
            'relatedByUser' => $relatedByUser,
        ]);
    }

    /**
     * POST /administrator/alerts/:id/block-ip
     * Block an IP address (add to blacklist).
     *
     * @param int $id Alert ID
     */
    public function blockIP(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('core.alerts.manage');
        CSRF::check();

        $id = (int) ($params['id'] ?? 0);
        $alert = AlertModel::find($id);
        if ($alert === null) {
            $this->jsonError('Alert not found', 404);
        }

        $ip = $alert['ip_address'] ?? '';
        if ($ip === '' || $ip === 'unknown') {
            $this->jsonError('Cannot block invalid IP address', 400);
        }

        // Block the IP
        if (AlertSystem::blockIP($ip)) {
            AuditModel::log(
                Auth::adminId(),
                'security_action',
                'admin_ip_blacklist',
                0,
                ['ip' => $ip, 'alert_id' => $id, 'action' => 'block_ip'],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );
            $this->flash('success', "IP {$ip} has been blocked.");
        } else {
            $this->flash('error', 'Failed to block IP address.');
        }

        $this->redirect('/administrator/alerts/' . $id);
    }

    /**
     * POST /administrator/alerts/:id/disable-user
     * Disable an admin user account (security lockdown).
     *
     * @param int $id Alert ID
     */
    public function disableUser(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('core.alerts.manage');
        CSRF::check();

        $id = (int) ($params['id'] ?? 0);
        $alert = AlertModel::find($id);
        if ($alert === null) {
            $this->jsonError('Alert not found', 404);
        }

        $userId = $alert['admin_user_id'] ?? null;
        if ($userId === null) {
            $this->jsonError('No user associated with this alert', 400);
        }

        // Cannot disable super-admin accounts this way
        if (ACL::can('core.super')) {
            // Only allow if we're disabling a non-super-admin
            $currentUserId = Auth::adminId();
            if ($userId === $currentUserId) {
                $this->jsonError('Cannot disable your own account', 400);
            }
        }

        // Disable the user
        if (AlertSystem::disableUser(
            (int) $userId,
            'Disabled via AlertDashboard due to alert ID ' . $id
        )) {
            AuditModel::log(
                Auth::adminId(),
                'security_action',
                'admin_users',
                $userId,
                ['user_id' => $userId, 'alert_id' => $id, 'action' => 'disable_user', 'reason' => 'Security alert'],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );
            $this->flash('success', 'User account has been disabled.');
        } else {
            $this->flash('error', 'Failed to disable user account.');
        }

        $this->redirect('/administrator/alerts/' . $id);
    }

    /**
     * GET /administrator/alerts/filter
     * Advanced filtering with more options.
     */
    public function filter(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;

        // Build filters from query string
        $filters = [];
        if (!empty($_GET['event'])) {
            $filters['event'] = trim($_GET['event']);
        }
        if (!empty($_GET['severity'])) {
            $filters['severity'] = trim($_GET['severity']);
        }
        if (!empty($_GET['ip'])) {
            $filters['ip'] = trim($_GET['ip']);
        }
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = (int) $_GET['user_id'];
        }

        $pager = AlertModel::listPaginated($page, $perPage, $filters);

        $this->renderAdmin('alerts/filter', [
            'alerts'  => $pager['data'],
            'total'   => $pager['total'],
            'pages'   => $pager['pages'],
            'page'    => $pager['current'],
            'filters' => $filters,
        ]);
    }

    /**
     * GET /administrator/alerts/by-event/:event
     * View alerts filtered by event type.
     *
     * @param string $event Event type
     */
    public function byEvent(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $event = trim((string) ($params['event'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;

        // Get alerts for this event type
        $allAlerts = AlertModel::byEvent($event, 1000);
        $total = count($allAlerts);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $alerts = array_slice($allAlerts, $offset, $perPage);

        $this->renderAdmin('alerts/by-event', [
            'event'   => $event,
            'alerts'  => $alerts,
            'total'   => $total,
            'pages'   => $pages,
            'page'    => $page,
            'perPage' => $perPage,
        ]);
    }

    /**
     * GET /administrator/alerts/by-ip/:ip
     * View alerts from a specific IP address.
     *
     * @param string $ip IP address
     */
    public function byIP(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $ip = trim((string) ($params['ip'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;

        // Validate IP format
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->flash('error', 'Invalid IP address format.');
            $this->redirect('/administrator/alerts');
        }

        $allAlerts = AlertModel::byIP($ip, 1000);
        $total = count($allAlerts);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $alerts = array_slice($allAlerts, $offset, $perPage);

        // Check if blacklisted
        $isBlacklisted = AlertModel::isIPBlacklisted($ip);

        $this->renderAdmin('alerts/by-ip', [
            'ip'             => $ip,
            'alerts'         => $alerts,
            'total'          => $total,
            'pages'          => $pages,
            'page'           => $page,
            'isBlacklisted'  => $isBlacklisted,
        ]);
    }

    /**
     * GET /administrator/alerts/by-user/:userId
     * View alerts related to a specific user.
     *
     * @param int $userId User ID
     */
    public function byUser(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $userId = (int) ($params['userId'] ?? 0);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;

        $allAlerts = AlertModel::byUser($userId, 1000);
        $total = count($allAlerts);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $alerts = array_slice($allAlerts, $offset, $perPage);

        $this->renderAdmin('alerts/by-user', [
            'userId'  => $userId,
            'alerts'  => $alerts,
            'total'   => $total,
            'pages'   => $pages,
            'page'    => $page,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    /**
     * Return a JSON error response.
     */
    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
