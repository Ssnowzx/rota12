<?php
declare(strict_types=1);

/**
 * Database Configuration
 *
 * IMPORTANT: In production, set environment variables in cPanel or .env
 * Never hard-code credentials in this file for production environments.
 *
 * cPanel → Software → PHP Config or set via .env loader before this file loads.
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'rota12_versao2');
define('DB_USER', getenv('DB_USER') ?: 'rota12_versao2');
define('DB_PASS', getenv('DB_PASS') ?: 'gustavoviado00');
