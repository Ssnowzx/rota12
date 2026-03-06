<?php
declare(strict_types=1);

/**
 * Admin Front Controller
 *
 * Entry point for all administration panel requests routed through
 * administrator/.htaccess → administrator/index.php.
 *
 * Responsibilities:
 *   1. Define ADMIN_PANEL constant (middleware, views, etc. can check this)
 *   2. Bootstrap the application
 *   3. Register admin-specific error handlers
 *   4. Load admin route definitions
 *   5. Dispatch the current request
 */

define('ADMIN_PANEL', true);

// Bootstrap is one level up from administrator/
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;

// ============================================================
// ERROR HANDLERS
// ============================================================
Router::error(404, function (): void {
    http_response_code(404);
    include APP_PATH . '/Admin/Views/errors/404.php';
});

Router::error(403, function (): void {
    http_response_code(403);
    include APP_PATH . '/Admin/Views/errors/403.php';
});

Router::error(500, function (): void {
    http_response_code(500);
    include APP_PATH . '/Admin/Views/errors/500.php';
});

// ============================================================
// ROUTES
// ============================================================
require_once dirname(__DIR__) . '/config/routes-admin.php';

// ============================================================
// TEMPORARY DEBUG v2 — Remove after fixing!
// ============================================================
if (isset($_GET['debug'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "=== DEBUG v2 - ROTA 12 ===\n\n";
    
    // 1. Server vars
    echo "REQUEST_URI:  " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME:  " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "ADMIN_PANEL:  " . (defined('ADMIN_PANEL') ? 'true' : 'false') . "\n";
    
    // 2. Check routes file
    $routesFile = dirname(__DIR__) . '/config/routes-admin.php';
    echo "\nRoutes file: " . $routesFile . "\n";
    echo "Exists? " . (file_exists($routesFile) ? 'YES' : 'NO!!!') . "\n";
    
    // 3. Check registered routes using Reflection
    $ref = new ReflectionClass('App\Core\Router');
    $prop = $ref->getProperty('routes');
    $prop->setAccessible(true);
    $routes = $prop->getValue();
    
    echo "\n--- Registered Routes ---\n";
    $total = 0;
    foreach ($routes as $method => $methodRoutes) {
        foreach ($methodRoutes as $r) {
            $total++;
            echo "$method " . $r['pattern'] . " => " . $r['handler'] . "\n";
        }
    }
    echo "Total routes: $total\n";
    
    // 4. Check basePath
    $bpProp = $ref->getProperty('basePath');
    $bpProp->setAccessible(true);
    echo "\nRouter basePath: '" . $bpProp->getValue() . "'\n";
    
    // 5. Simulate parseUri
    echo "\n--- Manual URI test ---\n";
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $pos = strpos($uri, '?');
    if ($pos !== false) $uri = substr($uri, 0, $pos);
    $uri = rawurldecode($uri);
    echo "URI raw: $uri\n";
    
    // Test match against first route
    if (isset($routes['GET'][0])) {
        $pattern = $routes['GET'][0]['pattern'];
        echo "First GET pattern: $pattern\n";
        echo "preg_match($pattern, $uri): " . (preg_match($pattern, $uri) ? 'MATCH' : 'NO') . "\n";
    }
    
    exit;
}

// ============================================================
// DISPATCH
// ============================================================
Router::run();
