<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;

class TesteController
{
    public function index(): Response
    {
        return Response::json([
            'success' => true,
            'message' => 'API funcionando'
        ]);
    }
}
