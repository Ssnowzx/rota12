<?php
declare(strict_types=1);

/**
 * Admin Routes
 *
 * Registers all administration panel routes for the Rota 12 application.
 * Loaded exclusively by administrator/index.php via bootstrap.php.
 */

use App\Core\Router;

// ============================================================
// AUTH — Public (no authentication required)
// ============================================================
Router::get('/administrator',        'App\Admin\Controllers\AuthController@index');
Router::get('/administrator/login',  'App\Admin\Controllers\AuthController@loginForm');
Router::post('/administrator/login', 'App\Admin\Controllers\AuthController@login');
Router::get('/administrator/logout',  'App\Admin\Controllers\AuthController@logout');
Router::post('/administrator/logout', 'App\Admin\Controllers\AuthController@logout');

// ============================================================
// DASHBOARD
// ============================================================
Router::get('/administrator/dashboard', 'App\Admin\Controllers\DashboardController@index');

// ============================================================
// USERS
// ============================================================
Router::get('/administrator/users',              'App\Admin\Controllers\UserController@index');
Router::get('/administrator/users/create',       'App\Admin\Controllers\UserController@create');
Router::post('/administrator/users',             'App\Admin\Controllers\UserController@store');
Router::get('/administrator/users/{id}/edit',    'App\Admin\Controllers\UserController@edit');
Router::post('/administrator/users/{id}',        'App\Admin\Controllers\UserController@update');
Router::post('/administrator/users/{id}/delete', 'App\Admin\Controllers\UserController@destroy');

// ============================================================
// ROLES
// ============================================================
Router::get('/administrator/roles',              'App\Admin\Controllers\RoleController@index');
Router::get('/administrator/roles/create',       'App\Admin\Controllers\RoleController@create');
Router::post('/administrator/roles',             'App\Admin\Controllers\RoleController@store');
Router::get('/administrator/roles/{id}/edit',    'App\Admin\Controllers\RoleController@edit');
Router::post('/administrator/roles/{id}',        'App\Admin\Controllers\RoleController@update');
Router::post('/administrator/roles/{id}/delete', 'App\Admin\Controllers\RoleController@destroy');

// ============================================================
// PAGES
// ============================================================
Router::get('/administrator/pages',              'App\Admin\Controllers\PageController@index');
Router::get('/administrator/pages/create',       'App\Admin\Controllers\PageController@create');
Router::post('/administrator/pages',             'App\Admin\Controllers\PageController@store');
Router::get('/administrator/pages/{id}/edit',    'App\Admin\Controllers\PageController@edit');
Router::post('/administrator/pages/{id}',        'App\Admin\Controllers\PageController@update');
Router::post('/administrator/pages/{id}/delete', 'App\Admin\Controllers\PageController@destroy');

// ============================================================
// BANNERS
// ============================================================
Router::get('/administrator/banners',              'App\Admin\Controllers\BannerController@index');
Router::get('/administrator/banners/create',       'App\Admin\Controllers\BannerController@create');
Router::post('/administrator/banners',             'App\Admin\Controllers\BannerController@store');
Router::get('/administrator/banners/{id}/edit',    'App\Admin\Controllers\BannerController@edit');
Router::post('/administrator/banners/{id}',        'App\Admin\Controllers\BannerController@update');
Router::post('/administrator/banners/{id}/delete', 'App\Admin\Controllers\BannerController@destroy');

// ============================================================
// HIGHLIGHTS
// ============================================================
Router::get('/administrator/highlights',              'App\Admin\Controllers\HighlightController@index');
Router::get('/administrator/highlights/create',       'App\Admin\Controllers\HighlightController@create');
Router::post('/administrator/highlights',             'App\Admin\Controllers\HighlightController@store');
Router::get('/administrator/highlights/{id}/edit',    'App\Admin\Controllers\HighlightController@edit');
Router::post('/administrator/highlights/{id}',        'App\Admin\Controllers\HighlightController@update');
Router::post('/administrator/highlights/{id}/delete', 'App\Admin\Controllers\HighlightController@destroy');

