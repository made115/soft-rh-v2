<?php

class Router
{
    private array $routes = [];

    public function get(string $path, string $action): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $action;
    }

    public function post(string $path, string $action): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->getCurrentPath($uri);
        $action = $this->routes[$method][$path] ?? null;

        if ($action === null) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;
        }

        [$controller_name, $method_name] = explode('@', $action);

        if (!class_exists($controller_name)) {
            die('El controlador no existe: ' . htmlspecialchars($controller_name));
        }

        $controller = new $controller_name();

        if (!method_exists($controller, $method_name)) {
            die('El método no existe: ' . htmlspecialchars($method_name));
        }

        $controller->$method_name();
    }

    private function getCurrentPath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        $basePath = '/' . trim($basePath, '/');

        if ($basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $path = str_replace('/index.php', '', $path);

        return $this->normalizePath($path);
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}