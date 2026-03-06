<?php
declare(strict_types=1);

/**
 * Frontend Front Controller
 *
 * Entry point for all public-facing requests routed through
 * public/.htaccess → public/index.php.
 *
 * Responsibilities:
 *   1. Define FRONT_END constant (guards, views, etc. can check this)
 *   2. Bootstrap the application
 *   3. Register frontend error handlers
 *   4. Load frontend route definitions
 *   5. Dispatch the current request
 */

define('FRONT_END', true);

// Bootstrap is one level up from public/
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;

// Base path for subdirectory installs (e.g. /rota12)
if (defined('APP_BASE_PATH') && APP_BASE_PATH !== '') {
    Router::setBasePath(APP_BASE_PATH);
}

// ============================================================
// ERROR HANDLERS
// ============================================================
Router::error(404, function (): void {
    http_response_code(404);
    include APP_PATH . '/Views/errors/404.php';
});

Router::error(403, function (): void {
    http_response_code(403);
    include APP_PATH . '/Views/errors/403.php';
});

Router::error(500, function (): void {
    http_response_code(500);
    include APP_PATH . '/Views/errors/500.php';
});

// ============================================================
// ROUTES
// ============================================================
require_once dirname(__DIR__) . '/config/routes.php';

// ============================================================
// DISPATCH
// ============================================================
Router::run();
