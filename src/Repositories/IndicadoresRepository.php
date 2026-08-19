<?php

declare(strict_types=1);

namespace App\Repositories;

use RuntimeException;

class IndicadoresRepository
{
    private object $connection;

    /**
     * @param array{host: string, database: string, username: string, password: string} $config
     */
    public function __construct(array $config, ?object $connection = null)
    {
        if ($connection !== null) {
            $this->connection = $connection;
            return;
        }

        $this->connection = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database']
        );

        if ($this->connection->connect_error) {
            throw new RuntimeException(
                'Falha ao conectar ao banco de dados: ' . $this->connection->connect_error
            );
        }

        $this->connection->set_charset('utf8');
    }

    public function existsByChatId(int $idChat): bool
    {
        $query = '
            SELECT 1
            FROM tb_neg_indicadores
            WHERE ID_CHAT = ?
        ';

        $stmt = $this->connection->prepare($query);

        if ($stmt === false) {
            throw new RuntimeException(
                'Erro ao preparar consulta de verificação do ID_CHAT.'
            );
        }

        $stmt->bind_param('i', $idChat);
        $stmt->execute();
        $stmt->store_result();

        $exists = $stmt->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    /**
     * @param array<int, array{indicador: string|null, conteudo: mixed}> $indicadores
     * @return array{success: bool, affected_rows: int, database_error: bool}
     */
    public function insert(int $idChat, ?string $numero, array $indicadores): array
    {
        $indicatorFields = [];
        $contentFields = [];

        for ($i = 1; $i <= 10; $i++) {
            $indicatorFields[] = "INDICADOR_{$i}";
            $contentFields[] = "CONTEUDO_{$i}";
        }

        $fields = array_merge(['ID_CHAT', 'NUMERO'], $indicatorFields, $contentFields);
        $query = sprintf(
            'INSERT INTO tb_neg_indicadores (%s) VALUES (%s)',
            implode(', ', $fields),
            implode(', ', array_fill(0, count($fields), '?'))
        );
        $stmt = $this->connection->prepare($query);

        if ($stmt === false) {
            throw new RuntimeException('Erro ao preparar inserção de indicadores.');
        }

        $indicadores = array_slice(array_values($indicadores), 0, 10);
        $values = [$idChat, $numero];
        for ($i = 1; $i <= 10; $i++) {
            $item = $indicadores[$i - 1] ?? [];
            $values[] = $item['indicador'] ?? null;
        }
        for ($i = 1; $i <= 10; $i++) {
            $item = $indicadores[$i - 1] ?? [];
            $conteudo = $item['conteudo'] ?? null;
            $values[] = $conteudo === null ? null : (string) $conteudo;
        }

        $types = 'i' . str_repeat('s', count($values) - 1);
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            error_log(sprintf('Erro ao inserir indicadores: [%d] %s', $stmt->errno, $stmt->error));
            $stmt->close();

            return ['success' => false, 'affected_rows' => 0, 'database_error' => true];
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return [
            'success' => $affectedRows > 0,
            'affected_rows' => $affectedRows,
            'database_error' => false,
        ];
    }

    public function __destruct()
    {
        if (isset($this->connection) && method_exists($this->connection, 'close')) {
            $this->connection->close();
        }
    }
}
