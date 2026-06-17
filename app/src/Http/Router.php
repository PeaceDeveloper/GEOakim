<?php

declare(strict_types=1);

namespace App\Http;

use App\Bootstrap;

final class Router
{
    /** @var array<int, array{methods: string[], pattern: string, handler: callable, auth: bool}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler, bool $auth = false): void
    {
        $this->add(['GET'], $pattern, $handler, $auth);
    }

    public function post(string $pattern, callable $handler, bool $auth = false): void
    {
        $this->add(['POST'], $pattern, $handler, $auth);
    }

    public function patch(string $pattern, callable $handler, bool $auth = false): void
    {
        $this->add(['PATCH'], $pattern, $handler, $auth);
    }

    public function delete(string $pattern, callable $handler, bool $auth = false): void
    {
        $this->add(['DELETE'], $pattern, $handler, $auth);
    }

    /** @param string[] $methods */
    public function add(array $methods, string $pattern, callable $handler, bool $auth = false): void
    {
        $this->routes[] = [
            'methods' => $methods,
            'pattern' => $pattern,
            'handler' => $handler,
            'auth' => $auth,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'], true)) {
                continue;
            }

            $params = $this->match($route['pattern'], $path);
            if ($params === null) {
                continue;
            }

            if ($route['auth']) {
                Middleware\RequireAuthMiddleware::handle($path);
            }

            ($route['handler'])(...array_values($params));
            return;
        }

        http_response_code(404);
        Bootstrap::view('errors/not_found');
    }

    /** @return array<string, string>|null */
    private function match(string $pattern, string $path): ?array
    {
        if ($pattern === $path) {
            return [];
        }

        $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
