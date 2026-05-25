<?php

$empleado = $empleado ?? null;
$contratos = $contratos ?? [];

$nombreCompleto = '';

if ($empleado) {
    $nombreCompleto = trim(
        $empleado['nombre_empleado'] . ' ' .
        $empleado['apellido_pat_empleado'] . ' ' .
        $empleado['apellido_mat_empleado']
    );
}

function formatoEstadoContratoHistorial(string $estado): string
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

<h1 class="page-title">Historial de contratos</h1>

<section class="panel-card">
    <?php if (!$empleado): ?>
        <div class="alert-error">
            No se encontró información del empleado.
        </div>

        <div class="module-actions">
            <a class="btn btn-secondary" href="<?= base_url('contratos') ?>">
                Volver al listado
            </a>
        </div>
    <?php else: ?>
        <?php if (isset($_GET['actualizado'])): ?>
            <div class="alert-success">
                Contrato actualizado correctamente.
            </div>
        <?php endif; ?>

        <div class="form-card form-card-wide">
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

            <div class="detail-grid">
                <div class="detail-item">
                    <span>ID empleado</span>
                    <strong><?= e((string) $empleado['id_empleado']) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Fecha de ingreso laboral</span>
                    <strong><?= e($empleado['fecha_ingreso']) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Departamento</span>
                    <strong><?= e($empleado['nombre_departamento']) ?></strong>
                </div>

                <div class="detail-item">
                    <span>Puesto</span>
                    <strong><?= e($empleado['nombre_puesto']) ?></strong>
                </div>
            </div>

            <div
                class="detail-section"
                x-data="{
                    idInput: '',
                    estadoInput: '',
                    idFiltro: '',
                    estadoFiltro: '',
                    estadoDropdownAbierto: false,
                    totalCoincidencias: 0,

                    estadoTexto() {
                        if (this.estadoInput === 'vigente') {
                            return 'Vigente';
                        }

                        if (this.estadoInput === 'renovado') {
                            return 'Renovado';
                        }

                        if (this.estadoInput === 'terminado') {
                            return 'Terminado';
                        }

                        if (this.estadoInput === 'cancelado') {
                            return 'Cancelado';
                        }

                        return 'Filtrar por estado';
                    },

                    normalizar(valor) {
                        return String(valor ?? '')
                            .toLowerCase()
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .trim();
                    },

                    buscar() {
                        this.idFiltro = this.idInput;
                        this.estadoFiltro = this.estadoInput;
                        this.actualizarConteo();
                    },

                    limpiar() {
                        this.idInput = '';
                        this.estadoInput = '';
                        this.idFiltro = '';
                        this.estadoFiltro = '';
                        this.actualizarConteo();
                    },

                    hayFiltrosActivos() {
                        return this.idFiltro !== '' || this.estadoFiltro !== '';
                    },

                    coincide(fila) {
                        const idBuscado = this.normalizar(this.idFiltro).replace('#', '');
                        const estadoBuscado = this.normalizar(this.estadoFiltro);

                        const idContrato = this.normalizar(fila.dataset.idContrato);
                        const estadoContrato = this.normalizar(fila.dataset.estadoContrato);

                        const coincideId = idBuscado === '' || idContrato.includes(idBuscado);
                        const coincideEstado = estadoBuscado === '' || estadoContrato === estadoBuscado;

                        return coincideId && coincideEstado;
                    },

                    actualizarConteo() {
                        this.$nextTick(() => {
                            const filas = this.$refs.contratosBody
                                ? Array.from(this.$refs.contratosBody.querySelectorAll('tr[data-id-contrato]'))
                                : [];

                            this.totalCoincidencias = filas.filter(fila => this.coincide(fila)).length;
                        });
                    }
                }"
                x-init="actualizarConteo()"
            >
                <h2 class="detail-section-title">Contratos registrados</h2>

                <?php if (empty($contratos)): ?>
                    <div class="alert-error">
                        Este empleado todavía no tiene contratos registrados.
                    </div>
                <?php else: ?>
                    <div class="module-actions empleados-search-bar" style="margin-bottom: 18px;">
                        <input
                            class="search-input"
                            type="text"
                            placeholder="Buscar por ID contrato"
                            x-model="idInput"
                            @keydown.enter="buscar()"
                        >

                        <div class="custom-select" @click.outside="estadoDropdownAbierto = false">
                            <button
                                type="button"
                                class="custom-select-button"
                                @click="estadoDropdownAbierto = !estadoDropdownAbierto"
                            >
                                <span x-text="estadoTexto()"></span>
                                <span class="custom-select-arrow">▾</span>
                            </button>

                            <div
                                class="custom-select-menu"
                                x-show="estadoDropdownAbierto"
                                x-cloak
                            >
                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="estadoInput = ''; estadoDropdownAbierto = false"
                                >
                                    Filtrar por estado
                                </button>

                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="estadoInput = 'vigente'; estadoDropdownAbierto = false"
                                >
                                    Vigente
                                </button>

                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="estadoInput = 'renovado'; estadoDropdownAbierto = false"
                                >
                                    Renovado
                                </button>

                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="estadoInput = 'terminado'; estadoDropdownAbierto = false"
                                >
                                    Terminado
                                </button>

                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="estadoInput = 'cancelado'; estadoDropdownAbierto = false"
                                >
                                    Cancelado
                                </button>
                            </div>
                        </div>

                        <button
                            class="btn btn-secondary"
                            type="button"
                            @click="buscar()"
                        >
                            Buscar
                        </button>

                        <button
                            class="btn btn-secondary"
                            type="button"
                            @click="limpiar()"
                        >
                            Limpiar
                        </button>
                    </div>

                    <p class="table-helper" x-show="hayFiltrosActivos()" x-cloak>
                        Resultados encontrados: <strong x-text="totalCoincidencias"></strong>
                    </p>

                    <div class="table-box" style="overflow-x: visible;">
                        <table class="data-table" style="font-size: 0.9rem; width: 100%; min-width: 0; table-layout: fixed;">
                            <colgroup>
                                <col style="width: 8%;">
                                <col style="width: 22%;">
                                <col style="width: 13%;">
                                <col style="width: 12%;">
                                <col style="width: 45%;">
                            </colgroup>

                            <thead>
                                <tr>
                                    <th>ID contrato</th>
                                    <th>Vigencia</th>
                                    <th>Sueldo diario</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <tbody x-ref="contratosBody">
                                <?php foreach ($contratos as $contrato): ?>
                                    <tr
                                        data-id-contrato="<?= e((string) $contrato['id_contrato']) ?>"
                                        data-estado-contrato="<?= e($contrato['estado_contrato']) ?>"
                                        x-show="coincide($el)"
                                        x-cloak
                                    >
                                        <td>
                                            #<?= e((string) $contrato['id_contrato']) ?>
                                        </td>

                                        <td style="white-space: nowrap;">
                                            <?= e($contrato['fecha_inicio']) ?> al <?= e($contrato['fecha_fin']) ?>
                                        </td>

                                        <td>
                                            $<?= e(number_format((float) $contrato['sueldo_diario'], 2)) ?>
                                        </td>

                                        <td>
                                            <?php if ($contrato['estado_contrato'] === 'vigente'): ?>
                                                <span class="badge badge-activo">Vigente</span>
                                            <?php elseif ($contrato['estado_contrato'] === 'cancelado'): ?>
                                                <span class="badge badge-inactivo">Cancelado</span>
                                            <?php else: ?>
                                                <span class="badge btn-table-disabled">
                                                    <?= e(formatoEstadoContratoHistorial($contrato['estado_contrato'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: nowrap;">
                                                <?php if ($contrato['estado_contrato'] === 'vigente'): ?>
                                                    <a
                                                        class="btn-table btn-table-edit"
                                                        href="<?= base_url('contratos/editar?id=' . (int) $contrato['id_contrato']) ?>"
                                                        style="min-width: 90px; padding: 10px 14px;"
                                                    >
                                                        Editar
                                                    </a>
                                                <?php else: ?>
                                                    <span
                                                        class="btn-table btn-table-disabled"
                                                        style="min-width: 90px; padding: 10px 14px;"
                                                    >
                                                        Editar
                                                    </span>
                                                <?php endif; ?>

                                                <span
                                                    class="btn-table btn-table-disabled"
                                                    style="min-width: 80px; padding: 10px 14px;"
                                                    title="Generar PDF"
                                                >
                                                    PDF
                                                </span>

                                                <a
                                                    class="btn-table btn-table-state"
                                                    href="<?= base_url('contratos/movimientos?id=' . (int) $contrato['id_contrato']) ?>"
                                                    style="min-width: 130px; padding: 10px 14px;"
                                                    title="Ver movimientos del contrato"
                                                >
                                                    Movimientos
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php if (!empty($contrato['observaciones'])): ?>
                                        <tr
                                            data-id-contrato="<?= e((string) $contrato['id_contrato']) ?>"
                                            data-estado-contrato="<?= e($contrato['estado_contrato']) ?>"
                                            x-show="coincide($el.previousElementSibling)"
                                            x-cloak
                                        >
                                            <td colspan="5" class="text-left">
                                                <strong>Observaciones:</strong>
                                                <?= e($contrato['observaciones']) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <tr x-show="totalCoincidencias === 0 && hayFiltrosActivos()" x-cloak>
                                    <td colspan="5">
                                        No se encontraron contratos con ese criterio de búsqueda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="<?= base_url('contratos') ?>">
                    Volver a contratos
                </a>

                <?php if ($empleado['estado_laboral'] === 'activo'): ?>
                    <a
                        class="btn btn-primary"
                        href="<?= base_url('contratos/crear?id_empleado=' . (int) $empleado['id_empleado']) ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 24px; border-radius: 10px; background: #f59e0b; color: #ffffff; font-weight: 700; text-decoration: none;"
                    >
                        Nuevo / renovar contrato
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>