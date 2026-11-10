<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private Request $request;
    private Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function any(string $path, $handler, array $middlewares = []): self
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares = []): self
    {
        $prefix = '';
        $groupMiddlewares = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $m = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $groupMiddlewares = array_merge($groupMiddlewares, $m);
            }
        }

        $fullPath = '/' . trim($prefix . '/' . trim($path, '/'), '/');
        $allMiddlewares = array_merge($groupMiddlewares, $middlewares);

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $fullPath,
            'handler' => $handler,
            'middlewares' => $allMiddlewares,
        ];

        return $this;
    }

    public function dispatch(): void
    {
        $method = $this->request->getMethod();
        $uri = $this->request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) {
                        $params[] = $v;
                    }
                }

                // Run middleware pipeline
                $this->runMiddlewares($route['middlewares'], function () use ($route, $params) {
                    $this->executeHandler($route['handler'], $params);
                });

                return;
            }
        }

        // No route matched -> 404
        $this->response->setStatusCode(404);
        $errorView = __DIR__ . '/../Views/errors/404.php';
        if (file_exists($errorView)) {
            extract(['title' => '404 - Halaman Tidak Ditemukan']);
            require $errorView;
        } else {
            echo "<h1>404 Not Found</h1><p>Halaman yang Anda tuju tidak ditemukan.</p>";
        }
        exit;
    }

    private function runMiddlewares(array $middlewares, callable $destination): void
    {
        $pipeline = array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function () use ($middleware, $next) {
                    if (is_callable($middleware)) {
                        return call_user_func($middleware, $this->request, $this->response, $next);
                    } elseif (is_string($middleware) && class_exists($middleware)) {
                        $instance = new $middleware();
                        return $instance->handle($this->request, $this->response, $next);
                    }
                    return $next();
                };
            },
            $destination
        );

        $pipeline();
    }

    private function executeHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, array_merge([$this->request, $this->response], $params));
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $action] = $handler;
            $controller = new $class($this->request, $this->response);
            call_user_func_array([$controller, $action], $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$class, $action] = explode('@', $handler);
            $controller = new $class($this->request, $this->response);
            call_user_func_array([$controller, $action], $params);
            return;
        }

        throw new \RuntimeException("Invalid route handler provided.");
    }
}

