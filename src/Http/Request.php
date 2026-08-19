<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $queryParams;
    private ?string $rawBody;
    private array $parsedBody;
    private array $routeParams = [];

    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        array $queryParams = [],
        ?string $rawBody = null
    ) {
        $this->method = strtoupper($method);
        $this->uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $this->headers = $headers;
        $this->queryParams = $queryParams;
        $this->rawBody = $rawBody;
        $this->parsedBody = $this->parseBody();
    }

    public static function capture(): self
    {
        return self::createFromGlobals();
    }

    public static function createFromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];

        if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        if (!isset($headers['Auth']) && !isset($headers['auth'])) {
            if (isset($_SERVER['HTTP_AUTH'])) {
                $headers['Auth'] = $_SERVER['HTTP_AUTH'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTH'])) {
                $headers['Auth'] = $_SERVER['REDIRECT_HTTP_AUTH'];
            }
        }

        $queryParams = $_GET;
        $rawBody = file_get_contents('php://input') ?: null;

        return new self($method, $uri, $headers, $queryParams, $rawBody);
    }

    private function parseBody(): array
    {
        if ($this->rawBody === null || $this->rawBody === '') {
            return $_POST;
        }

        $contentType = $this->getHeader('Content-Type') ?? '';
        if (str_contains(strtolower($contentType), 'application/json')) {
            $json = json_decode($this->rawBody, true);
            return is_array($json) ? $json : [];
        }

        return $_POST;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function method(): string
    {
        return $this->getMethod();
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function uri(): string
    {
        return $this->getUri();
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        $normalizedName = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower((string) $key) === $normalizedName) {
                return (string) $value;
            }
        }
        return null;
    }

    public function header(string $name): ?string
    {
        return $this->getHeader($name);
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->getQuery($key, $default);
    }

    public function setRouteParams(array $params): self
    {
        $this->routeParams = $params;
        return $this;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }

    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    public function body(): mixed
    {
        return $this->getParsedBody();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->parsedBody[$key] ?? $this->queryParams[$key] ?? $this->routeParams[$key] ?? $default;
    }
}
