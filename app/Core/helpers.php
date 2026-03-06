<?php

/**
 * Global Helper Functions
 *
 * NO namespace — loaded via bootstrap so functions are globally available.
 * These supplement (and do not replace) the App\Core\* static classes.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Output escaping
// ---------------------------------------------------------------------------

if (!function_exists('e')) {
    /**
     * Escape a value for safe HTML output.
     *
     * Uses ENT_SUBSTITUTE so malformed UTF-8 sequences are replaced with the
     * Unicode replacement character instead of returning an empty string.
     *
     * @param mixed $value
     * @return string
     */
    function e($value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

// ---------------------------------------------------------------------------
// HTML sanitisation
// ---------------------------------------------------------------------------

if (!function_exists('sanitizeHtml')) {
    /**
     * Strip dangerous HTML and attributes from user-supplied markup.
     *
     * Allowed tags: p, br, strong, em, ul, ol, li, h2, h3, h4, a, img,
     *               table, thead, tbody, tr, td, th
     *
     * Allowed attributes:
     *   a   → href, title, class
     *   img → src, alt, width, height, class
     *   *   → class  (on all other elements)
     *
     * Any attribute whose value contains "javascript:" or starts with "on"
     * (event handlers) is stripped unconditionally.
     *
     * @param string $html User-supplied HTML markup.
     * @return string Sanitised HTML.
     */
    function sanitizeHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        // 1. Strip disallowed tags (keep the content inside them).
        $allowedTags = '<p><br><strong><em><ul><ol><li><h2><h3><h4><a><img>'
                     . '<table><thead><tbody><tr><td><th>';
        $html = strip_tags($html, $allowedTags);

        // 2. Parse the remaining markup with DOMDocument and clean attributes.
        $doc = new DOMDocument('1.0', 'UTF-8');

        // Suppress warnings for malformed markup; use encoding meta for UTF-8.
        $wrapped = '<?xml encoding="utf-8" ?>'
                 . '<div id="__sanitize_root__">' . $html . '</div>';

        libxml_use_internal_errors(true);
        $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Per-element allowed attribute map.
        $allowedAttrs = [
            'a'   => ['href', 'title', 'class'],
            'img' => ['src', 'alt', 'width', 'height', 'class'],
            '*'   => ['class'],
        ];

        // Walk every element in the document.
        /** @var DOMElement $element */
        foreach (iterator_to_array($doc->getElementsByTagName('*'), false) as $element) {
            $tag = strtolower($element->tagName);

            // Build the list of permitted attributes for this element.
            $permitted = array_merge(
                $allowedAttrs['*'],
                $allowedAttrs[$tag] ?? []
            );

            // Collect attribute names to remove (cannot modify while iterating).
            $toRemove = [];

            /** @var DOMAttr $attr */
            foreach ($element->attributes as $attr) {
                $attrName  = strtolower($attr->name);
                $attrValue = strtolower(trim($attr->value));

                // Remove event handlers (on*) and javascript: pseudo-protocol.
                if (str_starts_with($attrName, 'on') ||
                    str_contains($attrValue, 'javascript:') ||
                    str_contains($attrValue, 'data:')) {
                    $toRemove[] = $attr->name;
                    continue;
                }

                // Remove anything not in the allow-list for this tag.
                if (!in_array($attrName, $permitted, true)) {
                    $toRemove[] = $attr->name;
                }
            }

            foreach ($toRemove as $name) {
                $element->removeAttribute($name);
            }
        }

        // Extract only the inner HTML of our wrapper <div>.
        $root   = $doc->getElementById('__sanitize_root__');
        $output = '';

        if ($root !== null) {
            foreach ($root->childNodes as $child) {
                $output .= $doc->saveHTML($child);
            }
        }

        return $output;
    }
}

// ---------------------------------------------------------------------------
// URL helpers
// ---------------------------------------------------------------------------

if (!function_exists('slugify')) {
    /**
     * Convert a string into a URL-friendly slug.
     *
     * Steps:
     *   1. Transliterate Unicode characters to their ASCII equivalents.
     *   2. Lowercase the result.
     *   3. Replace any sequence of non-alphanumeric characters with a hyphen.
     *   4. Trim leading/trailing hyphens.
     *
     * @param string $text
     * @return string
     */
    function slugify(string $text): string
    {
        // Transliterate (requires intl or iconv extension).
        if (function_exists('transliterator_transliterate')) {
            $text = (string) transliterator_transliterate(
                'Any-Latin; Latin-ASCII; Lower()',
                $text
            );
        } elseif (function_exists('iconv')) {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($ascii !== false) {
                $text = $ascii;
            }
            $text = strtolower($text);
        } else {
            $text = strtolower($text);
        }

        // Replace non-alphanumeric characters with hyphens.
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? $text;

        // Remove consecutive hyphens and trim.
        $text = trim($text, '-');

        return strtolower($text);
    }
}

