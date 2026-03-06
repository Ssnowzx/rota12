<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * View Renderer
 *
 * Resolves template paths relative to APP_PATH, extracts data into scope,
 * and returns the rendered HTML string.
 *
 * A global e() helper is also registered here for use inside view templates.
 */
final class View
{
    // -------------------------------------------------------------------------
    // Front-end views
    // -------------------------------------------------------------------------

    /**
     * Render a front-end template and return its output as a string.
     *
     * @param string               $template Slash/dot-separated path relative to app/Views/
     *                                        e.g. 'home/index' → APP_PATH/Views/home/index.php
     * @param array<string, mixed> $data     Variables to extract into the template scope.
     * @return string Rendered HTML.
     *
     * @throws RuntimeException If the template file does not exist.
     */
    public static function render(string $template, array $data = []): string
    {
        $path = APP_PATH . '/Views/' . str_replace('.', '/', $template) . '.php';
        return self::capture($path, $data);
    }

    // -------------------------------------------------------------------------
    // Admin views
    // -------------------------------------------------------------------------

    /**
     * Render an admin template and return its output as a string.
     *
     * @param string               $template Path relative to app/Admin/Views/
     *                                        e.g. 'dashboard/index' → APP_PATH/Admin/Views/dashboard/index.php
     * @param array<string, mixed> $data     Variables to extract into the template scope.
     * @return string Rendered HTML.
     *
     * @throws RuntimeException If the template file does not exist.
     */
    public static function renderAdmin(string $template, array $data = []): string
    {
        $path = APP_PATH . '/Admin/Views/' . str_replace('.', '/', $template) . '.php';
        return self::capture($path, $data);
    }

    // -------------------------------------------------------------------------
    // Escaping
    // -------------------------------------------------------------------------

    /**
     * Escape a string for safe HTML output.
     *
     * @param string $value Raw string.
     * @return string HTML-safe string.
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Include a PHP file in an isolated scope and return its buffered output.
     *
     * @param string               $path Absolute path to the template file.
     * @param array<string, mixed> $data Variables to extract.
     * @return string
     *
     * @throws RuntimeException If $path does not point to a readable file.
     */
    private static function capture(string $path, array $data): string
    {
        if (!is_file($path)) {
            throw new RuntimeException("View template not found: {$path}");
        }

        // Extract into local scope; EXTR_SKIP prevents overwriting existing locals.
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $path;
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}

// ---------------------------------------------------------------------------
// Global helper: e()
// Registered here so it is available as soon as View.php is autoloaded.
// ---------------------------------------------------------------------------

if (!function_exists('e')) {
    /**
     * Escape a value for safe HTML output.
     *
     * @param mixed $value
     * @return string
     */
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
