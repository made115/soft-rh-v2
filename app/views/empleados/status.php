<?php

$empleado = $empleado ?? null;
$errors = $errors ?? [];

require_once __DIR__ . '/../layouts/private_header.php';

$nombreCompleto = '';

if ($empleado) {
    $nombreCompleto = trim(
        $empleado['nombre_empleado'] . ' ' .
        $empleado['apellido_pat_empleado'] . ' ' .
        $empleado['apellido_mat_empleado']
    );
}
?>

<h1 class="page-title">Cambiar estado del empleado</h1>

<section class="panel-card form-card-wide empleado-status-page" x-data="{ modalInactivarEmpleado: false, modalReactivarEmpleado: false }">
    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <strong>Revisa la información:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$empleado): ?>
        <div class="alert-error">
            No se encontró información del empleado.
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= base_url('empleados') ?>">
                Volver al listado
            </a>
        </div>
    <?php else: ?>

        <div class="detail-header">
            <div>
                <h2 class="detail-title">
                    <?= e($nombreCompleto) ?>
                </h2>

                <p class="detail-subtitle">
                    <?= e($empleado['nombre_departamento']) ?> · <?= e($empleado['nombre_puesto']) ?>
                </p>
            </div>

            <div>
                <?php if ($empleado['estado_laboral'] === 'activo'): ?>
                    <span class="badge badge-activo empleado-status-badge">Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo empleado-status-badge">Inactivo</span>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" action="<?= base_url('empleados/actualizar-estado') ?>">
            <?= csrf_field() ?>

            <input
                type="hidden"
                name="id_empleado"
                value="<?= e((string) $empleado['id_empleado']) ?>"
            >

            <?php if ($empleado['estado_laboral'] === 'activo'): ?>
                <input type="hidden" name="accion_estado" value="inactivar">

                <div class="form-notice empleado-status-notice">
                    La fecha de baja se asignará automáticamente con la fecha actual. El expediente del empleado se conservará en el historial y, si tiene un contrato vigente, este se dará por terminado.
                </div>

                <div class="form-grid empleado-status-grid">
                    <div class="form-group form-group-full">
                        <label for="motivo_baja">Motivo de baja</label>
                        <textarea
                            id="motivo_baja"
                            name="motivo_baja"
                            rows="4"
                            maxlength="255"
                            required
                            placeholder="Describe el motivo por el cual se inactiva al empleado."
                        ><?= e($_POST['motivo_baja'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions empleado-status-actions">
                    <a class="btn btn-secondary" href="<?= base_url('empleados/detalle?id=' . (int) $empleado['id_empleado']) ?>">
                        Cancelar
                    </a>

                    <button class="btn btn-danger" type="button" @click.prevent="modalInactivarEmpleado = true">
                        Inactivar empleado
                    </button>
                </div>

                <div class="modal-overlay" x-show="modalInactivarEmpleado" x-cloak>
                    <div class="modal-card empleado-status-modal">
                        <h2>Confirmar inactivación</h2>

                        <p class="modal-text">
                            ¿Está seguro que quiere inactivar a este empleado?
                        </p>

                        <p class="modal-warning">
                            El empleado será marcado como inactivo, se asignará la fecha de baja actual y ya no aparecerá como empleado activo.
                        </p>

                        <p class="modal-user">
                            Empleado: <strong><?= e($nombreCompleto) ?></strong>
                        </p>

                        <div class="form-group">
                            <label for="contrasena_confirmacion_inactivar">Confirma tu contraseña</label>
                            <input
                                type="password"
                                id="contrasena_confirmacion_inactivar"
                                name="contrasena_confirmacion"
                                autocomplete="current-password"
                                required
                                placeholder="Ingresa tu contraseña para confirmar"
                            >
                        </div>

                        <div class="modal-actions">
                            <button class="btn btn-secondary" type="button" @click="modalInactivarEmpleado = false">
                                Cancelar
                            </button>

                            <button class="btn btn-danger" type="submit">
                                Inactivar empleado
                            </button>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <input type="hidden" name="accion_estado" value="reactivar">

                <div class="form-notice empleado-status-notice">
                    Al reactivar al empleado, se limpiará la fecha de baja y el motivo registrado. El empleado volverá a aparecer como activo dentro del sistema.
                </div>

                <div class="estado-baja-summary">
                    <article class="estado-baja-card">
                        <span>Fecha de baja</span>
                        <strong>
                            <?= !empty($empleado['fecha_baja']) ? e($empleado['fecha_baja']) : 'Sin fecha registrada' ?>
                        </strong>
                    </article>

                    <article class="estado-baja-card estado-baja-card-wide">
                        <span>Motivo de baja</span>
                        <strong>
                            <?= !empty($empleado['motivo_baja']) ? e($empleado['motivo_baja']) : 'Sin motivo registrado' ?>
                        </strong>
                    </article>
                </div>

                <div class="form-actions empleado-status-actions">
                    <a class="btn btn-secondary" href="<?= base_url('empleados/detalle?id=' . (int) $empleado['id_empleado']) ?>">
                        Cancelar
                    </a>

                    <button class="btn btn-warning" type="button" @click.prevent="modalReactivarEmpleado = true">
                        Reactivar empleado
                    </button>
                </div>

                <div class="modal-overlay" x-show="modalReactivarEmpleado" x-cloak>
                    <div class="modal-card empleado-status-modal">
                        <h2>Confirmar reactivación</h2>

                        <p class="modal-text">
                            ¿Está seguro que quiere reactivar a este empleado?
                        </p>

                        <p class="modal-warning">
                            El empleado volverá a estar activo. La fecha de baja y el motivo de baja serán limpiados del registro actual.
                        </p>

                        <p class="modal-user">
                            Empleado: <strong><?= e($nombreCompleto) ?></strong>
                        </p>

                        <div class="form-group">
                            <label for="contrasena_confirmacion_reactivar">Confirma tu contraseña</label>
                            <input
                                type="password"
                                id="contrasena_confirmacion_reactivar"
                                name="contrasena_confirmacion"
                                autocomplete="current-password"
                                required
                                placeholder="Ingresa tu contraseña para confirmar"
                            >
                        </div>

                        <div class="modal-actions">
                            <button class="btn btn-secondary" type="button" @click="modalReactivarEmpleado = false">
                                Cancelar
                            </button>

                            <button class="btn btn-warning" type="submit">
                                Reactivar empleado
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>