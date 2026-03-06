<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\{ModelBase, DB};

/**
 * Alert Model
 *
 * Manages admin_alerts table - stores triggered security alerts
 * with severity levels, IP addresses, and context data.
 */
class AlertModel extends ModelBase
{
    protected static string $table = 'admin_alerts';

    // -------------------------------------------------------------------------
    // Retrieval Methods
    // -------------------------------------------------------------------------

    /**
     * Get recent alerts with optional admin user info joined.
     *
     * @param int $limit Number of alerts to retrieve
     * @return array<int, array<string, mixed>>
     */
    public static function recent(int $limit = 50): array
    {
        return DB::select(
            'SELECT aa.*, au.name AS admin_name, au.email AS admin_email
             FROM admin_alerts aa
             LEFT JOIN admin_users au ON au.id = aa.admin_user_id
             ORDER BY aa.triggered_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    /**
     * Get alerts by severity level.
     *
     * @param string $severity (critical, high, medium, low)
     * @param int    $limit
     * @return array<int, array<string, mixed>>
     */
    public static function bySeverity(string $severity, int $limit = 50): array
    {
        return DB::select(
            'SELECT aa.*, au.name AS admin_name
             FROM admin_alerts aa
             LEFT JOIN admin_users au ON au.id = aa.admin_user_id
             WHERE aa.severity = ?
             ORDER BY aa.triggered_at DESC
             LIMIT ?',
            [$severity, $limit]
        );
    }

    /**
     * Get alerts by event type.
     *
     * @param string $event (login_failed, admin_delete, role_change, etc)
     * @param int    $limit
     * @return array<int, array<string, mixed>>
     */
    public static function byEvent(string $event, int $limit = 50): array
    {
        return DB::select(
            'SELECT aa.*, au.name AS admin_name
             FROM admin_alerts aa
             LEFT JOIN admin_users au ON au.id = aa.admin_user_id
             WHERE aa.event = ?
             ORDER BY aa.triggered_at DESC
             LIMIT ?',
            [$event, $limit]
        );
    }

    /**
     * Get alerts from a specific IP address.
     *
     * @param string $ipAddress
     * @param int    $limit
     * @return array<int, array<string, mixed>>
     */
    public static function byIP(string $ipAddress, int $limit = 50): array
    {
        return DB::select(
            'SELECT aa.*, au.name AS admin_name
             FROM admin_alerts aa
             LEFT JOIN admin_users au ON au.id = aa.admin_user_id
             WHERE aa.ip_address = ?
             ORDER BY aa.triggered_at DESC
             LIMIT ?',
            [$ipAddress, $limit]
        );
    }

    /**
     * Get alerts from a specific user (admin or target).
     *
     * @param int $userId
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public static function byUser(int $userId, int $limit = 50): array
    {
        return DB::select(
            'SELECT aa.*, au.name AS admin_name
             FROM admin_alerts aa
             LEFT JOIN admin_users au ON au.id = aa.admin_user_id
             WHERE aa.admin_user_id = ?
             ORDER BY aa.triggered_at DESC
             LIMIT ?',
            [$userId, $limit]
        );
    }

    /**
     * Get alerts within a date range.
     *
     * @param string $startDate (Y-m-d or Y-m-d H:i:s)
     * @param string $endDate   (Y-m-d or Y-m-d H:i:s)
     * @param int    $limit
     * @return array<int, array<string, mixed>>
     */
    public static function byDateRange(string $startDate, string $endDate, int $limit = 100): array
    {
        return DB::select(
            'SELECT aa.*, au.name AS admin_name
             FROM admin_alerts aa
             LEFT JOIN admin_users au ON au.id = aa.admin_user_id
             WHERE aa.triggered_at BETWEEN ? AND ?
             ORDER BY aa.triggered_at DESC
             LIMIT ?',
            [$startDate, $endDate, $limit]
        );
    }

    /**
     * Paginated list of alerts with optional filters.
     *
     * @param int          $page
     * @param int          $perPage
     * @param array{
     *     event?: string,
     *     severity?: string,
     *     ip?: string,
     *     user_id?: int,
     * }  $filters
     * @return array{data: list<array<string,mixed>>, total: int, pages: int, current: int}
     */
    public static function listPaginated(
        int $page = 1,
        int $perPage = 30,
        array $filters = []
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        // Build WHERE clause
        $whereClauses = [];
        $params = [];

        if (!empty($filters['event'])) {
            $whereClauses[] = 'aa.event = ?';
            $params[] = $filters['event'];
        }
        if (!empty($filters['severity'])) {
            $whereClauses[] = 'aa.severity = ?';
            $params[] = $filters['severity'];
        }
        if (!empty($filters['ip'])) {
            $whereClauses[] = 'aa.ip_address = ?';
            $params[] = $filters['ip'];
        }
        if (!empty($filters['user_id'])) {
            $whereClauses[] = 'aa.admin_user_id = ?';
            $params[] = $filters['user_id'];
        }

        $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        // Count total
        $countSql = 'SELECT COUNT(*) AS cnt FROM admin_alerts aa ' . $whereClause;
        $countRow = DB::selectOne($countSql, $params);
        $total = (int) ($countRow['cnt'] ?? 0);
        $pages = (int) ceil($total / $perPage);

        // Fetch paginated data
        $dataSql = 'SELECT aa.*, au.name AS admin_name, au.email AS admin_email
                    FROM admin_alerts aa
                    LEFT JOIN admin_users au ON au.id = aa.admin_user_id
                    ' . $whereClause . '
                    ORDER BY aa.triggered_at DESC
                    LIMIT ? OFFSET ?';
        $params[] = $perPage;
        $params[] = $offset;

        $data = DB::select($dataSql, $params);

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => $pages,
            'current' => $page,
        ];
    }

    // -------------------------------------------------------------------------
    // Analysis Methods
    // -------------------------------------------------------------------------

    /**
     * Count alerts by severity in a time window.
     *
     * @param int $minutesAgo Look back this many minutes (default: 24 hours)
     * @return array{critical: int, high: int, medium: int, low: int}
     */
    public static function countBySeverity(int $minutesAgo = 1440): array
    {
        $results = DB::select(
            'SELECT severity, COUNT(*) AS cnt
             FROM admin_alerts
             WHERE triggered_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
             GROUP BY severity',
            [$minutesAgo]
        );

        $counts = [
            'critical' => 0,
            'high'     => 0,
            'medium'   => 0,
            'low'      => 0,
        ];

        foreach ($results as $row) {
            $severity = $row['severity'] ?? '';
            if (isset($counts[$severity])) {
                $counts[$severity] = (int) $row['cnt'];
            }
        }

        return $counts;
    }

    /**
     * Count alerts by event type in a time window.
     *
     * @param int $minutesAgo
     * @return array<string, int>
     */
    public static function countByEvent(int $minutesAgo = 1440): array
    {
        $results = DB::select(
            'SELECT event, COUNT(*) AS cnt
             FROM admin_alerts
             WHERE triggered_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
             GROUP BY event
             ORDER BY cnt DESC',
            [$minutesAgo]
        );

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['event'] ?? ''] = (int) $row['cnt'];
        }

