<?php

declare(strict_types=1);

namespace App\Controllers\BigQuery;
use App\Http\Request;
use App\Http\Response;

final class BigQueryController
{
    public function orders(Request $request): Response
    {
        return Response::json([
            'mensagem' => 'Pedidos',
            'data' => [
                'pedidos' => [
                    [
                        'id' => 1,
                        'numero' => 'PED001',
                        'cliente' => 'Cliente 1',
                        'valor' => 100.00,
                        'status' => 'Pendente'
                    ],
                    [
                        'id' => 2,
                        'numero' => 'PED002',
                        'cliente' => 'Cliente 2',
                        'valor' => 200.00,
                        'status' => 'Aprovado'
                    ]
                ]
            ]
        ]);
    }
}