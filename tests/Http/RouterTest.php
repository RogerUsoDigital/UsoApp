<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use App\Http\Router;
use App\Http\Container;
use App\Http\Request;
use App\Http\Response;

class DummyController
{
    public function index(): Response
    {
        return new Response('ok', 200);
    }
}

class RouterTest extends TestCase
{
    public function testRouterInitializesContainerInConstructor(): void
    {
        $container = new Container();
        $router = new Router($container);

        $router->get('/test', [DummyController::class, 'index']);

        $request = new Request('GET', '/test');
        $response = $router->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }

    public function testRouterInitializesDefaultContainerWithoutArguments(): void
    {
        $router = new Router();

        $router->get('/test', [DummyController::class, 'index']);

        $request = new Request('GET', '/test');
        $response = $router->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }

    public function testGroupCaseIsAvailableAsRouteCasoWithoutBeingInTheUrl(): void
    {
        $router = new Router();

        $router->group(['prefix' => '/APIv3/consulta', 'case' => 'bigquery'], function (Router $router): void {
            $router->get('/clientes/{empresa}', static function (Request $request): Response {
                return Response::json([
                    'caso' => $request->route('caso'),
                    'empresa' => $request->route('empresa'),
                ]);
            });
        });

        $response = $router->dispatch(new Request('GET', '/APIv3/consulta/clientes/acme'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"caso":"bigquery","empresa":"acme"}',
            $response->getBody()
        );
    }
}
