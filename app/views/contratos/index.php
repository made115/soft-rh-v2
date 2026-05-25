<?php

$empleados = $empleados ?? [];
$departamentosFiltro = [];

foreach ($empleados as $empleadoItem) {
    if (!empty($empleadoItem['nombre_departamento'])) {
        $departamentosFiltro[] = $empleadoItem['nombre_departamento'];
    }
}

$departamentosFiltro = array_unique($departamentosFiltro);
sort($departamentosFiltro);

function formatoEstadoContratoIndex(?string $estado): string
{
    return match ($estado) {
        'vigente' => 'Vigente',
        'terminado' => 'Terminado',
        'cancelado' => 'Cancelado',
        'renovado' => 'Renovado',
        default => 'Sin contrato'
    };
}

function formatoPeriodicidadContratoIndex(?string $periodicidad): string
{
    return match ($periodicidad) {
        'diario' => 'Diario',
        'semanal' => 'Semanal',
        'quincenal' => 'Quincenal',
        'mensual' => 'Mensual',
        default => 'No registrada'
    };
}

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Gestión de contratos</h1>

<section
    class="panel-card"
    x-data="{
        busquedaInput: '',
        departamentoInput: '',
        estadoInput: '',
        departamentoDropdownAbierto: false,
        estadoDropdownAbierto: false,

        busqueda: '',
        filtroDepartamento: '',
        filtroEstado: '',
        totalCoincidencias: 0,

        estadoTexto() {
            if (this.estadoInput === 'vigente') {
                return 'Contrato vigente';
            }

            if (this.estadoInput === 'sin_contrato') {
                return 'Sin contrato';
            }

            if (this.estadoInput === 'inactivo') {
                return 'Empleado inactivo';
            }

            return 'Filtrar por estado';
        },

        departamentoTexto() {
            return this.departamentoInput === '' ? 'Filtrar por departamento' : this.departamentoInput;
        },

        normalizar(valor) {
            return String(valor ?? '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        },

        buscar() {
            this.busqueda = this.busquedaInput;
            this.filtroDepartamento = this.departamentoInput;
            this.filtroEstado = this.estadoInput;
            this.actualizarConteo();
        },

        limpiar() {
            this.busquedaInput = '';
            this.departamentoInput = '';
            this.estadoInput = '';
            this.busqueda = '';
            this.filtroDepartamento = '';
            this.filtroEstado = '';
            this.actualizarConteo();
        },

        coincide(fila) {
            const termino = this.normalizar(this.busqueda);
            const departamento = this.normalizar(this.filtroDepartamento);
            const estado = this.normalizar(this.filtroEstado);

            const textoFila = this.normalizar(fila.dataset.search);
            const departamentoFila = this.normalizar(fila.dataset.departamento);
            const estadoFila = this.normalizar(fila.dataset.estado);

            const coincideTexto = termino === '' || textoFila.includes(termino);
            const coincideDepartamento = departamento === '' || departamentoFila === departamento;
            const coincideEstado = estado === '' || estadoFila === estado;

            return coincideTexto && coincideDepartamento && coincideEstado;
        },

        hayFiltrosActivos() {
            return this.busqueda !== '' || this.filtroDepartamento !== '' || this.filtroEstado !== '';
        },

        actualizarConteo() {
            this.$nextTick(() => {
                const filas = this.$refs.contratosBody
                    ? Array.from(this.$refs.contratosBody.querySelectorAll('tr[data-search]'))
                    : [];

                this.totalCoincidencias = filas.filter(fila => this.coincide(fila)).length;
            });
        }
    }"
    x-init="actualizarConteo()"
>
    <?php if (isset($_GET['guardado'])): ?>
        <div class="alert-success">
            Contrato registrado correctamente.
        </div>
    <?php endif; ?>

    <div class="module-actions empleados-search-bar">
        <input
            class="search-input"
            type="text"
            placeholder="Buscar por ID o nombre"
            x-model="busquedaInput"
            @keydown.enter="buscar()"
        >

        <div class="custom-select" @click.outside="departamentoDropdownAbierto = false">
            <button
                type="button"
                class="custom-select-button"
                @click="departamentoDropdownAbierto = !departamentoDropdownAbierto"
            >
                <span x-text="departamentoTexto()"></span>
                <span class="custom-select-arrow">▾</span>
            </button>

            <div
                class="custom-select-menu"
                x-show="departamentoDropdownAbierto"
                x-cloak
            >
                <button
                    type="button"
                    class="custom-select-option"
                    @click="departamentoInput = ''; departamentoDropdownAbierto = false"
                >
                    Filtrar por departamento
                </button>

                <?php foreach ($departamentosFiltro as $departamentoFiltro): ?>
                    <button
                        type="button"
                        class="custom-select-option"
                        @click="departamentoInput = '<?= e($departamentoFiltro) ?>'; departamentoDropdownAbierto = false"
                    >
                        <?= e($departamentoFiltro) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

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
                    Contrato vigente
                </button>

                <button
                    type="button"
                    class="custom-select-option"
                    @click="estadoInput = 'sin_contrato'; estadoDropdownAbierto = false"
                >
                    Sin contrato
                </button>

                <button
                    type="button"
                    class="custom-select-option"
                    @click="estadoInput = 'inactivo'; estadoDropdownAbierto = false"
                >
                    Empleado inactivo
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

    <?php if (!empty($empleados)): ?>
        <p class="table-helper" x-show="hayFiltrosActivos()" x-cloak>
            Resultados encontrados: <strong x-text="totalCoincidencias"></strong>
        </p>
    <?php endif; ?>

    <div class="table-box">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID empleado</th>
                    <th>Empleado</th>
                    <th>Estado laboral</th>
                    <th>Contrato actual</th>
                    <th>Fecha fin</th>
                    <th>Sueldo diario</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody x-ref="contratosBody">
                <?php if (empty($empleados)): ?>
                    <tr>
                        <td colspan="7">
                            No hay empleados registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($empleados as $empleadoItem): ?>
                        <?php
                            $nombreCompleto = trim(
                                $empleadoItem['nombre_empleado'] . ' ' .
                                $empleadoItem['apellido_pat_empleado'] . ' ' .
                                $empleadoItem['apellido_mat_empleado']
                            );

                            $tieneContratoVigente = !empty($empleadoItem['id_contrato'])
                                && ($empleadoItem['estado_contrato'] ?? '') === 'vigente';

                            if ($empleadoItem['estado_laboral'] === 'inactivo') {
                                $estadoFiltro = 'inactivo';
                            } elseif ($tieneContratoVigente) {
                                $estadoFiltro = 'vigente';
                            } else {
                                $estadoFiltro = 'sin_contrato';
                            }

                            $textoBusqueda = trim(
                                $empleadoItem['id_empleado'] . ' ' .
                                $nombreCompleto . ' ' .
                                $empleadoItem['nombre_departamento'] . ' ' .
                                $empleadoItem['nombre_puesto'] . ' ' .
                                formatoEstadoContratoIndex($empleadoItem['estado_contrato'] ?? null)
                            );
                        ?>

                        <tr
                            data-search="<?= e($textoBusqueda) ?>"
                            data-departamento="<?= e($empleadoItem['nombre_departamento']) ?>"
                            data-estado="<?= e($estadoFiltro) ?>"
                            x-show="coincide($el)"
                            x-cloak
                        >
                            <td><?= e((string) $empleadoItem['id_empleado']) ?></td>

                            <td class="text-left">
                                <?= e($nombreCompleto) ?>
                            </td>

                            <td>
                                <?php if ($empleadoItem['estado_laboral'] === 'activo'): ?>
                                    <span class="badge badge-activo">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($tieneContratoVigente): ?>
                                    <span class="badge badge-activo">Vigente</span>
                                    <br>
                                    <small>
                                        <?= e((string) $empleadoItem['duracion_meses']) ?> meses ·
                                        <?= e(formatoPeriodicidadContratoIndex($empleadoItem['periodicidad_pago'] ?? null)) ?>
                                    </small>
                                <?php else: ?>
                                    <span class="badge badge-inactivo">Sin contrato</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= !empty($empleadoItem['fecha_fin']) ? e($empleadoItem['fecha_fin']) : 'No registrada' ?>

                                <?php if ($tieneContratoVigente && isset($empleadoItem['dias_restantes'])): ?>
                                    <br>
                                    <small>
                                        <?php if ((int) $empleadoItem['dias_restantes'] < 0): ?>
                                            Vencido
                                        <?php else: ?>
                                            <?= e((string) $empleadoItem['dias_restantes']) ?> días restantes
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($tieneContratoVigente): ?>
                                    $<?= e(number_format((float) $empleadoItem['sueldo_diario'], 2)) ?>
                                <?php else: ?>
                                    No registrado
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <?php if ($empleadoItem['estado_laboral'] === 'activo'): ?>
                                        <a
                                            class="btn-table btn-table-edit"
                                            href="<?= base_url('contratos/crear?id_empleado=' . (int) $empleadoItem['id_empleado']) ?>"
                                        >
                                            <?= $tieneContratoVigente ? 'Renovar' : 'Nuevo' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-table btn-table-disabled">
                                            Nuevo
                                        </span>
                                    <?php endif; ?>

                                    <a
                                        class="btn-table"
                                        href="<?= base_url('contratos/historial?id_empleado=' . (int) $empleadoItem['id_empleado']) ?>"
                                    >
                                        Historial
                                    </a>

                                    <?php if ($tieneContratoVigente): ?>
                                        <a
                                            class="btn-table btn-table-state"
                                            href="<?= base_url('contratos/editar?id=' . (int) $empleadoItem['id_contrato']) ?>"
                                        >
                                            Editar
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-table btn-table-disabled">
                                            Editar
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($tieneContratoVigente): ?>
                                        <a
                                            class="btn-table btn-table-state"
                                            href="<?= base_url('contratos/pdf?id=' . (int) $empleadoItem['id_contrato']) ?>"
                                            title="Generar o descargar PDF del contrato"
                                        >
                                            PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-table btn-table-disabled">
                                            PDF
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr x-show="totalCoincidencias === 0 && hayFiltrosActivos()" x-cloak>
                        <td colspan="7">
                            No se encontraron empleados con ese criterio de búsqueda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>
