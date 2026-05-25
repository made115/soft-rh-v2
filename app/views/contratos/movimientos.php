<?php

$contrato = $contrato ?? null;
$bitacora = $bitacora ?? [];

$nombreCompleto = '';

if ($contrato) {
    $nombreCompleto = trim(
        $contrato['nombre_empleado'] . ' ' .
        $contrato['apellido_pat_empleado'] . ' ' .
        $contrato['apellido_mat_empleado']
    );
}

function formatoAccionContratoMovimientos(string $accion): string
{
    return match ($accion) {
        'registro' => 'Registro de contrato',
        'registro_renovacion' => 'Registro por renovación',
        'renovacion' => 'Contrato renovado',
        'edicion' => 'Edición de contrato',
        'pdf_generado' => 'PDF generado',
        'cancelacion' => 'Cancelación de contrato',
        'terminacion_por_baja' => 'Terminación por baja laboral',
        default => ucfirst(str_replace('_', ' ', $accion))
    };
}

function formatoEstadoContratoMovimientos(string $estado): string
{
    return match ($estado) {
        'vigente' => 'Vigente',
        'terminado' => 'Terminado',
        'cancelado' => 'Cancelado',
        'renovado' => 'Renovado',
        default => ucfirst($estado)
    };
}

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Movimientos del contrato</h1>

<section class="panel-card">
    <?php if (!$contrato): ?>
        <div class="alert-error">
            No se encontró información del contrato.
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= base_url('contratos') ?>">
                Volver a contratos
            </a>
        </div>
    <?php else: ?>
        <div class="form-card form-card-wide">

            <?php if (isset($_GET['pdf_bloqueado'])): ?>
                <div class="alert-error">
                    No se puede generar PDF para un contrato cancelado.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['correccion_bloqueada'])): ?>
                <div class="alert-error">
                    Este contrato ya tiene PDF generado, por lo que no puede corregirse directamente. Si se requiere un cambio, deberá generarse una nueva versión o realizar una renovación según corresponda.
                </div>
            <?php endif; ?>
            <div class="detail-header">
                <div>
                    <h2 class="detail-title">
                        Contrato #<?= e((string) $contrato['id_contrato']) ?>
                    </h2>

                    <p class="detail-subtitle">
                        <?= e($nombreCompleto) ?>
                    </p>
                </div>

                <div>
                    <?php if ($contrato['estado_contrato'] === 'vigente'): ?>
                        <span class="badge badge-activo">Vigente</span>
                    <?php elseif ($contrato['estado_contrato'] === 'cancelado'): ?>
                        <span class="badge badge-inactivo">Cancelado</span>
                    <?php else: ?>
                        <span class="badge btn-table-disabled">
                            <?= e(formatoEstadoContratoMovimientos($contrato['estado_contrato'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <span>Empleado</span>
                    <strong><?= e($nombreCompleto) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Departamento y puesto</span>
                    <strong>
                        <?= e($contrato['nombre_departamento']) ?> · <?= e($contrato['nombre_puesto']) ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Vigencia</span>
                    <strong>
                        <?= e($contrato['fecha_inicio']) ?> al <?= e($contrato['fecha_fin']) ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Sueldo diario</span>
                    <strong>
                        $<?= e(number_format((float) $contrato['sueldo_diario'], 2)) ?> MXN
                    </strong>
                </div>
            </div>

            <div class="detail-section">
                <h2 class="detail-section-title">Bitácora de movimientos del contrato</h2>

                <?php if (empty($bitacora)): ?>
                    <div class="alert-error">
                        No hay movimientos registrados para este contrato.
                    </div>
                <?php else: ?>
                    <div class="table-box" style="overflow-x: visible;">
                        <table class="data-table" style="font-size: 0.9rem; width: 100%; min-width: 0; table-layout: fixed;">
                            <colgroup>
                                <col style="width: 18%;">
                                <col style="width: 34%;">
                                <col style="width: 28%;">
                                <col style="width: 20%;">
                            </colgroup>

                            <thead>
                                <tr>
                                    <th>ID contrato</th>
                                    <th>Acción realizada</th>
                                    <th>Usuario responsable</th>
                                    <th>Fecha de acción</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($bitacora as $movimiento): ?>
                                    <tr>
                                        <td>
                                            #<?= e((string) $movimiento['id_contrato']) ?>
                                        </td>

                                        <td>
                                            <?= e(formatoAccionContratoMovimientos($movimiento['accion'])) ?>
                                        </td>

                                        <td>
                                            <?= e($movimiento['usuario_accion']) ?>
                                        </td>

                                        <td>
                                            <?= e($movimiento['fecha_accion']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a
                    class="btn btn-secondary"
                    href="<?= base_url('contratos/historial?id_empleado=' . (int) $contrato['id_empleado']) ?>"
                >
                    Volver al historial
                </a>

                <?php if ($contrato['estado_contrato'] === 'vigente'): ?>
                    <a
                        class="btn btn-primary"
                        href="<?= base_url('contratos/editar?id=' . (int) $contrato['id_contrato']) ?>"
                    >
                        Editar contrato
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>