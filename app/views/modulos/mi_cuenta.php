<?php

$usuario = $usuario ?? current_user();
$passwordErrors = $passwordErrors ?? [];
$passwordSuccess = $passwordSuccess ?? null;

function rol_cuenta_label(string $rol): string
{
    return match ($rol) {
        'administrador' => 'Administrador',
        'recursos_humanos' => 'Recursos Humanos',
        'psicologia' => 'Psicología',
        default => ucfirst(str_replace('_', ' ', $rol)),
    };
}

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Mi cuenta</h1>

<section class="account-wrapper" x-data="{ modalPassword: false }">
    <div class="account-card account-card-wide">
        <div class="account-header">
            <div class="account-avatar">
                <?= e(strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1))) ?>
            </div>

            <div>
                <h2><?= e($usuario['nombre'] ?? '') ?></h2>
                <p><?= e($usuario['nombre_usuario'] ?? '') ?></p>
            </div>
        </div>

        <?php if (!empty($passwordSuccess)): ?>
            <div class="alert-success account-alert">
                <?= e($passwordSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($passwordErrors)): ?>
            <div class="alert-error account-alert">
                <strong>Revisa la información:</strong>
                <ul>
                    <?php foreach ($passwordErrors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="account-info-grid account-info-grid-compact">
            <div class="account-info-item">
                <span>Nombre</span>
                <strong><?= e($usuario['nombre'] ?? '') ?></strong>
            </div>

            <div class="account-info-item">
                <span>Usuario</span>
                <strong><?= e($usuario['nombre_usuario'] ?? '') ?></strong>
            </div>

            <div class="account-info-item">
                <span>Rol</span>
                <strong><?= e(rol_cuenta_label($usuario['nombre_rol'] ?? '')) ?></strong>
            </div>

            <div class="account-info-item">
                <span>Fecha de registro</span>
                <strong>
                    <?= !empty($usuario['fecha_registro']) ? e($usuario['fecha_registro']) : 'No disponible' ?>
                </strong>
            </div>
        </div>

        <form method="POST" action="<?= base_url('mi-cuenta/actualizar-contrasena') ?>" class="account-password-form">
            <?= csrf_field() ?>

            <h3>Cambiar contraseña</h3>

            <div class="form-grid account-password-grid">
                <div class="form-group">
                    <label for="contrasena">Nueva contraseña</label>
                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        autocomplete="new-password"
                        required
                        placeholder="Ingresa tu nueva contraseña"
                    >
                </div>

                <div class="form-group">
                    <label for="confirmar_contrasena">Confirmar nueva contraseña</label>
                    <input
                        type="password"
                        id="confirmar_contrasena"
                        name="confirmar_contrasena"
                        autocomplete="new-password"
                        required
                        placeholder="Confirma tu nueva contraseña"
                    >
                </div>
            </div>

            <div class="account-actions account-actions-password">
                <a class="btn btn-secondary" href="<?= base_url('/') ?>">Volver al inicio</a>

                <button class="btn btn-warning" type="button" @click="modalPassword = true">
                    Cambiar contraseña
                </button>
            </div>

            <div class="modal-overlay" x-show="modalPassword" x-cloak>
                <div class="modal-card">
                    <h2>Confirmar cambio de contraseña</h2>

                    <p class="modal-text">
                        Para establecer la nueva contraseña, confirma primero tu contraseña actual.
                    </p>

                    <p class="modal-warning">
                        Después de confirmar, la nueva contraseña será la que usarás para iniciar sesión.
                    </p>

                    <div class="form-group">
                        <label for="contrasena_actual">Contraseña actual</label>
                        <input
                            type="password"
                            id="contrasena_actual"
                            name="contrasena_actual"
                            autocomplete="current-password"
                            required
                            placeholder="Ingresa tu contraseña actual"
                        >
                    </div>

                    <div class="modal-actions">
                        <button class="btn btn-secondary" type="button" @click="modalPassword = false">
                            Cancelar
                        </button>

                        <button class="btn btn-warning" type="submit">
                            Confirmar cambio
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>