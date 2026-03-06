<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF Protection
 *
 * Generates a per-session token and validates it on mutating requests.
 * Tokens are stored in the session and compared with a constant-time
 * comparison to prevent timing attacks.
 */
final class CSRF
{
    /** Session key used to store the CSRF token. */
    private const SESSION_KEY = 'csrf_token';

    /** HTML field name posted by forms. */
    private const FIELD_NAME = 'csrf_token';

    // -------------------------------------------------------------------------
    // Token management
    // -------------------------------------------------------------------------

    /**
     * Return the current CSRF token, generating one if it does not yet exist.
     *
     * @return string Hex-encoded 256-bit random token.
     */
    public static function generate(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if ($token === null || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }

        return (string) $token;
    }

    /**
     * Validate a token submitted by the client.
     *
     * Uses hash_equals() for a constant-time comparison to prevent
     * timing-based attacks.
     *
     * @param string $token Token from the request.
     * @return bool
     */
    public static function validate(string $token): bool
    {
        $stored = (string) Session::get(self::SESSION_KEY, '');

        if ($stored === '' || $token === '') {
            return false;
        }

        return hash_equals($stored, $token);
    }

    // -------------------------------------------------------------------------
    // View helpers
    // -------------------------------------------------------------------------

    /**
     * Return a hidden HTML input field containing the current CSRF token.
     * Safe to embed directly in any HTML form.
     *
     * @return string HTML input element.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . e(self::generate()) . '">';
    }

    // -------------------------------------------------------------------------
    // Request guard
    // -------------------------------------------------------------------------

    /**
     * Verify the CSRF token for the current request.
     *
     * Should be called at the top of any controller method that handles
     * POST / PUT / PATCH / DELETE requests in the admin area.
     * Triggers a 403 error and halts execution if the token is missing or invalid.
     */
    public static function check(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Only guard mutating requests.
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $submitted = $_POST[self::FIELD_NAME]
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        if (!self::validate((string) $submitted)) {
            Router::triggerError(403);
        }
    }

    /**
     * Rotate the CSRF token (e.g. after a successful form submission).
     * Forces a new token to be generated on the next call to generate().
     */
    public static function rotate(): void
    {
        Session::remove(self::SESSION_KEY);
    }
}
