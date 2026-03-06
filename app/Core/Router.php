<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Front-controller Router
 *
 * Usage (index.php):
 *   Router::get('/', 'App\Controllers\HomeController@index');
 *   Router::get('/city/{slug}', 'App\Controllers\CityController@show');
 *   Router::post('/contact', 'App\Controllers\ContactController@store');
 *   Router::error(404, fn() => (new App\Controllers\ErrorController)->notFound());
 *   Router::run();
 */
final class Router
{
    /** @var array<string, array<int, array{pattern: string, handler: string}>> */
    private static array $routes = [];

    /** @var array<int, callable> */
    private static array $errorHandlers = [];

    /** Base path to strip from REQUEST_URI (useful for admin sub-directory). */
    private static string $basePath = '';

    // -------------------------------------------------------------------------
    // Route registration
    // -------------------------------------------------------------------------

    public static function get(string $path, string $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, string $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    public static function put(string $path, string $handler): void
    {
        self::addRoute('PUT', $path, $handler);
    }

    public static function patch(string $path, string $handler): void
    {
        self::addRoute('PATCH', $path, $handler);
    }

    public static function delete(string $path, string $handler): void
    {
        self::addRoute('DELETE', $path, $handler);
    }

    /** Register a route for any HTTP method. */
    public static function any(string $path, string $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            self::addRoute($method, $path, $handler);
        }
    }

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    /**
     * Strip a base path prefix before matching (e.g. '/administrator' for admin routes).
     */
    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    /**
     * Register a custom handler for HTTP error codes (403, 404, 500 …).
     */
    public static function error(int $code, callable $handler): void
    {
        self::$errorHandlers[$code] = $handler;
    }

    /**
     * Invoke the registered error handler or send a plain HTTP error response.
     */
    public static function triggerError(int $code): void
    {
        http_response_code($code);

        if (isset(self::$errorHandlers[$code])) {
            call_user_func(self::$errorHandlers[$code], $code);
            exit;
        }

        // Default fallback responses.
        $messages = [
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ];

        $message = $messages[$code] ?? 'Error';
        echo "<!DOCTYPE html><html><head><title>{$code} {$message}</title></head>"
            . "<body><h1>{$code} {$message}</h1></body></html>";
        exit;
    }

    // -------------------------------------------------------------------------
    // Dispatch
    // -------------------------------------------------------------------------

    /**
     * Match the current request against registered routes and dispatch.
     */
    public static function run(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Support method override via hidden field or X-HTTP-Method-Override header.
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '';
            if (in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = strtoupper($override);
            }
        }

        $uri = self::parseUri();

        // Try to find a matching route for the current HTTP method.
        $matched = self::dispatch($method, $uri);

        if ($matched === false) {
            // Check if the URI exists for a different method (405 vs 404).
            foreach (self::$routes as $routeMethod => $routes) {
                if ($routeMethod === $method) {
                    continue;
                }
                foreach ($routes as $route) {
                    if (self::matchRoute($route['pattern'], $uri) !== null) {
                        self::triggerError(405);
                    }
                }
            }
            self::triggerError(404);
        }
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private static function addRoute(string $method, string $path, string $handler): void
    {
        $pattern = self::compilePattern($path);
        self::$routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    /**
     * Convert a route path like /city/{slug} into a named-capture regex.
     */
    private static function compilePattern(string $path): string
    {
        $path    = rtrim($path, '/') ?: '/';
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn(array $m): string => '(?P<' . $m[1] . '>[^/]+)',
            $path
        );
        return '#^' . $pattern . '/?$#u';
    }

    /**
     * Strip query string, base path, and normalise the URI.
     */
    private static function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string.
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Decode percent-encoding.
        $uri = rawurldecode($uri);

                // Auto-detect subdirectory from SCRIPT_NAME
        if (self::$basePath === '') {
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '') {
                if (defined('ADMIN_PANEL') && str_ends_with($scriptDir, '/administrator')) {
                    $scriptDir = substr($scriptDir, 0, -strlen('/administrator'));
                }
                if ($scriptDir !== '' && $scriptDir !== '/' && $scriptDir !== false) {
                    self::$basePath = rtrim($scriptDir, '/');
                }
            }
        }


        // Strip base path prefix.
        if (self::$basePath !== '' && strpos($uri, self::$basePath) === 0) {
            $uri = substr($uri, strlen(self::$basePath));
        }

        $uri = '/' . ltrim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    /**
     * Attempt to match $uri against a compiled pattern.
     * Returns the captured named params array on success, or null on failure.
     *
     * @return array<string, string>|null
     */
    private static function matchRoute(string $pattern, string $uri): ?array
    {
        if (!preg_match($pattern, $uri, $matches)) {
            return null;
        }

        // Keep only named captures (filter out integer-indexed matches).
        $params = array_filter(
            $matches,
            static fn($key): bool => is_string($key),
            ARRAY_FILTER_USE_KEY
        );

        return $params;
    }

    /**
     * Try every route registered for $method until one matches, then dispatch.
     * Returns true on match, false if no route was found.
     */
    private static function dispatch(string $method, string $uri): bool
    {
        $routes = self::$routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = self::matchRoute($route['pattern'], $uri);

            if ($params === null) {
                continue;
            }

            self::invokeHandler($route['handler'], $params);
            return true;
        }

        return false;
    }

    /**
     * Resolve a "Controller@method" string, instantiate the controller, and call the method.
     *
     * @param string               $handler "FullyQualified\Controller@method"
     * @param array<string,string> $params  Named URL parameters.
     */
    private static function invokeHandler(string $handler, array $params): void
    {
        if (!str_contains($handler, '@')) {
            throw new RuntimeException("Router: invalid handler format '{$handler}'. Expected 'Class@method'.");
        }

        [$class, $method] = explode('@', $handler, 2);

        if (!class_exists($class)) {
            throw new RuntimeException("Router: controller class '{$class}' not found.");
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Router: method '{$method}' not found on '{$class}'.");
        }

        $controller->{$method}($params);
    }
}
