<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Utils\TokenValidator;

final class AuthMiddleware implements MiddlewareInterface
{
    private TokenValidator $tokenValidator;

    public function __construct(?TokenValidator $tokenValidator = null)
    {
        $this->tokenValidator = $tokenValidator ?? new TokenValidator();
    }

    public function handle(
        Request $request,
        callable $next,
        string $caso = '',
        string $empresa = ''
    ): Response {
        $routeCaso = (string) ($request->route('caso') ?? $request->get('caso') ?? $caso);
        $routeEmpresa = (string) ($request->route('empresa') ?? $request->get('empresa') ?? $empresa);

        $authHeader = $request->header('Auth') ?? $request->header('Authorization');

        if (!$this->tokenValidator->validate($authHeader, $routeCaso, $routeEmpresa)) {
            return Response::json(
                [
                    'mensagem' => 'Acesso não autorizado. Token inválido ou ausente.'
                ],
                401,
                [
                    'WWW-Authenticate' => 'Bearer'
                ]
            );
        }

        return $next($request);
    }
}