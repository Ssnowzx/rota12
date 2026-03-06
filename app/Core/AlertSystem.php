<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Alert System
 *
 * Centralized system for monitoring critical audit events and triggering
 * real-time alerts for suspicious patterns. Integrates with AuditModel
 * and sends notifications asynchronously.
 *
 * Critical events monitored:
 *   - login_failed: Multiple failed login attempts from same IP
 *   - admin_delete: Admin deletion of users, roles, or critical data
 *   - role_change: Permission or role modifications
 *   - acl_denied: Access control violations
 *   - file_upload_suspicious: Suspicious file uploads
 *   - config_change: System configuration modifications
 */
final class AlertSystem
{
    // -------------------------------------------------------------------------
    // Constants: Critical Events
    // -------------------------------------------------------------------------

    public const EVENT_LOGIN_FAILED = 'login_failed';
    public const EVENT_ADMIN_DELETE = 'admin_delete';
    public const EVENT_ROLE_CHANGE = 'role_change';
    public const EVENT_ACL_DENIED = 'acl_denied';
    public const EVENT_FILE_UPLOAD_SUSPICIOUS = 'file_upload_suspicious';
    public const EVENT_CONFIG_CHANGE = 'config_change';

    // -------------------------------------------------------------------------
    // Constants: Alert Severity
    // -------------------------------------------------------------------------

    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    // -------------------------------------------------------------------------
    // Constants: Thresholds
    // -------------------------------------------------------------------------

    private const FAILED_LOGIN_THRESHOLD = 3;
    private const FAILED_LOGIN_WINDOW_MINUTES = 10;
    private const RATE_LIMIT_ALERTS_PER_IP = 100;
    private const RATE_LIMIT_WINDOW_MINUTES = 60;

    // -------------------------------------------------------------------------
    // Static state
    // -------------------------------------------------------------------------

    /** Cache for in-memory quick checks (prevents database spam) */
    private static array $suspiciousIPs = [];
    private static array $whitelistedIPs = [];
    private static array $recentAlerts = [];

    // -------------------------------------------------------------------------
    // Initialization
    // -------------------------------------------------------------------------

    /**
     * Initialize the alert system with whitelisted IPs from config.
     * Should be called early in bootstrap.
     */
    public static function initialize(): void
    {
        // Load whitelisted IPs from environment or configuration
        $whitelist = getenv('ALERT_WHITELIST_IPS');
        if ($whitelist && is_string($whitelist)) {
            self::$whitelistedIPs = array_map('trim', explode(',', $whitelist));
        }
    }

    // -------------------------------------------------------------------------
    // Public API: Trigger Alerts
    // -------------------------------------------------------------------------

