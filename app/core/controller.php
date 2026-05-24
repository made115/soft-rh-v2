<?php

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $view_path = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($view_path)) {
            die('La vista no existe: ' . htmlspecialchars($view));
        }

        require_once $view_path;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . base_url($path));
        exit;
    }
}