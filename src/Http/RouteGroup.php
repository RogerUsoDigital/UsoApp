<?php

declare(strict_types=1);

namespace App\Http;

class RouteGroup
{
    /** @var Route[] */
    private array $routes = [];

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function middleware(string ...$middlewares): self
    {
        foreach ($this->routes as $route) {
            $route->middleware(...$middlewares);
        }
        return $this;
    }
}
