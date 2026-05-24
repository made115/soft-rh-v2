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

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Gestión de empleados</h1>

<section 
    class="panel-card"
    x-data="{
        busquedaInput: '',
        departamentoInput: '',
        estadoInput: '',
        estadoDropdownAbierto: false,
        departamentoDropdownAbierto: false,

        busqueda: '',
        filtroDepartamento: '',
        filtroEstado: '',
        totalCoincidencias: 0,

        estadoTexto() {
            if (this.estadoInput === 'activo') {
                return 'Activo';
            }

            if (this.estadoInput === 'inactivo') {
                return 'Inactivo';
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
                const filas = this.$refs.empleadosBody
                    ? Array.from(this.$refs.empleadosBody.querySelectorAll('tr[data-search]'))
                    : [];

                this.totalCoincidencias = filas.filter(fila => this.coincide(fila)).length;
            });
        }
    }"
    x-init="actualizarConteo()"
>
    <?php if (isset($_GET['creado'])): ?>
        <div class="alert-success">
            Empleado registrado correctamente.
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
                    @click="estadoInput = 'activo'; estadoDropdownAbierto = false"
                >
                    Activo
                </button>

                <button
                    type="button"
                    class="custom-select-option"
                    @click="estadoInput = 'inactivo'; estadoDropdownAbierto = false"
                >
                    Inactivo
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

        <a class="btn btn-primary" href="<?= base_url('empleados/crear') ?>">
            Nuevo empleado
        </a>
    </div>

    <?php if (!empty($empleados)): ?>
        <p class="table-helper" x-show="hayFiltrosActivos()" x-cloak>
            Resultados encontrados: <strong x-text="totalCoincidencias"></strong>
        </p>
    <?php endif; ?>

    <div class="table-box">
        <table class="data-table empleados-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody x-ref="empleadosBody">
                <?php if (empty($empleados)): ?>
                    <tr>
                        <td colspan="6">
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

                            $textoBusqueda = trim(
                                $empleadoItem['id_empleado'] . ' ' .
                                $nombreCompleto . ' ' .
                                $empleadoItem['nombre_departamento'] . ' ' .
                                $empleadoItem['nombre_puesto'] . ' ' .
                                $empleadoItem['estado_laboral']
                            );
                        ?>

                        <tr
                            data-search="<?= e($textoBusqueda) ?>"
                            data-departamento="<?= e($empleadoItem['nombre_departamento']) ?>"
                            data-estado="<?= e($empleadoItem['estado_laboral']) ?>"
                            x-show="coincide($el)"
                            x-cloak
                        >
                            <td><?= e((string) $empleadoItem['id_empleado']) ?></td>

                            <td class="text-left">
                                <?= e($nombreCompleto) ?>
                            </td>

                            <td><?= e($empleadoItem['nombre_departamento']) ?></td>

                            <td><?= e($empleadoItem['nombre_puesto']) ?></td>

                            <td>
                                <?php if ($empleadoItem['estado_laboral'] === 'activo'): ?>
                                    <span class="badge badge-activo">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a 
                                        class="btn-table" 
                                        href="<?= base_url('empleados/detalle?id=' . (int) $empleadoItem['id_empleado']) ?>"
                                    >
                                        Detalle
                                    </a>

                                    <a 
                                        class="btn-table" 
                                        href="<?= base_url('empleados/editar?id=' . (int) $empleadoItem['id_empleado']) ?>"
                                    >
                                        Editar
                                    </a>

                                    <a 
                                        class="btn-table" 
                                        href="<?= base_url('empleados/estado?id=' . (int) $empleadoItem['id_empleado']) ?>"
                                    >
                                        Estado
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr x-show="totalCoincidencias === 0 && hayFiltrosActivos()" x-cloak>
                        <td colspan="6">
                            No se encontraron empleados con ese criterio de búsqueda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>