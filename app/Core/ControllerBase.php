<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base Controller
 *
 * All application and admin controllers extend this class.
 */
abstract class ControllerBase
{
    // -------------------------------------------------------------------------
    // View rendering
    // -------------------------------------------------------------------------

    /**
     * Render a front-end view wrapped in a layout.
     *
     * @param string $view   Dot/slash separated path relative to app/Views/ (e.g. 'home/index').
     * @param array  $data   Variables extracted into the view and layout scope.
     * @param string $layout Layout name resolved to app/Views/layouts/{layout}.php.
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath   = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = APP_PATH . '/Views/layouts/' . $layout . '.php';

        $this->renderWithLayout($viewPath, $layoutPath, $data);
    }

    /**
     * Render an admin view wrapped in an admin layout.
     *
     * @param string $view   Path relative to app/Admin/Views/ (e.g. 'dashboard/index').
     * @param array  $data   Variables extracted into the view scope.
     * @param string $layout Layout name resolved to app/Admin/Views/layouts/{layout}.php.
     */
    protected function renderAdmin(string $view, array $data = [], string $layout = 'admin'): void
    {
        $viewPath   = APP_PATH . '/Admin/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = APP_PATH . '/Admin/Views/layouts/' . $layout . '.php';

        $this->renderWithLayout($viewPath, $layoutPath, $data);
    }

    /**
     * Render a partner-panel view wrapped in the parceiro layout.
     *
     * @param string $view   Path relative to app/Views/ (e.g. 'parceiro/dashboard/index').
     * @param array  $data   Variables to extract.
     */
    protected function renderPartner(string $view, array $data = []): void
    {
        $viewPath   = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = APP_PATH . '/Views/parceiro/layouts/parceiro.php';

        $this->renderWithLayout($viewPath, $layoutPath, $data);
    }

    /**
     * Internal helper: capture view output into $content, then include the layout.
     *
     * @param string $viewPath   Absolute path to the view file.
     * @param string $layoutPath Absolute path to the layout file.
     * @param array  $data       Variables to extract.
     */
    private function renderWithLayout(string $viewPath, string $layoutPath, array $data): void
    {
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$viewPath}");
        }
        if (!is_file($layoutPath)) {
            throw new \RuntimeException("Layout not found: {$layoutPath}");
        }

        // Extract data so variables are available in the view.
        extract($data, EXTR_SKIP);

        // Capture the view output into $content.
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // Include the layout; $content is available inside the layout template.
        include $layoutPath;
    }

    // -------------------------------------------------------------------------
    // HTTP helpers
    // -------------------------------------------------------------------------

    /**
     * Issue an HTTP redirect.
     *
     * @param string $url  Target URL (absolute or relative).
     * @param int    $code HTTP status code (301, 302, 303 …).
     */
    protected function redirect(string $url, int $code = 302): void
    {
        // Auto-prepend subdirectory for root-relative URLs (e.g. /administrator/login)
        if (isset($url[0]) && $url[0] === '/' && !str_starts_with($url, '//')) {
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            // For admin routes, strip /administrator since it's already in the URL
            if (defined('ADMIN_PANEL') && str_ends_with($scriptDir, '/administrator')) {
                $scriptDir = substr($scriptDir, 0, -strlen('/administrator'));
            }
            if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '' && $scriptDir !== false) {
                $url = rtrim($scriptDir, '/') . $url;
            }
        }

        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Send a JSON response and terminate.
     *
     * @param array $data Response payload.
     * @param int   $code HTTP status code.
     */
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    /**
     * Ensure the current request is made by an authenticated admin.
     * Redirects to the admin login page on failure.
     */
    protected function requireAuth(): void
    {
        if (!Auth::checkAdmin()) {
            $this->redirect('/administrator/login');
        }
    }

    /**
     * Ensure the current admin user has a specific permission.
     * Triggers a 403 error on failure.
     *
     * @param string $perm Permission key, e.g. 'listings.edit'.
     */
    protected function requirePerm(string $perm): void
    {
        if (!ACL::can($perm)) {
            Router::triggerError(403);
        }
    }

    // -------------------------------------------------------------------------
    // Flash messages
    // -------------------------------------------------------------------------

    /**
     * Store a flash message for the next request.
     *
     * @param string $type    Severity: 'success', 'error', 'warning', 'info'.
     * @param string $message Human-readable message.
     */
    protected function flash(string $type, string $message): void
    {
        $flash   = Session::get('flash', []);
        $flash[] = ['type' => $type, 'message' => $message];
        Session::set('flash', $flash);
    }

    /**
     * Return all queued flash messages and clear them from the session.
     *
     * @return array<int, array{type: string, message: string}>
     */
    protected function getFlash(): array
    {
        $flash = Session::get('flash', []);
        Session::remove('flash');
        return $flash;
    }
}
