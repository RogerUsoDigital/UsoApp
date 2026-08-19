<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

final class CorsMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins = [
        'https://rabbitman.usodigital.app',
        'https://usodigital.cloud',
        'https://usodigital.pro',
        'http://localhost',
    ];

    public function handle(Request $request, callable $next): Response
    {
        $origin = $request->header('Origin') ?? $_SERVER['HTTP_ORIGIN'] ?? '*';
        $allowOrigin = '*';

        if ($origin !== '*') {
            foreach ($this->allowedOrigins as $allowed) {
                if (str_starts_with($origin, $allowed)) {
                    $allowOrigin = $origin;
                    break;
                }
            }
        }

        $corsHeaders = [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => 'OPTIONS, GET, POST, PUT, DELETE',
            'Access-Control-Max-Age' => '3600',
            'Access-Control-Allow-Headers' => 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Auth',
        ];

        if (strtoupper($request->method()) === 'OPTIONS') {
            return new Response('', 200, $corsHeaders);
        }

        $response = $next($request);

        foreach ($corsHeaders as $key => $value) {
            header("{$key}: {$value}");
        }

        return $response;
    }
}
