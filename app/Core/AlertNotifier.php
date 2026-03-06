<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Alert Notifier
 *
 * Handles sending notifications for triggered alerts via:
 *   - Email (instantaneous to super-admin)
 *   - File logging (storage/logs/alerts.log)
 *   - Database (admin_alerts table)
 *
 * Can be called synchronously or queued for async processing.
 */
final class AlertNotifier
{
    // -------------------------------------------------------------------------
    // Queue Management
    // -------------------------------------------------------------------------

    /**
     * Queue an alert notification for processing.
     * Notifications are stored and processed by cron/queue system.
     *
     * @param int    $alertId
     * @param string $event
     * @param string $severity
     * @param array  $context
     */
    public static function queue(int $alertId, string $event, string $severity, array $context): void
    {
        try {
            DB::execute(
                'INSERT INTO alert_notification_queue
                    (alert_id, event, severity, context_json, status, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())',
                [
                    $alertId,
                    $event,
                    $severity,
                    json_encode($context, JSON_UNESCAPED_UNICODE),
                    'pending',
                ]
            );
        } catch (\Throwable $e) {
            error_log('[AlertNotifier] Failed to queue notification: ' . $e->getMessage());
            // Fall back to immediate sending
            self::sendImmediate($alertId, $event, $severity, $context);
        }
    }

    /**
     * Process all pending notifications in the queue.
     * Should be called by a cron job or background worker.
     *
     * @param int $limit Maximum notifications to process in one call
     * @return int Number of notifications processed
     */
    public static function processPending(int $limit = 50): int
    {
        try {
            $pending = DB::select(
                'SELECT * FROM alert_notification_queue
                 WHERE status = ? ORDER BY created_at ASC LIMIT ?',
                ['pending', $limit]
            );

            $processed = 0;
            foreach ($pending as $notification) {
                try {
                    $context = json_decode(
                        (string) ($notification['context_json'] ?? '{}'),
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );

                    self::sendImmediate(
                        (int) $notification['alert_id'],
                        (string) $notification['event'],
                        (string) $notification['severity'],
                        $context
                    );

                    // Mark as sent
                    DB::execute(
                        'UPDATE alert_notification_queue SET status = ?, sent_at = NOW()
                         WHERE id = ?',
                        ['sent', (int) $notification['id']]
                    );

                    $processed++;
                } catch (\Throwable $e) {
                    error_log(
                        '[AlertNotifier] Failed to process notification ' .
                        $notification['id'] . ': ' . $e->getMessage()
                    );

                    // Mark as failed
                    DB::execute(
                        'UPDATE alert_notification_queue SET status = ?, error_message = ?
                         WHERE id = ?',
                        ['failed', $e->getMessage(), (int) $notification['id']]
                    );
                }
            }

            return $processed;
        } catch (\Throwable $e) {
            error_log('[AlertNotifier] Failed to process pending notifications: ' . $e->getMessage());
            return 0;
        }
    }

    // -------------------------------------------------------------------------
    // Notification Sending
    // -------------------------------------------------------------------------

    /**
     * Send notification immediately (used by queue processor or direct calls).
     */
    private static function sendImmediate(
        int $alertId,
        string $event,
        string $severity,
        array $context
    ): void {
        // Send email to super-admins
        self::sendEmail($alertId, $event, $severity, $context);

        // Log to alerts log
        self::logAlert($alertId, $event, $severity, $context);
    }

