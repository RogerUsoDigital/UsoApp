<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\Container;
use App\Http\Router;
use App\Http\Request;
use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

$container = new Container();

$request = Request::capture();

$router = new Router($container);
// $router = require __DIR__ . '/../config/router.php';

require __DIR__ . '/../routes/api.php';

$response = $router->dispatch($request);

$response->send();
