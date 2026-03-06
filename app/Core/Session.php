<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session Manager
 *
 * Wraps PHP's native session functions with a clean static API,
 * secure cookie defaults, and a flash-message mechanism.
 */
final class Session
{
    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    /**
     * Configure and start the session (idempotent – safe to call multiple times).
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name('rota12_sess');

        // Determine whether we are in a secure (HTTPS) context.
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

        session_set_cookie_params([
            'lifetime' => 0,           // Session cookie (expires when browser closes).
            'path'     => '/',
            'domain'   => '',          // Default: current domain.
            'secure'   => $isSecure,   // HTTPS-only in production.
            'httponly' => true,         // Not accessible via JavaScript.
            'samesite' => 'Lax',        // CSRF mitigation.
        ]);

        session_start();
    }

    /**
     * Regenerate the session ID (call after login / privilege changes).
     * Deletes the old session file on the server.
     */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Destroy the session completely (call on logout).
     */
    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        session_unset();

        // Expire the session cookie in the browser.
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // -------------------------------------------------------------------------
    // Data access
    // -------------------------------------------------------------------------

    /**
     * Store a value in the session under $key.
     *
     * @param string $key
     * @param mixed  $value Any serialisable value.
     */
    public static function set(string $key, $value): void
    {
        self::assertStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve a value from the session.
     *
     * @param string $key
     * @param mixed  $default Returned when $key is not present.
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        self::assertStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check whether a key exists in the session.
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        self::assertStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a key from the session.
     *
     * @param string $key
     */
    public static function remove(string $key): void
    {
        self::assertStarted();
        unset($_SESSION[$key]);
    }

    // -------------------------------------------------------------------------
    // Flash messages
    // -------------------------------------------------------------------------

    /**
     * Store a value that will be available only for the next request.
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function flash(string $key, $value): void
    {
        self::assertStarted();
        $_SESSION['__flash'][$key] = $value;
    }

    /**
     * Retrieve and immediately remove a flash value.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function getFlash(string $key, $default = null)
    {
        self::assertStarted();

        if (!isset($_SESSION['__flash'][$key])) {
            return $default;
        }

        $value = $_SESSION['__flash'][$key];
        unset($_SESSION['__flash'][$key]);

        // Clean up the parent key when no flash data remains.
        if (empty($_SESSION['__flash'])) {
            unset($_SESSION['__flash']);
        }

        return $value;
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Ensure the session has been started before accessing $_SESSION.
     *
     * @throws \RuntimeException
     */
    private static function assertStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException(
                'Session has not been started. Call Session::start() in your bootstrap file.'
            );
        }
    }
}
