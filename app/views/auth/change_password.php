<?php

$errors = $errors ?? [];

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Cambio obligatorio de contraseña</h1>

<section class="panel-card">
    <div class="form-card">
        <h2>Actualiza tu contraseña</h2>

        <p>
            Por seguridad, debes cambiar tu contraseña temporal antes de continuar usando el sistema.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('actualizar-mi-contrasena') ?>">
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="contrasena">Nueva contraseña</label>
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
                    Guardar nueva contraseña
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>