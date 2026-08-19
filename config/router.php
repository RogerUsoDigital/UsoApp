<?php

declare(strict_types=1);

use App\Http\Router;
use App\Middleware\CorsMiddleware;

$router = new Router();

// Adicionar middlewares globais da aplicação
$router->addGlobalMiddleware(CorsMiddleware::class);

return $router;