// ============================================================
// CITIES
// ============================================================
Router::get('/administrator/cities',              'App\Admin\Controllers\CityController@index');
Router::get('/administrator/cities/create',       'App\Admin\Controllers\CityController@create');
Router::post('/administrator/cities',             'App\Admin\Controllers\CityController@store');
Router::get('/administrator/cities/{id}/edit',    'App\Admin\Controllers\CityController@edit');
Router::post('/administrator/cities/{id}',        'App\Admin\Controllers\CityController@update');
Router::post('/administrator/cities/{id}/delete', 'App\Admin\Controllers\CityController@destroy');

// ============================================================
// PARTNERS — Approval Workflow
// ============================================================
Router::get('/administrator/approval/partners',              'App\Admin\Controllers\PartnerApprovalController@index');
Router::get('/administrator/approval/partners/{id}',         'App\Admin\Controllers\PartnerApprovalController@show');
Router::post('/administrator/approval/partners/{id}/approve', 'App\Admin\Controllers\PartnerApprovalController@approve');
Router::post('/administrator/approval/partners/{id}/reject',  'App\Admin\Controllers\PartnerApprovalController@reject');

// ============================================================
// PARTNERS — Management
// ============================================================
Router::get('/administrator/partners',              'App\Admin\Controllers\PartnerController@index');
Router::get('/administrator/partners/create',       'App\Admin\Controllers\PartnerController@create');
Router::post('/administrator/partners',             'App\Admin\Controllers\PartnerController@store');
Router::get('/administrator/partners/{id}/edit',    'App\Admin\Controllers\PartnerController@edit');
Router::post('/administrator/partners/{id}',        'App\Admin\Controllers\PartnerController@update');
Router::post('/administrator/partners/{id}/delete', 'App\Admin\Controllers\PartnerController@destroy');

// ============================================================
// ESTABLISHMENTS
// ============================================================
Router::get('/administrator/establishments',              'App\Admin\Controllers\EstablishmentController@index');
Router::get('/administrator/establishments/create',       'App\Admin\Controllers\EstablishmentController@create');
Router::post('/administrator/establishments',             'App\Admin\Controllers\EstablishmentController@store');
Router::get('/administrator/establishments/{id}/edit',    'App\Admin\Controllers\EstablishmentController@edit');
Router::post('/administrator/establishments/{id}',        'App\Admin\Controllers\EstablishmentController@update');
Router::post('/administrator/establishments/{id}/delete', 'App\Admin\Controllers\EstablishmentController@destroy');

// ============================================================
// COUPONS
// ============================================================
Router::get('/administrator/coupons',              'App\Admin\Controllers\CouponController@index');
Router::get('/administrator/coupons/create',       'App\Admin\Controllers\CouponController@create');
Router::post('/administrator/coupons',             'App\Admin\Controllers\CouponController@store');
Router::get('/administrator/coupons/{id}/edit',    'App\Admin\Controllers\CouponController@edit');
Router::post('/administrator/coupons/{id}',        'App\Admin\Controllers\CouponController@update');
Router::post('/administrator/coupons/{id}/delete', 'App\Admin\Controllers\CouponController@destroy');

// ============================================================
// AUDIT LOG
// ============================================================
Router::get('/administrator/audit', 'App\Admin\Controllers\AuditController@index');

// ============================================================
// ALERTS
// ============================================================
Router::get('/administrator/alerts',                       'App\Admin\Controllers\AlertDashboardController@index');
Router::get('/administrator/alerts/filter',                'App\Admin\Controllers\AlertDashboardController@filter');
Router::get('/administrator/alerts/by-event/{event}',      'App\Admin\Controllers\AlertDashboardController@byEvent');
Router::get('/administrator/alerts/by-ip/{ip}',            'App\Admin\Controllers\AlertDashboardController@byIP');
Router::get('/administrator/alerts/by-user/{userId}',      'App\Admin\Controllers\AlertDashboardController@byUser');
Router::get('/administrator/alerts/{id}',                  'App\Admin\Controllers\AlertDashboardController@show');
Router::post('/administrator/alerts/{id}/block-ip',        'App\Admin\Controllers\AlertDashboardController@blockIP');
Router::post('/administrator/alerts/{id}/disable-user',    'App\Admin\Controllers\AlertDashboardController@disableUser');

// ============================================================
