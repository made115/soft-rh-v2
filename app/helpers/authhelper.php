<?php

function is_logged_in(): bool
{
    return isset($_SESSION['usuario']);
}

function current_user(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function current_role(): ?string
{
    return $_SESSION['usuario']['nombre_rol'] ?? null;
}

function password_change_required(): bool
{
    return is_logged_in()
        && isset($_SESSION['usuario']['requiere_cambio_contrasena'])
        && (int) $_SESSION['usuario']['requiere_cambio_contrasena'] === 1;
}

function current_request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
    $basePath = '/' . trim($basePath, '/');

    if ($basePath !== '/' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
    }

    $path = '/' . trim($path, '/');

    return $path === '/' ? '/' : rtrim($path, '/');
}

function is_password_change_allowed_route(): bool
{
    $path = current_request_path();

    return in_array($path, [
        '/cambiar-contrasena',
        '/actualizar-mi-contrasena',
        '/logout'
    ], true);
}

function user_has_role($roles): bool
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    $rolActual = current_role();

    return $rolActual !== null && in_array($rolActual, $roles, true);
}

function require_auth(): void
{
    if (!is_logged_in()) {
        header('Location: ' . base_url('login'));
        exit;
    }

    if (password_change_required() && !is_password_change_allowed_route()) {
        header('Location: ' . base_url('cambiar-contrasena'));
        exit;
    }
}

function require_guest(): void
{
    if (is_logged_in()) {
        header('Location: ' . base_url('/'));
        exit;
    }
}

function require_role($roles): void
{
    require_auth();

    if (!user_has_role($roles)) {
        header('Location: ' . base_url('sin-permiso'));
        exit;
    }
}

function login_user(array $usuario): void
{
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id_usuario' => $usuario['id_usuario'],
        'id_rol' => $usuario['id_rol'],
        'nombre_rol' => $usuario['nombre_rol'],
        'nombre' => $usuario['nombre'],
        'nombre_usuario' => $usuario['nombre_usuario'],
        'requiere_cambio_contrasena' => $usuario['requiere_cambio_contrasena']
    ];
}

function mark_password_change_completed(): void
{
    if (isset($_SESSION['usuario'])) {
        $_SESSION['usuario']['requiere_cambio_contrasena'] = 0;
    }
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}