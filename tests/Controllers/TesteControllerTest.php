<?php

declare(strict_types=1);

namespace Tests\Controllers;

use App\Controllers\TesteController;
use App\Http\Response;
use PHPUnit\Framework\TestCase;

class TesteControllerTest extends TestCase
{
    public function testIndexReturnsSuccessMessageJsonResponse(): void
    {
        $controller = new TesteController();
        $response = $controller->index();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $expectedJson = json_encode([
            'success' => true,
            'message' => 'API funcionando'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->assertSame($expectedJson, $response->getBody());
    }
}
