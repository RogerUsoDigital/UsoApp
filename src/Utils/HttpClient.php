<?php

declare(strict_types=1);

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class HttpClient
{
    private Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client($config);
    }

    public function get(string $url, array $options = []): GuzzleResponse
    {
        return $this->client->get($url, $options);
    }

    public function post(string $url, array $options = []): GuzzleResponse
    {
        return $this->client->post($url, $options);
    }

    public function put(string $url, array $options = []): GuzzleResponse
    {
        return $this->client->put($url, $options);
    }

    public function delete(string $url, array $options = []): GuzzleResponse
    {
        return $this->client->delete($url, $options);
    }
}
