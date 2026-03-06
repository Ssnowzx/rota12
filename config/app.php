<?php
declare(strict_types=1);

define('APP_NAME', 'Rota 12');
define('APP_URL',  getenv('APP_URL')  ?: 'http://localhost');
define('APP_ENV',  getenv('APP_ENV')  ?: 'production');

// --- Base path for subdirectory installations ---
$urlPath = parse_url(APP_URL, PHP_URL_PATH);
define('APP_BASE_PATH', ($urlPath && $urlPath !== '/') ? rtrim($urlPath, '/') : '');
define('APP_BASE_URL', APP_URL);

// --- Debug Mode ---
define('APP_DEBUG', APP_ENV === 'development');

// --- Filesystem Paths ---
define('APP_PATH',     dirname(__DIR__) . '/app');
define('ROOT_PATH',    dirname(__DIR__));
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH',  ROOT_PATH . '/public/uploads');

// --- Upload Constraints ---
define('UPLOAD_MAX_SIZE',    3 * 1024 * 1024);
define('ALLOWED_IMAGE_EXTS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_IMAGE_MIMES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// --- Timezone ---
date_default_timezone_set('America/Sao_Paulo');
