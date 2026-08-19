<?php

declare(strict_types=1);

namespace Tests\Services\BigQuery;

use PHPUnit\Framework\TestCase;
use App\Services\BigQuery\IndicadoresChatService;
use App\Repositories\BigQueryRepository;
use App\Repositories\IndicadoresRepository;

class IndicadoresChatServiceTest extends TestCase
{
    private array $configMock;

    protected function setUp(): void
    {
        $this->configMock = [
            'dadosGoogleCloud' => [
                'conta'        => ['empresa_test' => 'account_1'],
                'projeto'      => ['empresa_test' => 'project_1'],
                'autenticacao' => ['account_1'    => '/path/to/key.json'],
            ],
            'portals' => [
                'meuportal-online' => [
                    'host'     => 'host-portal',
                    'database' => 'db_portal',
                    'username' => 'user_portal',
                    'password' => 'pass_portal',
                ],
                'usodigital-net' => [
                    'host'     => 'host-net',
                    'database' => 'db_net',
                    'username' => 'user_net',
                    'password' => 'pass_net',
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Config sem portals → service retorna erro de banco antes de conectar */
    private function configSemPortal(): array
    {
        return ['dadosGoogleCloud' => $this->configMock['dadosGoogleCloud']];
    }

    /**
     * Cria um service com IndicadoresRepository mockado injetado via reflexão,
     * contornando a criação da conexão mysqli real.
     */
    private function serviceComRepositoryMock(
        BigQueryRepository $bigQueryMock,
        IndicadoresRepository $repositoryMock
    ): IndicadoresChatService {
        $service = new class ($bigQueryMock, $this->configMock, $repositoryMock)
            extends IndicadoresChatService
        {
            private IndicadoresRepository $repositoryOverride;

            public function __construct(
                BigQueryRepository $bigQuery,
                array $config,
                IndicadoresRepository $repositoryOverride
            ) {
                parent::__construct($bigQuery, $config);
                $this->repositoryOverride = $repositoryOverride;
            }

            protected function criarRepositoryIndicadores(array $configBanco): IndicadoresRepository
            {
                return $this->repositoryOverride;
            }
        };

        return $service;
    }

    // -------------------------------------------------------------------------
    // Testes de indicadores (BigQuery)
    // -------------------------------------------------------------------------

    public function testIndicadorDesconhecidoEhIgnorado(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $bigQueryMock->expects($this->never())->method('query');

        $service = new IndicadoresChatService($bigQueryMock, $this->configSemPortal());

        $resultado = $service->executar([
            'cliente'   => 'cli123',
            'numero'    => '5511999999999',
            'indicador' => '[indicador_invalido]',
        ], 'empresa_test');

        $this->assertArrayHasKey('variables', $resultado);
    }

    // -------------------------------------------------------------------------
    // Testes de obterConfiguracaoBanco / portal
    // -------------------------------------------------------------------------

    public function testPortalValidoRetornaConfiguracaoCorreta(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $service = new IndicadoresChatService($bigQueryMock, $this->configMock);

        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('obterConfiguracaoBanco');

        $config = $method->invoke($service, 'meuportal-online');

        $this->assertIsArray($config);
        $this->assertEquals('host-portal', $config['host']);
        $this->assertEquals('db_portal',   $config['database']);
        $this->assertEquals('user_portal', $config['username']);
        $this->assertArrayHasKey('password', $config);
    }

    public function testPortalInexistenteRetornaNull(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $service = new IndicadoresChatService($bigQueryMock, $this->configMock);

        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('obterConfiguracaoBanco');

        $this->assertNull($method->invoke($service, 'portal-inexistente'));
    }

    public function testPortalInexistenteRetornaErroNoExecutar(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $bigQueryMock->method('query')->willReturn([['total' => 1]]);

        $service = new IndicadoresChatService($bigQueryMock, $this->configMock);

        $resultado = $service->executar([
            'cliente'   => 'cli123',
            'numero'    => '5511999999999',
            'indicador' => '[rechamada_hoje]',
            'portal'    => 'portal-inexistente',
        ], 'empresa_test');

        $this->assertEquals(200, $resultado['http_status']);
        $this->assertEquals('400', $resultado['variables']['error_code']);
        $this->assertStringContainsString(
            'portal',
            strtolower($resultado['variables']['constulta_indicadores_msg'])
        );
    }

    // -------------------------------------------------------------------------
    // Testes de montarBodyFinal
    // -------------------------------------------------------------------------

    public function testBodyFinalContemIdChat(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $service = new IndicadoresChatService($bigQueryMock, $this->configMock);

        $ref    = new \ReflectionClass($service);
        $method = $ref->getMethod('montarBodyFinal');

        $body = $method->invoke($service, 42, '5511999999999', ['rechamada_hoje' => 3]);
        $this->assertEquals(42, $body['id_chat']);
    }

    public function testBodyFinalContemNumero(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $service = new IndicadoresChatService($bigQueryMock, $this->configMock);

        $ref    = new \ReflectionClass($service);
        $method = $ref->getMethod('montarBodyFinal');

        $body = $method->invoke($service, null, '5511999999999', []);
        $this->assertEquals('5511999999999', $body['numero']);
    }

    public function testBodyFinalContemIndicadoresDosBigQuery(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $service = new IndicadoresChatService($bigQueryMock, $this->configMock);

        $ref    = new \ReflectionClass($service);
        $method = $ref->getMethod('montarBodyFinal');

        $indicadores = ['rechamada_hoje' => 5, 'ultimo_nps' => '9'];
        $body = $method->invoke($service, 10, '5511999999999', $indicadores);

        $this->assertEquals($indicadores, $body['indicadores']);
    }

    // -------------------------------------------------------------------------
    // Testes de resolverIdChat
    // -------------------------------------------------------------------------

    public function testResolverIdChatComValorValido(): void
    {
        $service = new IndicadoresChatService(
            $this->createMock(BigQueryRepository::class),
            $this->configMock
        );

        $ref = new \ReflectionClass($service);
        $m   = $ref->getMethod('resolverIdChat');

        $this->assertEquals(123, $m->invoke($service, 123));
        $this->assertEquals(123, $m->invoke($service, '123'));
    }

    public function testResolverIdChatComValorInvalido(): void
    {
        $service = new IndicadoresChatService(
            $this->createMock(BigQueryRepository::class),
            $this->configMock
        );

        $ref = new \ReflectionClass($service);
        $m   = $ref->getMethod('resolverIdChat');

        $this->assertNull($m->invoke($service, null));
        $this->assertNull($m->invoke($service, ''));
        $this->assertNull($m->invoke($service, 0));
        $this->assertNull($m->invoke($service, 'abc'));
    }

    // -------------------------------------------------------------------------
    // Testes de prepararIndicadoresParaInsert
    // -------------------------------------------------------------------------

    public function testPrepararIndicadoresRetornaEstruturaDe10Posicoes(): void
    {
        $service = new IndicadoresChatService(
            $this->createMock(BigQueryRepository::class),
            $this->configMock
        );

        $ref = new \ReflectionClass($service);
        $m   = $ref->getMethod('prepararIndicadoresParaInsert');

        $resultado = $m->invoke($service, ['rechamada_hoje' => 5, 'ultimo_nps' => '9']);

        $this->assertCount(10, $resultado);
        $this->assertArrayHasKey(1, $resultado);
        $this->assertArrayHasKey(10, $resultado);
    }

    public function testPrepararIndicadoresMapeiaCorretamente(): void
    {
        $service = new IndicadoresChatService(
            $this->createMock(BigQueryRepository::class),
            $this->configMock
        );

        $ref = new \ReflectionClass($service);
        $m   = $ref->getMethod('prepararIndicadoresParaInsert');

        $resultado = $m->invoke($service, ['rechamada_hoje' => 10, 'ultimo_nps' => '9']);

        $this->assertEquals('rechamada_hoje', $resultado[1]['indicador']);
        $this->assertEquals(10,               $resultado[1]['conteudo']);
        $this->assertEquals('ultimo_nps',     $resultado[2]['indicador']);
        $this->assertEquals('9',              $resultado[2]['conteudo']);
    }

    public function testPosicoesRestantesFilledComNull(): void
    {
        $service = new IndicadoresChatService(
            $this->createMock(BigQueryRepository::class),
            $this->configMock
        );

        $ref = new \ReflectionClass($service);
        $m   = $ref->getMethod('prepararIndicadoresParaInsert');

        $resultado = $m->invoke($service, ['rechamada_hoje' => 3]);

        $this->assertNull($resultado[2]['indicador']);
        $this->assertNull($resultado[2]['conteudo']);
        $this->assertNull($resultado[10]['indicador']);
    }

    public function testLimiteDe10IndicadoresEhRespeitado(): void
    {
        $service = new IndicadoresChatService(
            $this->createMock(BigQueryRepository::class),
            $this->configMock
        );

        $ref = new \ReflectionClass($service);
        $m   = $ref->getMethod('prepararIndicadoresParaInsert');

        $onze = [];
        for ($i = 1; $i <= 11; $i++) {
            $onze["indicador_{$i}"] = $i;
        }

        $resultado = $m->invoke($service, $onze);

        $this->assertCount(10, $resultado);
        // 11º indicador não deve aparecer
        $chaves = array_column($resultado, 'indicador');
        $this->assertNotContains('indicador_11', $chaves);
    }

    public function testDadosAdicionaisEhExcluidoDosIndicadoresPreparados(): void
    {
        $service = new IndicadoresChatService(
            $this->createMock(BigQueryRepository::class),
            $this->configMock
        );

        $ref = new \ReflectionClass($service);
        $m   = $ref->getMethod('prepararIndicadoresParaInsert');

        $resultado = $m->invoke($service, [
            'rechamada_hoje'  => 5,
            'dados_adicionais'=> ['origem' => 'web'],
        ]);

        $chaves = array_column($resultado, 'indicador');
        $this->assertNotContains('dados_adicionais', $chaves);
    }

    public function testIdChatDuplicadoImpedeCompletamenteOInsert(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $bigQueryMock->method('query')->willReturn([['total' => 1]]);
        $repositoryMock = $this->createMock(IndicadoresRepository::class);
        $repositoryMock->expects($this->once())->method('existsByChatId')->with(42)->willReturn(true);
        $repositoryMock->expects($this->never())->method('insert');

        $resultado = $this->serviceComRepositoryMock($bigQueryMock, $repositoryMock)->executar([
            'cliente' => 'cli', 'numero' => '5511', 'indicador' => '[rechamada_hoje]',
            'portal' => 'meuportal-online', 'id_chat' => 42,
        ], 'empresa_test');

        $this->assertSame('409', $resultado['variables']['error_code']);
    }

    public function testInsertBemSucedidoMantemContratoLegadoDaResposta(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $bigQueryMock->method('query')->willReturn([['total' => 1]]);
        $repositoryMock = $this->createMock(IndicadoresRepository::class);
        $repositoryMock->method('existsByChatId')->willReturn(false);
        $repositoryMock->expects($this->once())->method('insert')
            ->with(42, '5511', $this->isType('array'))
            ->willReturn(['success' => true, 'affected_rows' => 1, 'database_error' => false]);

        $resultado = $this->serviceComRepositoryMock($bigQueryMock, $repositoryMock)->executar([
            'cliente' => 'cli', 'numero' => '5511', 'indicador' => '[rechamada_hoje]',
            'portal' => 'meuportal-online', 'id_chat' => 42,
        ], 'empresa_test');

        $this->assertSame('true', $resultado['variables']['constulta_indicadores_status']);
        $this->assertSame('Dados inseridos com sucesso.', $resultado['variables']['consulta_indicadores_resposta']);
    }

    public function testInsertSemAlteracaoMantemMensagemLegada(): void
    {
        $bigQueryMock = $this->createMock(BigQueryRepository::class);
        $bigQueryMock->method('query')->willReturn([['total' => 1]]);
        $repositoryMock = $this->createMock(IndicadoresRepository::class);
        $repositoryMock->method('existsByChatId')->willReturn(false);
        $repositoryMock->method('insert')
            ->willReturn(['success' => false, 'affected_rows' => 0, 'database_error' => false]);

        $resultado = $this->serviceComRepositoryMock($bigQueryMock, $repositoryMock)->executar([
            'cliente' => 'cli', 'numero' => '5511', 'indicador' => '[rechamada_hoje]',
            'portal' => 'meuportal-online', 'id_chat' => 42,
        ], 'empresa_test');

        $this->assertSame(
            'Nenhum dado inserido ou erro na inserção.',
            $resultado['variables']['consulta_indicadores_resposta']
        );
    }
}