    /**
     * Send email notification to super-admin users.
     */
    private static function sendEmail(
        int $alertId,
        string $event,
        string $severity,
        array $context
    ): void {
        try {
            // Get all super-admin users
            $superAdmins = DB::select(
                'SELECT DISTINCT au.id, au.email, au.name
                 FROM admin_users au
                 JOIN admin_user_roles aur ON aur.admin_user_id = au.id
                 JOIN admin_roles ar ON ar.id = aur.admin_role_id
                 WHERE ar.key = ? AND au.is_active = 1',
                ['super_admin']
            );

            if (empty($superAdmins)) {
                error_log('[AlertNotifier] No super-admin users found for email notification');
                return;
            }

            // Build email
            $subject = self::buildEmailSubject($event, $severity);
            $body = self::buildEmailBody($alertId, $event, $severity, $context);

            // Send to each super-admin
            foreach ($superAdmins as $admin) {
                $email = $admin['email'] ?? '';
                if ($email === '') {
                    continue;
                }

                self::sendMailMessage(
                    $email,
                    $admin['name'] ?? 'Admin',
                    $subject,
                    $body
                );
            }
        } catch (\Throwable $e) {
            error_log('[AlertNotifier] Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Send a mail message using PHP mail() function.
     */
    private static function sendMailMessage(
        string $recipientEmail,
        string $recipientName,
        string $subject,
        string $body
    ): bool {
        try {
            $siteEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@rota12.local';
            $siteName = getenv('APP_NAME') ?: 'Rota12';

            $headers = [
                'From: ' . $siteName . ' <' . $siteEmail . '>',
                'Reply-To: ' . $siteEmail,
                'Content-Type: text/html; charset=UTF-8',
                'X-Priority: 1 (Highest)',
            ];

            return mail(
                $recipientEmail,
                $subject,
                $body,
                implode("\r\n", $headers)
            );
        } catch (\Throwable $e) {
            error_log('[AlertNotifier] mail() failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log alert details to alerts.log file.
     */
    private static function logAlert(
        int $alertId,
        string $event,
        string $severity,
        array $context
    ): void {
        try {
            $logPath = rtrim(dirname(__DIR__, 2), '/') . '/storage/logs/alerts.log';

            // Ensure directory exists
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $timestamp = date('Y-m-d H:i:s');
            $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE);
            $ip = $context['ip'] ?? 'unknown';
            $userId = $context['user_id'] ?? 'null';

            $logMessage = sprintf(
                "[%s] ALERT [alert_id=%d] [severity=%s] [event=%s] [user_id=%s] [ip=%s]\nContext: %s\n\n",
                $timestamp,
                $alertId,
                strtoupper($severity),
                $event,
                $userId,
                $ip,
                $contextJson
            );

            error_log($logMessage, 3, $logPath);
        } catch (\Throwable $e) {
            error_log('[AlertNotifier] Failed to log alert: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Email Template Building
    // -------------------------------------------------------------------------

    /**
     * Build email subject based on event and severity.
     */
    private static function buildEmailSubject(string $event, string $severity): string
    {
        $severityLabel = strtoupper($severity);
        $eventLabel = self::friendlyEventName($event);
        $siteName = getenv('APP_NAME') ?: 'Rota12';

        return "[{$severityLabel}] {$eventLabel} - {$siteName} Security Alert";
    }

    /**
     * Build email body with alert details.
     */
    private static function buildEmailBody(
        int $alertId,
        string $event,
        string $severity,
        array $context
    ): string {
        $timestamp = date('Y-m-d H:i:s');
        $eventName = self::friendlyEventName($event);
        $ip = htmlspecialchars($context['ip'] ?? 'unknown', ENT_QUOTES, 'UTF-8');
        $userId = $context['user_id'] ?? 'N/A';
        $appUrl = getenv('APP_URL') ?: 'http://localhost';

        // Color code severity
        $severityColor = match (strtolower($severity)) {
            'critical' => '#DC3545',  // red
            'high' => '#FFC107',      // orange
            'medium' => '#17A2B8',    // cyan
            'low' => '#28A745',       // green
            default => '#6C757D',     // gray
        };

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {$severityColor}; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #e0e0e0; border-radius: 0 0 5px 5px; }
        .detail { margin: 15px 0; }
        .label { font-weight: bold; color: #555; display: inline-block; width: 120px; }
        .value { color: #333; font-family: monospace; }
        .action-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: {$severityColor}; color: white; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #e0e0e0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ {$eventName} Alert</h1>
            <p>Severity: <strong>{$severity}</strong></p>
        </div>

        <div class="content">
            <p>A critical security alert has been triggered on your Rota12 installation:</p>

            <div class="detail">
                <span class="label">Alert ID:</span>
                <span class="value">{$alertId}</span>
            </div>

            <div class="detail">
                <span class="label">Event:</span>
                <span class="value">{$event}</span>
            </div>

            <div class="detail">
                <span class="label">Timestamp:</span>
                <span class="value">{$timestamp}</span>
            </div>

            <div class="detail">
                <span class="label">Source IP:</span>
                <span class="value">{$ip}</span>
            </div>

            <div class="detail">
                <span class="label">User ID:</span>
                <span class="value">{$userId}</span>
            </div>
HTML;

        // Add context details if present
        if (!empty($context)) {
            $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $contextJson = htmlspecialchars($contextJson, ENT_QUOTES, 'UTF-8');
            $html .= <<<HTML

            <div class="detail">
                <span class="label">Details:</span>
                <pre style="background: white; padding: 10px; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">{$contextJson}</pre>
            </div>
HTML;
        }

        $html .= <<<HTML

            <a href="{$appUrl}/administrator/alerts" class="action-link">View Alert Dashboard</a>
        </div>

        <div class="footer">
            <p>This is an automated security alert. Do not reply to this email.</p>
            <p>Alert sent at {$timestamp} UTC</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Convert event constant to friendly name.
     */
    private static function friendlyEventName(string $event): string
    {
        return match ($event) {
            'login_failed' => 'Failed Login Attempt',
            'admin_delete' => 'Critical Data Deletion',
            'role_change' => 'Permission Change',
            'acl_denied' => 'Access Denied',
            'file_upload_suspicious' => 'Suspicious File Upload',
            'config_change' => 'System Configuration Change',
            default => ucfirst(str_replace('_', ' ', $event)),
        };
    }
}