    /**
     * Trigger an alert for a critical event.
     *
     * This is the main entry point called from Auth, AuditModel, and other
     * critical code paths. It:
     *   1. Validates the event type
     *   2. Checks for false positives
     *   3. Evaluates severity and thresholds
     *   4. Queues a notification if needed
     *   5. Logs to storage/logs/alerts.log
     *
     * @param string $event      Event type (use self::EVENT_* constants)
     * @param array  $context    Event context (user_id, ip, action, entity, etc)
     * @param string $severity   Severity level (use self::SEVERITY_* constants)
     * @return bool TRUE if alert was triggered, FALSE if suppressed
     */
    public static function trigger(
        string $event,
        array $context = [],
        string $severity = self::SEVERITY_MEDIUM
    ): bool {
        try {
            // Validate event type
            if (!self::isValidEvent($event)) {
                error_log("[AlertSystem] Unknown event type: {$event}");
                return false;
            }

            // Extract useful context
            $ipAddress = $context['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $userId = $context['user_id'] ?? null;

            // Check if IP is whitelisted
            if (self::isIPWhitelisted($ipAddress)) {
                return false;
            }

            // Check rate limiting (prevent alert spam from same IP)
            if (self::isRateLimited($ipAddress)) {
                error_log("[AlertSystem] Rate limit exceeded for IP: {$ipAddress}");
                return false;
            }

            // Evaluate severity based on patterns
            $evaluatedSeverity = self::evaluateSeverity($event, $context, $severity);

            // Check for false positives based on event type
            if (!self::shouldAlert($event, $context)) {
                return false;
            }

            // Create alert record
            $alertId = self::createAlert([
                'event'           => $event,
                'severity'        => $evaluatedSeverity,
                'ip_address'      => $ipAddress,
                'admin_user_id'   => $userId,
                'context_json'    => json_encode($context, JSON_UNESCAPED_UNICODE),
                'triggered_at'    => now(),
            ]);

            if ($alertId === 0) {
                error_log("[AlertSystem] Failed to create alert for event: {$event}");
                return false;
            }

            // Queue notification asynchronously
            self::queueNotification($alertId, $event, $evaluatedSeverity, $context);

            // Log to file
            self::logToFile($alertId, $event, $evaluatedSeverity, $ipAddress, $userId, $context);

            // Cache this alert to track patterns
            self::recordAlert($alertId, $ipAddress, $event);

            return true;
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Exception in trigger(): ' . $e->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Event Detection Helpers
    // -------------------------------------------------------------------------

    /**
     * Detect and trigger alert for failed login attempt.
     * Called from Auth::loginAdmin() when password verification fails.
     *
     * @param string $email    Admin email that failed to authenticate
     * @param string $ip       IP address of the attempt
     * @return bool TRUE if alert was triggered
     */
    public static function loginFailed(string $email, string $ip = ''): bool
    {
        if ($ip === '') {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        return self::trigger(self::EVENT_LOGIN_FAILED, [
            'email'              => $email,
            'ip'                 => $ip,
            'failed_attempt_num' => self::countFailedLoginsFromIP($ip),
        ], self::SEVERITY_MEDIUM);
    }

    /**
     * Detect and trigger alert for admin delete operations.
     * Called when an admin deletes a user, role, or critical data.
     *
     * @param int|null $adminUserId  ID of admin performing delete
     * @param string   $entity       Entity type being deleted (user, role, etc)
     * @param int      $entityId     ID of entity being deleted
     * @param array    $entityData   Data of deleted entity (for context)
     * @return bool
     */
    public static function adminDelete(
        ?int $adminUserId,
        string $entity,
        int $entityId,
        array $entityData = []
    ): bool {
        // Only trigger alert for critical deletes (not all deletes)
        $criticalEntities = ['admin_user', 'admin_role', 'admin_permission', 'coupon'];
        if (!in_array($entity, $criticalEntities, true)) {
            return false;
        }

        return self::trigger(self::EVENT_ADMIN_DELETE, [
            'user_id'     => $adminUserId,
            'entity'      => $entity,
            'entity_id'   => $entityId,
            'entity_data' => json_encode($entityData, JSON_UNESCAPED_UNICODE),
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ], self::SEVERITY_HIGH);
    }

    /**
     * Detect and trigger alert for role/permission changes.
     *
     * @param int|null $adminUserId    ID of admin making the change
     * @param int      $targetUserId   ID of user whose roles/perms are changing
     * @param array    $oldData        Previous role/permission data
     * @param array    $newData        New role/permission data
     * @return bool
     */
    public static function roleChange(
        ?int $adminUserId,
        int $targetUserId,
        array $oldData,
        array $newData
    ): bool {
        return self::trigger(self::EVENT_ROLE_CHANGE, [
            'user_id'       => $adminUserId,
            'target_user_id' => $targetUserId,
            'old_data'      => json_encode($oldData, JSON_UNESCAPED_UNICODE),
            'new_data'      => json_encode($newData, JSON_UNESCAPED_UNICODE),
            'ip'            => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ], self::SEVERITY_HIGH);
    }

    /**
     * Detect and trigger alert for ACL denial.
     *
     * @param int|null $adminUserId   ID of user attempting access
     * @param string   $permission    Permission key that was denied
     * @param string   $resource      Resource being accessed (optional)
     * @return bool
     */
    public static function aclDenied(
        ?int $adminUserId,
        string $permission,
        string $resource = ''
    ): bool {
        return self::trigger(self::EVENT_ACL_DENIED, [
            'user_id'     => $adminUserId,
            'permission'  => $permission,
            'resource'    => $resource,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ], self::SEVERITY_MEDIUM);
    }

    /**
     * Detect and trigger alert for suspicious file upload.
     *
     * @param int|null $adminUserId   ID of user uploading
     * @param string   $filename      Original filename
     * @param string   $mimeType      Detected MIME type
     * @param int      $fileSize      File size in bytes
     * @param string   $reason        Reason why it's suspicious
     * @return bool
     */
    public static function fileSuspicious(
        ?int $adminUserId,
        string $filename,
        string $mimeType,
        int $fileSize,
        string $reason
    ): bool {
        return self::trigger(self::EVENT_FILE_UPLOAD_SUSPICIOUS, [
            'user_id'   => $adminUserId,
            'filename'  => $filename,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'reason'    => $reason,
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ], self::SEVERITY_HIGH);
    }

    /**
     * Detect and trigger alert for config changes.
     *
     * @param int|null $adminUserId   ID of user making change
     * @param string   $configKey     Configuration key being changed
     * @param mixed    $oldValue      Previous value
     * @param mixed    $newValue      New value
     * @return bool
     */
    public static function configChange(
        ?int $adminUserId,
        string $configKey,
        mixed $oldValue,
        mixed $newValue
    ): bool {
        return self::trigger(self::EVENT_CONFIG_CHANGE, [
            'user_id'    => $adminUserId,
            'config_key' => $configKey,
            'old_value'  => json_encode($oldValue, JSON_UNESCAPED_UNICODE),
            'new_value'  => json_encode($newValue, JSON_UNESCAPED_UNICODE),
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ], self::SEVERITY_HIGH);
    }

    // -------------------------------------------------------------------------
    // Private: Pattern Analysis & Severity
    // -------------------------------------------------------------------------

    /**
     * Evaluate severity based on event type and context patterns.
     * May upgrade severity if repeated events are detected.
     */
    private static function evaluateSeverity(
        string $event,
        array $context,
        string $baseSeverity
    ): string {
        $ip = $context['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Upgrade severity if this IP has multiple recent alerts
        $recentCount = self::countRecentAlertsFromIP($ip);
        if ($recentCount >= 5) {
            return self::SEVERITY_CRITICAL;
        }
        if ($recentCount >= 3) {
            return self::SEVERITY_HIGH;
        }

        // For login failures, escalate if count exceeds threshold
        if ($event === self::EVENT_LOGIN_FAILED) {
            $failedCount = $context['failed_attempt_num'] ?? 0;
            if ($failedCount >= self::FAILED_LOGIN_THRESHOLD) {
                return self::SEVERITY_HIGH;
            }
        }

        return $baseSeverity;
    }

    /**
     * Determine if an alert should be triggered for this event.
     * Returns FALSE to suppress alerts (avoid false positives).
     */
    private static function shouldAlert(string $event, array $context): bool
    {
        $ip = $context['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        switch ($event) {
            case self::EVENT_LOGIN_FAILED:
                // Only alert if we have 3+ failures in 10 minutes
                $failedCount = self::countFailedLoginsFromIP($ip);
                return $failedCount >= self::FAILED_LOGIN_THRESHOLD;

            case self::EVENT_ADMIN_DELETE:
            case self::EVENT_ROLE_CHANGE:
            case self::EVENT_FILE_UPLOAD_SUSPICIOUS:
            case self::EVENT_CONFIG_CHANGE:
                // Always alert on these critical events
                return true;

            case self::EVENT_ACL_DENIED:
                // Only alert if multiple denials from same user/IP in short time
                $denialsCount = self::countRecentEventsByUser($context['user_id'] ?? null, $event);
                return $denialsCount >= 3;

            default:
                return true;
        }
    }

    // -------------------------------------------------------------------------
    // Private: Helper Methods
    // -------------------------------------------------------------------------

    /**
     * Check if event type is recognized.
     */
    private static function isValidEvent(string $event): bool
    {
        $validEvents = [
            self::EVENT_LOGIN_FAILED,
            self::EVENT_ADMIN_DELETE,
            self::EVENT_ROLE_CHANGE,
            self::EVENT_ACL_DENIED,
            self::EVENT_FILE_UPLOAD_SUSPICIOUS,
            self::EVENT_CONFIG_CHANGE,
        ];
        return in_array($event, $validEvents, true);
    }

    /**
     * Check if IP is in the whitelist (trusted/internal).
     */
    private static function isIPWhitelisted(string $ip): bool
    {
        // Always allow localhost
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return true;
        }

        // Check static whitelist
        if (in_array($ip, self::$whitelistedIPs, true)) {
            return true;
        }

        return false;
    }

    /**
     * Check if IP has exceeded rate limit for alerts.
     */
    private static function isRateLimited(string $ip): bool
    {
        // Get count of alerts from this IP in last N minutes
        $count = self::countRecentAlertsFromIP($ip, self::RATE_LIMIT_WINDOW_MINUTES);
        return $count >= self::RATE_LIMIT_ALERTS_PER_IP;
    }

    /**
     * Count failed login attempts from IP in the monitoring window.
     */
    private static function countFailedLoginsFromIP(string $ip): int
    {
        try {
            $row = DB::selectOne(
                'SELECT COUNT(*) AS cnt FROM admin_alerts
                 WHERE ip_address = ? AND event = ?
                   AND triggered_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)',
                [$ip, self::EVENT_LOGIN_FAILED, self::FAILED_LOGIN_WINDOW_MINUTES]
            );
            return (int) ($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to count login failures: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count recent alerts from an IP.
     */
    private static function countRecentAlertsFromIP(string $ip, int $windowMinutes = 60): int
    {
        try {
            $row = DB::selectOne(
                'SELECT COUNT(*) AS cnt FROM admin_alerts
                 WHERE ip_address = ? AND triggered_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)',
                [$ip, $windowMinutes]
            );
            return (int) ($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to count alerts: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count recent events of a specific type by user.
     */
    private static function countRecentEventsByUser(?int $userId, string $event, int $windowMinutes = 60): int
    {
        try {
            if ($userId === null) {
                return 0;
            }

            $row = DB::selectOne(
                'SELECT COUNT(*) AS cnt FROM admin_alerts
                 WHERE admin_user_id = ? AND event = ?
                   AND triggered_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)',
                [$userId, $event, $windowMinutes]
            );
            return (int) ($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to count events by user: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create an alert record in the database.
     */
    private static function createAlert(array $alertData): int
    {
        try {
            return DB::insert(
                'INSERT INTO admin_alerts
                    (event, severity, ip_address, admin_user_id, context_json, triggered_at)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $alertData['event'],
                    $alertData['severity'],
                    $alertData['ip_address'],
                    $alertData['admin_user_id'],
                    $alertData['context_json'],
                    $alertData['triggered_at'],
                ]
            );
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to create alert: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Queue notification for immediate or async sending.
     */
    private static function queueNotification(
        int $alertId,
        string $event,
        string $severity,
        array $context
    ): void {
        try {
            // Queue via AlertNotifier (will be processed by cron or queue system)
            if (class_exists(\App\Core\AlertNotifier::class)) {
                \App\Core\AlertNotifier::queue($alertId, $event, $severity, $context);
            }
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to queue notification: ' . $e->getMessage());
        }
    }

    /**
     * Log alert to storage/logs/alerts.log.
     */
    private static function logToFile(
        int $alertId,
        string $event,
        string $severity,
        string $ip,
        ?int $userId,
        array $context
    ): void {
        try {
            $logPath = rtrim(dirname(__DIR__, 2), '/') . '/storage/logs/alerts.log';
            $userInfo = $userId ? "user_id={$userId}" : 'user_id=null';
            $timestamp = date('Y-m-d H:i:s');
            $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE);

            $message = sprintf(
                "[%s] [%s] [%s] alert_id=%d event=%s %s ip=%s %s\n",
                $timestamp,
                strtoupper($severity),
                getenv('APP_ENV') ?: 'production',
                $alertId,
                $event,
                $userInfo,
                $ip,
                $contextJson
            );

            error_log($message, 3, $logPath);
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to log to file: ' . $e->getMessage());
        }
    }

    /**
     * Record this alert in memory cache for pattern tracking.
     */
    private static function recordAlert(int $alertId, string $ip, string $event): void
    {
        $now = time();
        self::$recentAlerts[$ip][] = [
            'id'        => $alertId,
            'event'     => $event,
            'timestamp' => $now,
        ];

        // Keep memory under control (only last 1000 alerts)
        if (count(self::$recentAlerts[$ip] ?? []) > 100) {
            array_shift(self::$recentAlerts[$ip]);
        }
    }

    // -------------------------------------------------------------------------
    // Management API (for dashboard/admin)
    // -------------------------------------------------------------------------

    /**
     * Add an IP to the whitelist.
     */
    public static function whitelistIP(string $ip): void
    {
        if (!in_array($ip, self::$whitelistedIPs, true)) {
            self::$whitelistedIPs[] = $ip;
        }
    }

    /**
     * Remove an IP from the whitelist.
     */
    public static function unwhitelistIP(string $ip): void
    {
        self::$whitelistedIPs = array_diff(self::$whitelistedIPs, [$ip]);
    }

    /**
     * Get recent alerts from database.
     */
    public static function getRecentAlerts(int $limit = 50): array
    {
        try {
            return DB::select(
                'SELECT * FROM admin_alerts
                 ORDER BY triggered_at DESC
                 LIMIT ?',
                [$limit]
            );
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to get recent alerts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get alerts with filters.
     */
    public static function getFilteredAlerts(
        ?string $event = null,
        ?string $severity = null,
        ?string $ip = null,
        ?int $userId = null,
        int $limit = 50
    ): array {
        try {
            $sql = 'SELECT * FROM admin_alerts WHERE 1=1';
            $params = [];

            if ($event !== null) {
                $sql .= ' AND event = ?';
                $params[] = $event;
            }
            if ($severity !== null) {
                $sql .= ' AND severity = ?';
                $params[] = $severity;
            }
            if ($ip !== null) {
                $sql .= ' AND ip_address = ?';
                $params[] = $ip;
            }
            if ($userId !== null) {
                $sql .= ' AND admin_user_id = ?';
                $params[] = $userId;
            }

            $sql .= ' ORDER BY triggered_at DESC LIMIT ?';
            $params[] = $limit;

            return DB::select($sql, $params);
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to get filtered alerts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Block an IP (add to blacklist if table exists).
     */
    public static function blockIP(string $ip): bool
    {
        try {
            DB::execute(
                'INSERT INTO admin_ip_blacklist (ip_address, reason, created_at)
                 VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE updated_at = NOW()',
                [$ip, 'Blocked via AlertSystem']
            );
            return true;
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to block IP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable an admin user (security lockdown).
     */
    public static function disableUser(int $userId, string $reason): bool
    {
        try {
            DB::execute(
                'UPDATE admin_users SET is_active = 0, updated_at = NOW() WHERE id = ?',
                [$userId]
            );

            // Log this action
            \App\Models\AuditModel::log(
                null,
                'system_disable_user',
                'admin_user',
                $userId,
                ['reason' => $reason],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            return true;
        } catch (\Throwable $e) {
            error_log('[AlertSystem] Failed to disable user: ' . $e->getMessage());
            return false;
        }
    }
}
