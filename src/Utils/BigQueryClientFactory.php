<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

final class BigQueryClientFactory
{
    public function __construct(private array $config = [])
    {
        if ($this->config === []) {
            $configFile = __DIR__ . '/../../config/config.php';
            $this->config = require $configFile;
        }
    }

    /** @return \Google\Cloud\BigQuery\BigQueryClient */
    public function make(string $contaAuth, string $projeto): object
    {
        if (!class_exists(\Google\Cloud\BigQuery\BigQueryClient::class)) {
            throw new RuntimeException(
                'A dependência google/cloud-bigquery não está instalada.'
            );
        }

        $credentials = $this->config['dadosGoogleCloud']['autenticacao'][$contaAuth] ?? null;

        if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new RuntimeException(
                sprintf('Credenciais do BigQuery não configuradas para a conta "%s".', $contaAuth)
            );
        }

        // Variáveis de ambiente normalmente guardam a chave com "\\n" literal.
        $credentials['private_key'] = str_replace('\\n', "\n", (string) $credentials['private_key']);

        return new \Google\Cloud\BigQuery\BigQueryClient([
            'projectId' => $projeto,
            'keyFile' => $credentials,
        ]);
    }
}
