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

<section class="panel-card form-card-wide">
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
                    <span class="badge badge-activo">Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo">Inactivo</span>
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

                <div class="form-notice">
                    La fecha de baja se asignará automáticamente con la fecha actual.
                </div>

                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label for="motivo_baja">Motivo de baja</label>
                        <textarea
                            id="motivo_baja"
                            name="motivo_baja"
                            rows="5"
                            maxlength="255"
                            required
                            placeholder="Describe el motivo por el cual se inactiva al empleado."
                        ><?= e($_POST['motivo_baja'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="<?= base_url('empleados/detalle?id=' . (int) $empleado['id_empleado']) ?>">
                        Cancelar
                    </a>

                    <button class="btn btn-danger" type="submit">
                        Inactivar empleado
                    </button>
                </div>
            <?php else: ?>
                <input type="hidden" name="accion_estado" value="reactivar">

                <div class="form-notice">
                    Al reactivar al empleado, se limpiará la fecha de baja y el motivo de baja.
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <span>Fecha de baja</span>
                        <strong><?= e($empleado['fecha_baja'] ?? 'No registrada') ?></strong>
                    </div>

                    <div class="detail-item detail-item-wide">
                        <span>Motivo de baja</span>
                        <strong><?= e($empleado['motivo_baja'] ?? 'No registrado') ?></strong>
                    </div>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="<?= base_url('empleados/detalle?id=' . (int) $empleado['id_empleado']) ?>">
                        Cancelar
                    </a>

                    <button class="btn btn-primary" type="submit">
                        Reactivar empleado
                    </button>
                </div>
            <?php endif; ?>
        </form>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>