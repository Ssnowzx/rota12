<?php
declare(strict_types=1);

/**
 * Frontend Routes
 *
 * Registers all public-facing routes for the Rota 12 application.
 * Loaded exclusively by public/index.php via bootstrap.php.
 */

use App\Core\Router;

// --- Home ---
Router::get('/', 'App\Controllers\HomeController@index');

// --- Auth ---
Router::get('/login',    'App\Controllers\AuthController@loginForm');
Router::post('/login',   'App\Controllers\AuthController@login');
Router::get('/cadastro', 'App\Controllers\AuthController@registerForm');
Router::post('/cadastro','App\Controllers\AuthController@register');
Router::get('/sair',     'App\Controllers\AuthController@logout');

// --- Subscription ---
Router::get('/checkout',          'App\Controllers\SubscriptionController@index');
Router::get('/checkout/sucesso',  'App\Controllers\SubscriptionController@success');

// --- My Account ---
Router::get('/minha-conta', 'App\Controllers\AccountController@index');
Router::post('/minha-conta/pegar-cupom',          'App\Controllers\AccountController@grabCoupon');
Router::post('/minha-conta/cancelar-assinatura',   'App\Controllers\AccountController@cancelSubscription');
Router::post('/minha-conta/atualizar-avatar',      'App\Controllers\AccountController@updateAvatar');

// --- Partner Dashboard ---
Router::get('/parceiro',                              'App\Controllers\Partner\PartnerDashboardController@redirectToDashboard');
Router::get('/parceiro/dashboard',                    'App\Controllers\Partner\PartnerDashboardController@index');
Router::get('/parceiro/dashboard/stats',              'App\Controllers\Partner\PartnerDashboardController@stats');
Router::get('/parceiro/campanhas',                    'App\Controllers\Partner\PartnerCampaignController@index');
Router::post('/parceiro/campanhas',                   'App\Controllers\Partner\PartnerCampaignController@store');
Router::post('/parceiro/campanhas/{id}/status',       'App\Controllers\Partner\PartnerCampaignController@status');
Router::post('/parceiro/campanhas/{id}/delete',       'App\Controllers\Partner\PartnerCampaignController@destroy');
Router::post('/parceiro/cupons/validar',              'App\Controllers\Partner\PartnerCouponController@validar');

// --- Static Pages ---
Router::get('/pagina/{slug}', 'App\Controllers\PageController@show');

// --- Cities ---
Router::get('/cidades',        'App\Controllers\CityController@index');
Router::get('/cidades/{slug}', 'App\Controllers\CityController@show');

// --- Partners ---
Router::get('/parceiros',        'App\Controllers\PartnerController@index');
Router::get('/parceiros/{slug}', 'App\Controllers\PartnerController@show');

// --- Establishments ---
Router::get('/estabelecimentos/{city_slug}/{slug}', 'App\Controllers\EstablishmentController@show');

// --- Coupons ---
Router::get('/cupons', 'App\Controllers\CouponController@index');

// --- Catch-all: slugs diretos mapeiam para páginas do banco ---
// DEVE ser a última rota registrada
Router::get('/{slug}', 'App\Controllers\PageController@show');

