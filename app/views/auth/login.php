<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="login-wrapper login-v1-wrapper">
    <div class="login-v1-container">
        <div class="login-v1-left">
            <form class="login-v1-card" method="POST" action="<?= base_url('login') ?>">
                <div class="login-v1-logo">
                    <img src="<?= base_url('assets/img/logo-soft-rh.png') ?>" alt="Logo SOFT RH">
                </div>

                <h1>Bienvenido a SOFT RH</h1>
                <p>Inicia sesión para gestionar tus actividades</p>

                <?php if (!empty($success)): ?>
                    <div class="login-v1-alert login-v1-alert-success">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="login-v1-alert login-v1-alert-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?= csrf_field() ?>

                <label for="nombre_usuario">Nombre de usuario</label>
                <input
                    type="text"
                    id="nombre_usuario"
                    name="nombre_usuario"
                    autocomplete="username"
                    placeholder="Ej. admin.rh"
                    required
                >

                <label for="contrasena">Contraseña</label>
                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    required
                >

                <button class="btn login-v1-button" type="submit">Iniciar sesión</button>
            </form>
        </div>

        <div class="login-v1-image">
            <img src="<?= base_url('assets/img/login-rh.jpeg') ?>" alt="Imagen SOFT RH">
        </div>
    </div>

    <footer class="login-v1-footer">
        2026 SOFT RH Todos los derechos reservados
    </footer>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>