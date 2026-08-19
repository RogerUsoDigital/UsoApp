<?php

declare(strict_types=1);

namespace App\Http;

class Route
{
    private string $method;
    private string $path;
    private mixed $handler;
    private array $middlewares = [];
    private array $defaultParams = [];

    public function __construct(string $method, string $path, mixed $handler)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->handler = $handler;
    }

    public function middleware(string ...$middlewares): self
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Define parâmetros fixos associados à rota, sem que eles precisem fazer
     * parte da URL.
     */
    public function defaults(array $params): self
    {
        $this->defaultParams = array_merge($this->defaultParams, $params);
        return $this;
    }

    public function getDefaultParams(): array
    {
        return $this->defaultParams;
    }

    public function matches(string $method, string $uri, array &$params = []): bool
    {
        if ($this->method !== strtoupper($method)) {
            return false;
        }

        if ($this->path === $uri) {
            $params = [];
            return true;
        }

        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $this->path);
        $pattern = '#^' . $pattern . '$#i';

        if (preg_match($pattern, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }
}