        return $counts;
    }

    /**
     * Get most suspicious IPs (with most alerts).
     *
     * @param int $limit
     * @param int $minutesAgo
     * @return array<int, array{ip: string, count: int}>
     */
    public static function topSuspiciousIPs(int $limit = 10, int $minutesAgo = 1440): array
    {
        $results = DB::select(
            'SELECT ip_address AS ip, COUNT(*) AS cnt
             FROM admin_alerts
             WHERE triggered_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
             GROUP BY ip_address
             ORDER BY cnt DESC
             LIMIT ?',
            [$minutesAgo, $limit]
        );

        return array_map(fn($row) => [
            'ip'    => $row['ip'] ?? '',
            'count' => (int) ($row['cnt'] ?? 0),
        ], $results);
    }

    /**
     * Get most suspicious users (with most alerts against them).
     *
     * @param int $limit
     * @param int $minutesAgo
     * @return array<int, array{id: int, name: string, count: int}>
     */
    public static function topSuspiciousUsers(int $limit = 10, int $minutesAgo = 1440): array
    {
        $results = DB::select(
            'SELECT au.id, au.name, COUNT(*) AS cnt
             FROM admin_alerts aa
             LEFT JOIN admin_users au ON au.id = aa.admin_user_id
             WHERE aa.triggered_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
               AND aa.admin_user_id IS NOT NULL
             GROUP BY aa.admin_user_id
             ORDER BY cnt DESC
             LIMIT ?',
            [$minutesAgo, $limit]
        );

        return array_map(fn($row) => [
            'id'    => (int) ($row['id'] ?? 0),
            'name'  => $row['name'] ?? 'Unknown',
            'count' => (int) ($row['cnt'] ?? 0),
        ], $results);
    }

    /**
     * Check if IP is blacklisted.
     *
     * @param string $ip
     * @return bool
     */
    public static function isIPBlacklisted(string $ip): bool
    {
        try {
            $row = DB::selectOne(
                'SELECT id FROM admin_ip_blacklist
                 WHERE ip_address = ? LIMIT 1',
                [$ip]
            );
            return $row !== null;
        } catch (\Throwable $e) {
            error_log('[AlertModel] Failed to check blacklist: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear old alerts (older than specified days).
     * Useful for cleanup and maintenance.
     *
     * @param int $daysOld Delete alerts older than this
     * @return int Number of alerts deleted
     */
    public static function cleanup(int $daysOld = 90): int
    {
        return DB::execute(
            'DELETE FROM admin_alerts
             WHERE triggered_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$daysOld]
        );
    }
}
