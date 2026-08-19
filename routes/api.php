<?php

declare(strict_types=1);

use App\Controllers\BigQuery\BigQueryController;
use App\Controllers\BigQuery\ConsultaController;
use App\Controllers\BigQuery\GlobalController;
use App\Controllers\SwaggerController;
use App\Controllers\TesteController;
use App\Http\Router;
use App\Middleware\AuthMiddleware;

/** @var \App\Http\Router $router */

$router->get('/teste', [TesteController::class, 'index']);
$router->get('/swagger', [SwaggerController::class, 'index']);

$router->group('/APIv3', function (Router $router) {

    $router->get('/bigquery/orders', [BigQueryController::class, 'orders'])
        ->middleware(AuthMiddleware::class);

    $router->group('/bigquery', function (Router $router) {
        $router->group(['prefix' => '/global', 'case' => 'bigquery'], function (Router $router) {

        });

        $router->group(['prefix' => '/consulta', 'case' => 'bigquery'], function (Router $router) {
            $router->post('/indicadores-chats-finalizados/{empresa}', [ConsultaController::class, 'indicadoresChatsFinalizados']);
            $router->post('/rechamadasNps/{empresa}', [ConsultaController::class, 'rechamadasNps']);
            $router->post('/bd-nps/{empresa}', [ConsultaController::class, 'bdNps']);
            $router->post('/resposta-sms/{empresa}', [ConsultaController::class, 'respostaSms']);
            $router->post('/helpdesk/{empresa}', [ConsultaController::class, 'helpdesk']);
            $router->post('/status-meta/{empresa}', [ConsultaController::class, 'statusMeta']);
        })->middleware(AuthMiddleware::class);

    });


});
