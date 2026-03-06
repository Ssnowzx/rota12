<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AdminUserModel;

/**
 * Admin Authentication
 *
 * Handles login, logout, and session-based identity checks for the
 * administrator area. Public (front-end) users are not managed here.
 */
final class Auth
{
    // -------------------------------------------------------------------------
    // Login / Logout
    // -------------------------------------------------------------------------

    /**
     * Attempt to authenticate an admin user by e-mail and password.
     *
     * @param string $email
     * @param string $password Plain-text password to verify against the stored hash.
     * @return bool TRUE on success, FALSE on any failure (no exception leaks).
     */
    public static function loginAdmin(string $email, string $password): bool
    {
        $email = trim($email);

        if ($email === '' || $password === '') {
            return false;
        }

        $row = DB::selectOne(
            'SELECT * FROM `admin_users` WHERE `email` = ? AND `is_active` = 1 LIMIT 1',
            [$email]
        );

        if ($row === null) {
            // Perform a dummy verify to prevent timing-based user enumeration.
            // IMPORTANT: The dummy hash MUST be a properly formatted 60-character
            // bcrypt hash ($2y$ + 2-digit cost + $ + 22-char salt + 31-char hash).
            // An invalid/truncated hash causes password_verify() to short-circuit
            // and return false immediately without performing the bcrypt work,
            // defeating this timing protection entirely.
            password_verify($password, '$2y$12$AAAAAAAAAAAAAAAAAAAAAABBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB');
            AlertSystem::loginFailed($email);
            return false;
        }

        if (!password_verify($password, (string) ($row['password'] ?? ''))) {
            AlertSystem::loginFailed($email);
            return false;
        }

        // Rotate the session ID to prevent session fixation.
        Session::regenerate();

        // Persist the authenticated user (remove sensitive fields first).
        $safeRow = $row;
        unset($safeRow['password_hash']);
        Session::set('admin_user', $safeRow);

        // Record last login timestamp via Model.
        AdminUserModel::touchLogin((int) $row['id']);

        // Audit log – silently ignore if AuditModel is not yet available.
        self::auditLog(
            (int) $row['id'],
            'login',
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        return true;
    }

    /**
     * Log out the current admin user: clear session data, regenerate ID, destroy.
     */
    public static function logoutAdmin(): void
    {
        $userId = self::adminId();

        Session::remove('admin_user');
        Session::regenerate();

        if ($userId !== null) {
            self::auditLog(
                $userId,
                'logout',
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );
        }

        Session::destroy();
    }

    // -------------------------------------------------------------------------
    // Identity checks
    // -------------------------------------------------------------------------

    /**
     * Check whether an admin user is currently authenticated.
     *
     * @return bool
     */
    public static function checkAdmin(): bool
    {
        return Session::has('admin_user');
    }

    /**
     * Return the full admin user row stored in the session, or NULL.
     *
     * @return array<string, mixed>|null
     */
    public static function adminUser(): ?array
    {
        return Session::get('admin_user');
    }

    /**
     * Return the numeric ID of the currently authenticated admin, or NULL.
     *
     * @return int|null
     */
    public static function adminId(): ?int
    {
        $user = self::adminUser();
        return ($user !== null && isset($user['id'])) ? (int) $user['id'] : null;
    }

    /**
     * Require an authenticated admin user.
     * Redirect to login if not authenticated.
     *
     * @param string $redirectTo URL to redirect to if not authenticated
     */
    public static function requireAdmin(string $redirectTo = '/administrator/login'): void
    {
        if (!self::checkAdmin()) {
            header('Location: ' . basePath($redirectTo));
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Write an audit log entry if App\Models\AuditModel exists.
     * Fails silently so authentication is never blocked by a logging error.
     *
     * @param int    $userId
     * @param string $action
     * @param string $ipAddress
     * @param string $userAgent
     */
    private static function auditLog(
        int    $userId,
        string $action,
        string $ipAddress,
        string $userAgent
    ): void {
        try {
            if (class_exists(\App\Models\AuditModel::class)) {
                \App\Models\AuditModel::log(
                    $userId,
                    $action,
                    null,
                    null,
                    [],
                    $ipAddress,
                    $userAgent
                );
            }
        } catch (\Throwable $e) {
            // Logging failures must never interrupt the auth flow.
            error_log('[Auth] AuditModel::log failed: ' . $e->getMessage());
        }
    }
}
