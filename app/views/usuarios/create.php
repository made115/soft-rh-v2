<?php

$roles = $roles ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Registrar usuario</h1>

<section class="panel-card">
    <div class="form-card">
        <h2>Nuevo usuario</h2>
        <p>Captura los datos del usuario que tendrá acceso al sistema.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('usuarios/guardar') ?>">
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?= e($old['nombre'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="nombre_usuario">Nombre de usuario</label>
                    <input
                        type="text"
                        id="nombre_usuario"
                        name="nombre_usuario"
                        value="<?= e($old['nombre_usuario'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="id_rol">Rol</label>
                    <select id="id_rol" name="id_rol" required>
                        <option value="">Selecciona un rol</option>

                        <?php foreach ($roles as $rol): ?>
                            <option
                                value="<?= e((string) $rol['id_rol']) ?>"
                                <?= ((int) ($old['id_rol'] ?? 0) === (int) $rol['id_rol']) ? 'selected' : '' ?>
                            >
                                <?= e($rol['nombre_rol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="activo" <?= (($old['estado'] ?? 'activo') === 'activo') ? 'selected' : '' ?>>
                            Activo
                        </option>
                        <option value="inactivo" <?= (($old['estado'] ?? '') === 'inactivo') ? 'selected' : '' ?>>
                            Inactivo
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="contrasena">Contraseña temporal</label>
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
                <button class="btn btn-warning" type="submit">Guardar usuario</button>
                <a class="btn btn-secondary" href="<?= base_url('usuarios') ?>">Cancelar</a>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>