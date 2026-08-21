<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utils\BigQueryClientFactory;
use RuntimeException;

class BigQueryRepository
{
    public function __construct(
        private ?BigQueryClientFactory $clientFactory = null
    ) {
        $this->clientFactory ??= new BigQueryClientFactory();
    }

    public function query(
        string $contaAuth,
        string $projeto,
        string $sql,
        array $params = []
    ): array {
        $bigQuery = $this->clientFactory->make($contaAuth, $projeto);
        $queryJob = $bigQuery->query($sql);

        if (!empty($params)) {
            $queryJob->parameters($params);
        }

        $results = $bigQuery->runQuery($queryJob);

        if (!$results->isComplete()) {
            throw new RuntimeException('A consulta do BigQuery não foi concluída.');
        }

        $info = $results->info();
        if (!empty($info['status']['errorResult'])) {
            $message = $info['status']['errorResult']['message'] ?? 'Erro desconhecido ao consultar o BigQuery.';
            throw new RuntimeException((string) $message);
        }

        $data = [];
        foreach ($results as $row) {
            $data[] = $row;
        }

        return $data;
    }
}