if (!function_exists('redirect')) {
    /**
     * Issue an HTTP redirect and terminate the script.
     *
     * @param string $url  Target URL.
     * @param int    $code HTTP status code (default 302).
     */
    function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header('Location: ' . basePath($url));
        exit;
    }
}


if (!function_exists('basePath')) {
    /**
     * Build an internal URL path that works whether the app is installed at
     * domain root (/) or inside a subdirectory (e.g. /rota12).
     *
     * Examples:
     *   basePath('/')           => '/rota12/'   (or '/' if at root)
     *   basePath('/login')      => '/rota12/login'
     *   basePath('login')       => '/rota12/login'
     *   basePath('https://...') => 'https://...' (unchanged)
     */
    function basePath(string $path = ''): string
    {
        $base = defined('APP_BASE_PATH') ? (string)APP_BASE_PATH : '';
        $path = trim($path);

        // External URL or protocol-relative
        if (preg_match('~^https?://~i', $path) || str_starts_with($path, '//')) {
            return $path;
        }

        // Normalise input to a path beginning with "/"
        if ($path === '' || $path === '/') {
            $path = '/';
        } else {
            $path = '/' . ltrim($path, '/');
        }

        // If already prefixed with basePath, keep as-is
        if ($base !== '' && str_starts_with($path, $base . '/')) {
            return $path;
        }

        return ($base === '' ? '' : $base) . $path;
    }
}

if (!function_exists('urlPath')) {
    /**
     * Alias for basePath() for readability in views.
     */
    function urlPath(string $path = ''): string
    {
        return basePath($path);
    }
}

if (!function_exists('assetUrl')) {
    /**
     * Return the public URL for a front-end asset.
     *
     * @param string $path Relative path under public/assets/ (e.g. 'css/app.css').
     * @return string Full URL.
     */
    function assetUrl(string $path): string
    {
        return rtrim(APP_BASE_URL, '/') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('uploadUrl')) {
    /**
     * Return the public URL for an uploaded file.
     *
     * @param string $path Relative path under public/uploads/ (e.g. 'listings/photo.jpg').
     * @return string Full URL.
     */
    function uploadUrl(string $path): string
    {
        return rtrim(APP_BASE_URL, '/') . '/uploads/' . ltrim($path, '/');
    }
}

// ---------------------------------------------------------------------------
// Date / time
// ---------------------------------------------------------------------------

if (!function_exists('now')) {
    /**
     * Return the current date and time as a MySQL-compatible string.
     *
     * @return string e.g. '2025-04-01 14:30:00'
     */
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

// ---------------------------------------------------------------------------
// Debugging
// ---------------------------------------------------------------------------

if (!function_exists('dd')) {
    /**
     * Dump one or more variables and terminate. FOR DEVELOPMENT USE ONLY.
     *
     * In production (APP_ENV !== 'local' / 'development'), the function
     * silently returns to avoid leaking internals.
     *
     * @param mixed ...$vars
     */
    function dd(...$vars): void
    {
        $env = defined('APP_ENV') ? APP_ENV : 'production';

        if (!in_array($env, ['local', 'development', 'dev'], true)) {
            return;
        }

        header('Content-Type: text/html; charset=UTF-8');

        echo '<!DOCTYPE html><html><head><meta charset="utf-8">'
           . '<title>dd()</title>'
           . '<style>'
           . 'body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px}'
           . 'pre{background:#252526;padding:16px;border-radius:6px;overflow:auto}'
           . '.label{color:#569cd6;font-weight:bold;margin-top:16px}'
           . '</style></head><body>';

        foreach ($vars as $i => $var) {
            echo '<p class="label">Variable ' . ($i + 1) . ':</p><pre>';
            ob_start();
            var_dump($var);
            echo htmlspecialchars((string) ob_get_clean(), ENT_QUOTES, 'UTF-8');
            echo '</pre>';
        }

        // Include a backtrace so the caller is easy to find.
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        echo '<p class="label">Called from:</p><pre>';
        foreach ($trace as $frame) {
            $file = $frame['file'] ?? '[internal]';
            $line = $frame['line'] ?? '?';
            $fn   = ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
            echo htmlspecialchars("{$file}:{$line}  {$fn}()", ENT_QUOTES, 'UTF-8') . "\n";
        }
        echo '</pre></body></html>';

        exit;
    }
}
