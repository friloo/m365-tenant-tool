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

    public function delete(string $path, callable|array $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';

        if (in_array($method, ['POST', 'DELETE', 'PATCH'], true)) {
            // External REST API requests authenticate via X-Api-Key header
            // (validated by the controller). They never carry a session
            // CSRF token, so the form-style check must not apply there.
            $isApiCall = str_starts_with($uri, '/api/v1/');
            if (!$isApiCall && !\App\Core\Csrf::validate()) {
                http_response_code(419);
                if (!headers_sent()) {
                    header('Content-Type: text/html; charset=utf-8');
                }
                echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Sicherheitsfehler</title></head><body style="font-family:system-ui;text-align:center;padding:80px;"><h2>&#128274; CSRF-Schutz</h2><p>Ungültiges oder abgelaufenes Sicherheits-Token.</p><p>Bitte <a href="javascript:history.back()">gehe zurück</a> und versuche es erneut.</p></body></html>';
                exit;
            }
        }

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
        // Placeholders may be camelCase ({userId}, {policyId}, {noteId}, …), so
        // match \w+ — the previous [a-z_]+ silently failed to replace any
        // camelCase placeholder, leaving those routes permanently unmatchable.
        $regex = preg_replace('/\{\w+\}/', '([^/]+)', $pattern);
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
