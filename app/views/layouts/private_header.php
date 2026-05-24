<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?></title>

    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <?php
        $usuarioSesion = current_user();

        function is_menu_active(string $route): string
        {
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $targetPath = rtrim(BASE_URL, '/') . '/' . ltrim($route, '/');

            if ($route === '/') {
                return rtrim($currentPath, '/') === rtrim(BASE_URL, '/') ? 'active' : '';
            }

            return str_starts_with($currentPath, $targetPath) ? 'active' : '';
        }
    ?>

    <div class="app-layout">
        <aside class="app-sidebar">
            <div class="logo-box">
                SOFT <span>RH</span>
            </div>

            <nav class="menu">
                <a class="<?= is_menu_active('/') ?>" href="<?= base_url('/') ?>">
                    Inicio
                </a>

                <?php if (user_has_role(['administrador'])): ?>
                    <a class="<?= is_menu_active('usuarios') ?>" href="<?= base_url('usuarios') ?>">
                        Gestión de usuarios
                    </a>
                <?php endif; ?>

                <?php if (user_has_role(['administrador', 'recursos_humanos'])): ?>
                    <a class="<?= is_menu_active('empleados') ?>" href="<?= base_url('empleados') ?>">
                        Gestión de empleados
                    </a>

                    <a class="<?= is_menu_active('contratos') ?>" href="<?= base_url('contratos') ?>">
                        Gestión de contratos
                    </a>

                    <a class="<?= is_menu_active('actas') ?>" href="<?= base_url('actas') ?>">
                        Actas administrativas
                    </a>

                    <a class="<?= is_menu_active('vacaciones') ?>" href="<?= base_url('vacaciones') ?>">
                        Vacaciones
                    </a>
                <?php endif; ?>

                <?php if (user_has_role(['administrador', 'psicologia'])): ?>
                    <a class="<?= is_menu_active('seguimientos') ?>" href="<?= base_url('seguimientos') ?>">
                        Seguimiento psicológico
                    </a>
                <?php endif; ?>

                <a class="<?= is_menu_active('mi-cuenta') ?>" href="<?= base_url('mi-cuenta') ?>">
                    Mi cuenta
                </a>

                <form method="POST" action="<?= base_url('logout') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-warning" type="submit" style="width: 100%; margin-top: 12px;">
                        Cerrar sesión
                    </button>
                </form>
            </nav>
        </aside>
        <main class="app-main">