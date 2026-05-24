<?php

$usuario = $usuario ?? [];
$errors = $errors ?? [];

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Restablecer contraseña</h1>

<section class="panel-card">
    <div class="form-card">
        <h2>Contraseña temporal</h2>

        <p>
            Se asignará una nueva contraseña temporal al usuario:
            <strong><?= e($usuario['nombre_usuario'] ?? '') ?></strong>
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('usuarios/actualizar-contrasena') ?>">
            <?= csrf_field() ?>

            <input
                type="hidden"
                name="id_usuario"
                value="<?= e((string) ($usuario['id_usuario'] ?? '')) ?>"
            >

            <div class="form-grid">
                <div class="form-group">
                    <label for="contrasena">Nueva contraseña temporal</label>
                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirmar_contrasena">Confirmar contraseña</label>
                    <input
                        type="password"
                        id="confirmar_contrasena"
                        name="confirmar_contrasena"
                        autocomplete="new-password"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-warning" type="submit">
                    Guardar contraseña
                </button>

                <a class="btn btn-secondary" href="<?= base_url('usuarios') ?>">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>