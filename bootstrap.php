<?php
declare(strict_types=1);

/**
 * Bootstrap
 *
 * Application bootstrap file. Loaded by both front controllers:
 *   - public/index.php      (frontend)
 *   - administrator/index.php (admin panel)
 *
 * Responsibilities:
 *   1. Load core configuration (app + database)
 *   2. Register PSR-4 autoloader
 *   3. Load global helper functions
 *   4. Configure PHP error reporting / logging
 *   5. Register global exception handler
 *   6. Start session
 */

// ============================================================
// 0. LOAD .ENV (if exists)
// ============================================================
$envFile = __DIR__ . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("{$key}={$value}");
            }
        }
    }
}

// ============================================================
// 1. CONFIGURATION
// ============================================================
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// ============================================================
// 2. PSR-4 AUTOLOADER
// ============================================================
/**
 * Namespace → Filesystem mapping:
 *
 *   App\Core\DB                           → app/Core/DB.php
 *   App\Controllers\HomeController        → app/Controllers/HomeController.php
 *   App\Admin\Controllers\AuthController  → app/Admin/Controllers/AuthController.php
 *   App\Models\PageModel                  → app/Models/PageModel.php
 */
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = __DIR__ . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));

    // Defence-in-depth: reject any class name containing path-traversal sequences.
    // PHP identifiers cannot naturally contain '..', but a dynamic class string
    // (e.g. from class_exists() with external input) could theoretically abuse this.
    if (str_contains($relativeClass, '..') || str_contains($relativeClass, "\0")) {
        return;
    }

    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Ensure the resolved path stays within $baseDir (canonical path check).
    $realBase = realpath($baseDir);
    $realFile = realpath($file);

    if ($realFile === false || $realBase === false || !str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)) {
        return;
    }

    require $realFile;
});

// ============================================================
// 3. GLOBAL HELPERS
// ============================================================
require_once APP_PATH . '/Core/helpers.php';

// ============================================================
// 3.1 BASE PATH (subdirectory installs) + HTML link rewriting
// ============================================================
// Ensure Router strips the base path when matching routes (e.g. /rota12/*)
// and automatically prefix HTML links/forms/assets that were written as "/...".
if (defined('APP_BASE_PATH') && APP_BASE_PATH !== '') {
    // Configure router basePath
    if (class_exists(\App\Core\Router::class)) {
        \App\Core\Router::setBasePath(APP_BASE_PATH);
    }

    // Rewrite href/src/action="/..." → href/src/action="/<base>/..." (avoid // and avoid double prefix)
    ob_start(function (string $buffer): string {
        $base = (string)APP_BASE_PATH;
        $baseTrim = ltrim($base, '/');
        if ($baseTrim === '') {
            return $buffer;
        }
        $pattern = '~\b(href|src|action)=([\"\'])/(?!/|' . preg_quote($baseTrim, '~') . '/)~i';
        return (string)preg_replace($pattern, '$1=$2' . $base . '/', $buffer);
    });
}

// ============================================================
// 4. ERROR REPORTING
// ============================================================
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/error.log');
}

// ============================================================
// 5. GLOBAL EXCEPTION HANDLER
// ============================================================
set_exception_handler(function (Throwable $e): void {
    if (APP_DEBUG) {
        echo '<pre>'
            . htmlspecialchars($e->getMessage())
            . "\n"
            . htmlspecialchars($e->getTraceAsString())
            . '</pre>';
    } else {
        http_response_code(500);
        include APP_PATH . '/Views/errors/500.php';
    }
    exit;
});

// ============================================================
// 6. SESSION
// ============================================================
App\Core\Session::start();

// ============================================================
// 7. ALERT SYSTEM
// ============================================================
App\Core\AlertSystem::initialize();
