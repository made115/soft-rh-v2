<?php

$empleado = $empleado ?? null;
$historialLaboral = $historialLaboral ?? [];
$ultimoMovimiento = $ultimoMovimiento ?? null;

require_once __DIR__ . '/../layouts/private_header.php';

$nombreCompleto = '';
if ($empleado) {
    $nombreCompleto = trim(
        $empleado['nombre_empleado'] . ' ' .
        $empleado['apellido_pat_empleado'] . ' ' .
        $empleado['apellido_mat_empleado']
    );
}

function mostrarDatoEmpleado($valor): string
{
    if ($valor === null || $valor === '') {
        return 'No registrado';
    }

    return e((string) $valor);
}

function formatoSexoEmpleado(string $sexo): string
{
    return match ($sexo) {
        'masculino' => 'Masculino',
        'femenino' => 'Femenino',
        'no_especificado' => 'No especificado',
        default => 'No registrado'
    };
}

function formatoEstadoLaboral(string $estado): string
{
    return match ($estado) {
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        default => 'No registrado'
    };
}

function formatoTipoAltaEmpleado(string $tipoAlta): string
{
    return match ($tipoAlta) {
        'registro' => 'Registro inicial',
        'reactivacion' => 'Reactivación',
        default => 'No registrado'
    };
}

function formatoEstadoPeriodoEmpleado(string $estadoPeriodo): string
{
    return match ($estadoPeriodo) {
        'activo' => 'Activo',
        'cerrado' => 'Cerrado',
        default => 'No registrado'
    };
}

function formatoAccionEmpleado(string $accion): string
{
    return match ($accion) {
        'registro' => 'Registro de empleado',
        'edicion' => 'Edición de información',
        'inactivacion' => 'Inactivación de empleado',
        'reactivacion' => 'Reactivación de empleado',
        default => ucfirst(str_replace('_', ' ', $accion))
    };
}
?>



<h1 class="page-title">Detalle del empleado</h1>

