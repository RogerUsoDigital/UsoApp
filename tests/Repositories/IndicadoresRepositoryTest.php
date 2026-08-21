<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Repositories\Bigquery\IndicadoresRepository;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class IndicadoresRepositoryTest extends TestCase
{
    public function testInsertComUmIndicadorPreencheDemaisPosicoesComNull(): void
    {
        $connection = new FakeIndicadoresConnection();
        $repository = new IndicadoresRepository([], $connection);

        $result = $repository->insert(12, '5511999999999', [
            ['indicador' => 'rechamada_hoje', 'conteudo' => 10],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(22, substr_count($connection->query, '?'));
        $this->assertSame(12, $connection->statement->values[0]);
        $this->assertSame('5511999999999', $connection->statement->values[1]);
        $this->assertSame('rechamada_hoje', $connection->statement->values[2]);
        $this->assertNull($connection->statement->values[3]);
        $this->assertSame('10', $connection->statement->values[12]);
        $this->assertNull($connection->statement->values[13]);
        $this->assertNull($connection->statement->values[21]);
    }

    public function testInsertMantemOrdemDeMultiplosIndicadores(): void
    {
        $connection = new FakeIndicadoresConnection();
        $repository = new IndicadoresRepository([], $connection);

        $repository->insert(5, null, [
            ['indicador' => 'primeiro', 'conteudo' => 1],
            ['indicador' => 'segundo', 'conteudo' => '2'],
            ['indicador' => 'terceiro', 'conteudo' => 'texto'],
        ]);

        $this->assertSame(['primeiro', 'segundo', 'terceiro'], array_slice($connection->statement->values, 2, 3));
        $this->assertSame(['1', '2', 'texto'], array_slice($connection->statement->values, 12, 3));
    }

    public function testInsertComDezIndicadoresPreencheTodasAsPosicoes(): void
    {
        $connection = new FakeIndicadoresConnection();
        $repository = new IndicadoresRepository([], $connection);
        $indicadores = [];
        for ($i = 1; $i <= 10; $i++) {
            $indicadores[] = ['indicador' => "indicador_{$i}", 'conteudo' => $i];
        }

        $repository->insert(5, '55', $indicadores);

        $this->assertSame('indicador_10', $connection->statement->values[11]);
        $this->assertSame('10', $connection->statement->values[21]);
    }

    public function testInsertIgnoraIndicadoresAposODecimo(): void
    {
        $connection = new FakeIndicadoresConnection();
        $repository = new IndicadoresRepository([], $connection);
        $indicadores = [];
        for ($i = 1; $i <= 11; $i++) {
            $indicadores[] = ['indicador' => "indicador_{$i}", 'conteudo' => $i];
        }

        $repository->insert(5, '55', $indicadores);

        $this->assertSame('indicador_10', $connection->statement->values[11]);
        $this->assertNotContains('indicador_11', $connection->statement->values);
    }

    public function testErroNoPrepareEhControlado(): void
    {
        $connection = new FakeIndicadoresConnection();
        $connection->prepareResult = false;
        $repository = new IndicadoresRepository([], $connection);

        $this->expectException(RuntimeException::class);
        $repository->insert(5, '55', []);
    }

    public function testErroNoExecuteRetornaResultadoControlado(): void
    {
        $connection = new FakeIndicadoresConnection();
        $connection->statement->executeResult = false;
        $repository = new IndicadoresRepository([], $connection);

        $result = $repository->insert(5, '55', []);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['database_error']);
    }
}

final class FakeIndicadoresConnection
{
    public FakeIndicadoresStatement $statement;
    public FakeIndicadoresStatement|false|null $prepareResult = null;
    public string $query = '';

    public function __construct()
    {
        $this->statement = new FakeIndicadoresStatement();
    }

    public function prepare(string $query): FakeIndicadoresStatement|false
    {
        $this->query = $query;
        return $this->prepareResult ?? $this->statement;
    }

    public function close(): void {}
}

final class FakeIndicadoresStatement
{
    public array $values = [];
    public string $types = '';
    public bool $executeResult = true;
    public int $affected_rows = 1;
    public int $errno = 123;
    public string $error = 'erro simulado';

    public function bind_param(string $types, &...$values): bool
    {
        $this->types = $types;
        $this->values = $values;
        return true;
    }

    public function execute(): bool { return $this->executeResult; }
    public function close(): void {}
}
