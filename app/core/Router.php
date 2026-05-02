<?php

namespace App\Core;

/**
 * Router simples por método HTTP e regex de path interno (apos strip base).
 */
final class Router
{
    /** @var array<int, array{methods: string[], regex: string, handler: callable}> */
    private array $routes = [];

    /** Registra uma rota. */
    public function add(array $methods, string $pattern, callable $handler): void
    {
        $normalized = '#' . $pattern . '#u';
        $this->routes[] = [
            'methods' => array_map('strtoupper', $methods),
            'regex' => $normalized,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        foreach ($this->routes as $route) {
            if (! in_array($method, $route['methods'], true)) {
                continue;
            }
            if (preg_match($route['regex'], $path, $m)) {
                $args = array_values(array_slice($m, 1));
                ($route['handler'])(...$args);
                return;
            }
        }
        http_response_code(404);
        echo '<h1>Não encontrado</h1><p>Rota indefinida: ' . Helpers::e($path) . '</p>';
    }
}
