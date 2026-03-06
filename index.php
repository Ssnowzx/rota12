<?php
   
   define('FRONT_END', true);
   
   require_once __DIR__ . '/bootstrap.php';
   
   use App\Core\Router;   Router::error(404, function (): void {
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
   
   require_once __DIR__ . '/config/routes.php';
   
   Router::run();