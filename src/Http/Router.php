<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Container;

class Router
{
    private Container $container;
    private array $routes = [];
    private array $globalMiddlewares = [];
    private string $currentGroupPrefix = '';
    private array $currentGroupMiddlewares = [];
    private ?RouteGroup $activeGroup = null;
    private string $currentGroupCase = '';

    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? new Container();
    }

    public function setContainer(Container $container): self
    {
        $this->container = $container;
        return $this;
    }

    public function addGlobalMiddleware(string $middlewareClass, ?Container $container = null): self
    {
        if ($container !== null) {
            $this->container = $container;
        }
        $this->globalMiddlewares[] = $middlewareClass;
        return $this;
    }

    public function group(string|array $attributes, callable $callback): RouteGroup
    {
        $prefix = '';
        $groupMiddlewares = [];
        $groupCase = null;

        if (is_string($attributes)) {
            $prefix = $attributes;
        } elseif (is_array($attributes)) {
            $prefix = $attributes['prefix'] ?? '';
            $groupMiddlewares = (array) ($attributes['middleware'] ?? []);
            $groupCase = $attributes['case'] ?? null;
        }

        $previousPrefix = $this->currentGroupPrefix;
        $previousGroupMiddlewares = $this->currentGroupMiddlewares;
        $previousActiveGroup = $this->activeGroup;
        $previousGroupCase = $this->currentGroupCase;

        $this->currentGroupPrefix =
            rtrim($this->currentGroupPrefix, '/') .
            '/' .
            trim($prefix, '/');

            
        $this->currentGroupMiddlewares = array_merge(
            $this->currentGroupMiddlewares,
            $groupMiddlewares
        );
            
        if ($groupCase !== null && $groupCase !== '') {
            $this->currentGroupCase = $groupCase;
        }

        $newGroup = new RouteGroup();

        $this->activeGroup = $newGroup;

        $callback($this);

        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddlewares = $previousGroupMiddlewares;
        $this->activeGroup = $previousActiveGroup;
        $this->currentGroupCase = $previousGroupCase;

        return $newGroup;
    }

    public function addRoute(string $method, string $path, mixed $handler): Route
    {
        $fullPath = rtrim($this->currentGroupPrefix, '/');
        $cleanPath = '/' . ltrim($path, '/');
        $finalPath = ($fullPath === '' ? '' : $fullPath) . $cleanPath;

        $route = new Route($method, $finalPath, $handler);

        // O atributo `case` do grupo é um contexto da rota, não um segmento
        // da URL. A aplicação o consome historicamente com o nome `caso`.
        if ($this->currentGroupCase !== '') {
            $route->defaults(['caso' => $this->currentGroupCase]);
        }

        if (!empty($this->currentGroupMiddlewares)) {
            $route->middleware(...$this->currentGroupMiddlewares);
        }

        $this->routes[] = $route;

        if ($this->activeGroup !== null) {
            $this->activeGroup->addRoute($route);
        }

        return $route;
    }

    public function get(string $path, mixed $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): Route
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, mixed $handler): Route
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function options(string $path, mixed $handler): Route
    {
        return $this->addRoute('OPTIONS', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // 1. Tratamento padrão de CORS OPTIONS global caso nenhuma rota específica trate
        if ($method === 'OPTIONS') {
            foreach ($this->routes as $route) {
                $params = [];
                if ($route->matches('OPTIONS', $uri, $params)) {
                    return $this->executeRoute($route, $request, $params);
                }
            }
            return $this->runGlobalMiddlewares($request, function (Request $req) {
                return new Response('', 200);
            });
        }

        // 2. Busca por rota correspondente
        foreach ($this->routes as $route) {
            $params = [];
            if ($route->matches($method, $uri, $params)) {
                return $this->executeRoute($route, $request, $params);
            }
        }

        return Response::json([
            'success' => false,
            'message' => 'Rota não encontrada'
        ], 404);
    }

    private function executeRoute(Route $route, Request $request, array $routeParams = []): Response
    {
        // Parâmetros presentes na URL têm precedência sobre valores fixos.
        $request->setRouteParams(array_merge($route->getDefaultParams(), $routeParams));

        $allMiddlewares = array_merge($this->globalMiddlewares, $route->getMiddlewares());

        $pipeline = array_reduce(
            array_reverse($allMiddlewares),
            function ($next, $middlewareClass) {
                return function (Request $req) use ($next, $middlewareClass) {
                    $middleware = new $middlewareClass();
                    return $middleware->handle($req, $next);
                };
            },
            function (Request $req) use ($route) {
                return $this->callHandler($route->getHandler(), $req);
            }
        );

        return $pipeline($request);
    }

    private function runGlobalMiddlewares(Request $request, callable $finalHandler): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->globalMiddlewares),
            function ($next, $middlewareClass) {
                return function (Request $req) use ($next, $middlewareClass) {
                    $middleware = new $middlewareClass();
                    return $middleware->handle($req, $next);
                };
            },
            $finalHandler
        );

        return $pipeline($request);
    }

    private function callHandler(mixed $handler, Request $request): Response
    {
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;

            try {
                $controller = $this->container->get($controllerClass);
            } catch (\Throwable $e) {
                return Response::json([
                    'success' => false,
                    'message' => 'Erro ao resolver controller.',
                    // Não exponha isso em produção
                    'error' => $e->getMessage(),
                ], 500);
            }

            $ref = new \ReflectionMethod($controller, $method);

            if ($ref->getNumberOfParameters() > 0) {
                return $controller->$method($request);
            }

            return $controller->$method();
        }

        if (is_callable($handler)) {
            return $handler($request);
        }

        return Response::json([
            'success' => false,
            'message' => 'Handler de rota inválido'
        ], 500);
    }
}
