<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="login-wrapper">
    <form class="login-card" method="POST" action="<?= base_url('login') ?>" x-data="{ showPassword: false }">
        <h1>Iniciar sesión</h1>
        <p>Acceso al sistema SOFT RH V2</p>

        <?= csrf_field() ?>

        <label for="nombre_usuario">Usuario</label>
        <input 
            type="text" 
            id="nombre_usuario" 
            name="nombre_usuario" 
            autocomplete="username"
            required
        >

        <label for="contrasena">Contraseña</label>
        <div class="password-field">
            <input 
                :type="showPassword ? 'text' : 'password'"
                id="contrasena" 
                name="contrasena" 
                autocomplete="current-password"
                required
            >

            <button type="button" @click="showPassword = !showPassword">
                <span x-text="showPassword ? 'Ocultar' : 'Ver'"></span>
            </button>
        </div>

        <button class="btn" type="submit">Entrar</button>
    </form>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>