<section class="panel-card empleado-detail-page">
    <?php if (!$empleado): ?>
        <div class="alert-error">
            No se encontró información del empleado.
        </div>

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="alert-success">
                Empleado actualizado correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['sin_cambios'])): ?>
            <div class="alert-success">
                No se realizaron cambios en la información del empleado.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['inactivado'])): ?>
            <div class="alert-success">
                Empleado inactivado correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['reactivado'])): ?>
            <div class="alert-success">
                Empleado reactivado correctamente.
            </div>
        <?php endif; ?>

        <div class="module-actions">
            <a class="btn btn-secondary" href="<?= base_url('empleados') ?>">
                Volver al listado
            </a>
        </div>
    <?php else: ?>

        <?php if (isset($_GET['creado'])): ?>
            <div class="alert-success">
                Empleado registrado correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="alert-success">
                Empleado actualizado correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['sin_cambios'])): ?>
            <div class="alert-success">
                No se realizaron cambios en la información del empleado.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['inactivado'])): ?>
            <div class="alert-success">
                Empleado inactivado correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['reactivado'])): ?>
            <div class="alert-success">
                Empleado reactivado correctamente.
            </div>
        <?php endif; ?>

        <?php if (
            isset($_GET['contrato_pendiente']) &&
            in_array($_GET['contrato_pendiente'], ['registro', 'reactivacion'], true) &&
            $empleado['estado_laboral'] === 'activo'
        ): ?>
            <div class="alert-success" style="display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap;">
                <div>
                    <strong>
                        <?= $_GET['contrato_pendiente'] === 'reactivacion'
                            ? 'Ahora puedes generar el contrato de reingreso.'
                            : 'Ahora puedes generar el contrato inicial del empleado.' ?>
                    </strong>
                    <br>
                    <span>
                        También puedes omitir este paso y generarlo después desde Gestión de contratos.
                    </span>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a
                        class="btn btn-primary"
                        href="<?= base_url('contratos/crear?id_empleado=' . (int) $empleado['id_empleado']) ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border-radius: 10px; background: #f59e0b; color: #ffffff; font-weight: 700; text-decoration: none;"
                    >
                        Generar contrato
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="<?= base_url('empleados/detalle?id=' . (int) $empleado['id_empleado']) ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border-radius: 10px; text-decoration: none;"
                    >
                        Omitir por ahora
                    </a>
                </div>
            </div>
        <?php endif; ?>

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
                    <span class="badge badge-activo estado-detalle-badge">Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo estado-detalle-badge">Inactivo</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <span>Nombre completo</span>
                <strong><?= e($nombreCompleto) ?></strong>
            </div>

            <div class="detail-item">
                <span>Sexo</span>
                <strong><?= e(formatoSexoEmpleado($empleado['sexo'])) ?></strong>
            </div>

            <div class="detail-item">
                <span>CURP</span>
                <strong><?= e($empleado['curp']) ?></strong>
            </div>

            <div class="detail-item">
                <span>RFC</span>
                <strong><?= e($empleado['rfc']) ?></strong>
            </div>

            <div class="detail-item">
                <span>NSS</span>
                <strong><?= e($empleado['nss']) ?></strong>
            </div>

            <div class="detail-item">
                <span>Número de preafiliación IMSS</span>
                <strong><?= mostrarDatoEmpleado($empleado['numero_preafiliacion_imss']) ?></strong>
            </div>

            <div class="detail-item">
                <span>Departamento</span>
                <strong><?= e($empleado['nombre_departamento']) ?></strong>
            </div>

            <div class="detail-item">
                <span>Puesto</span>
                <strong><?= e($empleado['nombre_puesto']) ?></strong>
            </div>

            <div class="detail-item">
                <span>Teléfono</span>
                <strong><?= mostrarDatoEmpleado($empleado['telefono']) ?></strong>
            </div>

            <div class="detail-item">
                <span>Correo</span>
                <strong><?= mostrarDatoEmpleado($empleado['correo']) ?></strong>
            </div>

            <div class="detail-item">
                <span>Fecha de ingreso</span>
                <strong><?= e($empleado['fecha_ingreso']) ?></strong>
            </div>
        </div>

        <div class="detail-section">
            <h2 class="detail-section-title">Detalle de movimiento</h2>

            <div class="detail-grid">
                <div class="detail-item">
                    <span>Fecha de registro en sistema</span>
                    <strong><?= e($empleado['fecha_registro']) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Fecha de baja/inactivación</span>
                    <strong><?= mostrarDatoEmpleado($empleado['fecha_baja']) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Estado laboral</span>
                    <strong><?= e(formatoEstadoLaboral($empleado['estado_laboral'])) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Última modificación</span>
                    <strong><?= mostrarDatoEmpleado($empleado['fecha_actualizacion']) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Última acción realizada</span>
                    <strong>
                        <?= $ultimoMovimiento ? e(formatoAccionEmpleado($ultimoMovimiento['accion'])) : 'No registrado' ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Usuario que realizó la última acción</span>
                    <strong>
                        <?= $ultimoMovimiento ? e($ultimoMovimiento['usuario_accion']) : 'No registrado' ?>
                    </strong>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h2 class="detail-section-title">Historial laboral</h2>

            <?php if (empty($historialLaboral)): ?>
                <div class="alert-error">
                    No hay historial laboral registrado para este empleado.
                </div>
            <?php else: ?>
                <div class="table-box">
                    <table class="data-table empleado-history-table">
                        <thead>
                            <tr>
                                <th>Fecha alta</th>
                                <th>Fecha baja</th>
                                <th>Tipo de alta</th>
                                <th>Estado</th>
                                <th>Motivo de baja</th>
                                <th>Usuario alta</th>
                                <th>Usuario baja</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($historialLaboral as $historial): ?>
                                <tr>
                                    <td><?= e($historial['fecha_alta']) ?></td>

                                    <td>
                                        <?= mostrarDatoEmpleado($historial['fecha_baja']) ?>
                                    </td>

                                    <td>
                                        <?= e(formatoTipoAltaEmpleado($historial['tipo_alta'])) ?>
                                    </td>

                                    <td>
                                        <?php if ($historial['estado_periodo'] === 'activo'): ?>
                                            <span class="badge badge-activo">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactivo">Cerrado</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="motivo-baja-cell">
                                        <?= mostrarDatoEmpleado($historial['motivo_baja']) ?>
                                    </td>

                                    <td>
                                        <?= mostrarDatoEmpleado($historial['usuario_alta']) ?>
                                    </td>

                                    <td>
                                        <?= mostrarDatoEmpleado($historial['usuario_baja']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="module-actions module-actions-footer detail-footer-actions">
            <a class="btn btn-secondary detail-footer-btn" href="<?= base_url('empleados') ?>">
                Volver al listado
            </a>

            <a class="btn btn-warning detail-footer-btn" href="<?= base_url('empleados/editar?id=' . (int) $empleado['id_empleado']) ?>">
                Editar empleado
            </a>

            <a class="btn btn-warning detail-footer-btn" href="<?= base_url('empleados/estado?id=' . (int) $empleado['id_empleado']) ?>">
                Cambiar estado
            </a>
        </div>

    <?php endif; ?>
    
</section>
<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>