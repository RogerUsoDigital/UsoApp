<?php

declare(strict_types=1);

namespace App\Controllers\BigQuery;

use App\Http\Request;
use App\Http\Response;

class GlobalController
{
    private string $metodo;
    private string $endpoint;

    public function __construct(string $metodo = '', string $endpoint = '')
    {
        $this->metodo = $metodo;
        $this->endpoint = $endpoint;
    }

    public function processaRequisicao(?Request $request = null): Response
    {
        $endpoint = $request ? $request->route('endpoint', $this->endpoint) : $this->endpoint;
        $caso = $request ? $request->route('caso', '') : '';
        $empresa = $request ? $request->route('empresa', '') : '';

        return Response::json([
            'success' => true,
            'controller' => 'global',
            'endpoint' => $endpoint,
            'caso' => $caso,
            'empresa' => $empresa,
            'message' => 'Requisição global processada com sucesso.'
        ]);
    }
}

class_alias(GlobalController::class, 'globalController');
