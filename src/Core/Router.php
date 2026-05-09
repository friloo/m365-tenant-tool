<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $regex = $this->toRegex($pattern);
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                $params = array_values(array_filter($matches, 'strlen'));
                $this->call($handler, $params);
                return;
            }
        }

        http_response_code(404);
        if (file_exists(BASE_PATH . '/views/errors/404.php')) {
            require BASE_PATH . '/views/errors/404.php';
        } else {
            echo '<h1>404 — Not Found</h1>';
        }
    }

    private function toRegex(string $pattern): string
    {
        $regex = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $pattern);
        return '#^' . $regex . '$#i';
    }

    private function call(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $obj = new $class();
            $obj->$method(...$params);
        } else {
            $handler(...$params);
        }
    }
}
