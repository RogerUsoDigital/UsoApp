<?php

declare(strict_types=1);

namespace Tests\Services\BigQuery;

use App\Repositories\BigQueryRepository;
use App\Services\BigQuery\RechamadasNpsService;
use PHPUnit\Framework\TestCase;

class RechamadasNpsServiceTest extends TestCase
{
    private array $config = [
        'dadosGoogleCloud' => [
            'conta' => ['empresa_test' => 'conta_test'],
            'projeto' => ['empresa_test' => 'projeto_test'],
            'autenticacao' => ['conta_test' => ['client_email' => 'teste@exemplo.com']],
        ],
    ];

    public function testRetornaErroQuandoCorpoEstaVazio(): void
    {
        $bigQuery = $this->createMock(BigQueryRepository::class);
        $bigQuery->expects($this->never())->method('query');

        $resultado = (new RechamadasNpsService($bigQuery, $this->config))
            ->executar([], 'empresa_test');

        $this->assertSame(400, $resultado['http_status']);
        $this->assertSame('O corpo da requisição está vazio.', $resultado['variables']['constulta_rechamadas_msg']);
    }

    public function testMontaConsultaComParametrosNomeadosMantendoFiltroDeNumeroEDocumento(): void
    {
        $bigQuery = $this->createMock(BigQueryRepository::class);
        $bigQuery->expects($this->once())
            ->method('query')
            ->with(
                'conta_test',
                'projeto_test',
                $this->callback(static fn (string $sql): bool =>
                    str_contains($sql, 'EMPRESA = @cliente')
                    && str_contains($sql, '(NUMERO = @numero OR DOCUMENTO = @documento)')
                ),
                ['cliente' => "cliente'", 'numero' => '5511999999999', 'documento' => '123']
            )
            ->willReturn([['resultado' => 'ok']]);

        $resultado = (new RechamadasNpsService($bigQuery, $this->config))
            ->executar([
                'cliente' => "cliente'",
                'numero' => '5511999999999',
                'documento' => '123',
            ], 'empresa_test');

        $this->assertSame(200, $resultado['http_status']);
        $this->assertSame([['resultado' => 'ok']], $resultado['variables']['constulta_rechamadas_dados']);
    }
}
