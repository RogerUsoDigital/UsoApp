<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utils\BigQueryClientFactory;

class BigQueryRepository
{
    public function __construct(
        // private ?BigQueryClientFactory $clientFactory = null
    ) {}

    public function query(
        string $contaAuth,
        string $projeto,
        string $sql,
        array $params = []
    ): array {
        // if ($this->clientFactory === null) {
        //     return [];
        // }

        // $bigQuery = $this->clientFactory->make($contaAuth, $projeto);
        // $queryJob = $bigQuery->query($sql);

        // if (!empty($params)) {
        //     $queryJob->parameters($params);
        // }

        // $results = $bigQuery->runQuery($queryJob);

        $data = [];
        // foreach ($results as $row) {
        //     $data[] = $row;
        // }

        return $data;
    }
}