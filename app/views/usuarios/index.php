<?php

$usuarios = $usuarios ?? [];

$rolesFiltro = [];

foreach ($usuarios as $usuarioItem) {
    if (!empty($usuarioItem['nombre_rol'])) {
        $rolesFiltro[] = $usuarioItem['nombre_rol'];
    }
}

$rolesFiltro = array_unique($rolesFiltro);
sort($rolesFiltro);

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Gestión de usuarios</h1>

<section 
    class="panel-card"
    x-data="{
        busquedaInput: '',
        rolInput: '',
        estadoInput: '',

        busqueda: '',
        filtroRol: '',
        filtroEstado: '',

        rolDropdownAbierto: false,
        estadoDropdownAbierto: false,

        totalCoincidencias: 0,

        normalizar(valor) {
            return String(valor ?? '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        },

        rolTexto() {
            return this.rolInput === '' ? 'Filtrar por rol' : this.rolInput;
        },

        estadoTexto() {
            if (this.estadoInput === 'activo') {
                return 'Activo';
            }

            if (this.estadoInput === 'inactivo') {
                return 'Inactivo';
            }

            return 'Filtrar por estado';
        },

        buscar() {
            this.busqueda = this.busquedaInput;
            this.filtroRol = this.rolInput;
            this.filtroEstado = this.estadoInput;
            this.actualizarConteo();
        },

        limpiar() {
            this.busquedaInput = '';
            this.rolInput = '';
            this.estadoInput = '';

            this.busqueda = '';
            this.filtroRol = '';
            this.filtroEstado = '';

            this.actualizarConteo();
        },

        coincide(fila) {
            const termino = this.normalizar(this.busqueda);
            const rol = this.normalizar(this.filtroRol);
            const estado = this.normalizar(this.filtroEstado);

            const textoFila = this.normalizar(fila.dataset.search);
            const rolFila = this.normalizar(fila.dataset.rol);
            const estadoFila = this.normalizar(fila.dataset.estado);

            const coincideTexto = termino === '' || textoFila.includes(termino);
            const coincideRol = rol === '' || rolFila === rol;
            const coincideEstado = estado === '' || estadoFila === estado;

            return coincideTexto && coincideRol && coincideEstado;
        },

        hayFiltrosActivos() {
            return this.busqueda !== '' || this.filtroRol !== '' || this.filtroEstado !== '';
        },

        actualizarConteo() {
            this.$nextTick(() => {
                const filas = this.$refs.usuariosBody
                    ? Array.from(this.$refs.usuariosBody.querySelectorAll('tr[data-search]'))
                    : [];

                this.totalCoincidencias = filas.filter(fila => this.coincide(fila)).length;
            });
        }
    }"
    x-init="actualizarConteo()"
>
    <?php if (isset($_GET['creado'])): ?>
        <div class="alert-success">
            Usuario registrado correctamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['actualizado'])): ?>
        <div class="alert-success">
            Usuario actualizado correctamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['estado'])): ?>
        <div class="alert-success">
            Estado del usuario actualizado correctamente.
        </div>
    <?php endif; ?>

    <?php if (($_GET['error'] ?? '') === 'self_status'): ?>
        <div class="alert-error">
            No puedes cambiar el estado de tu propio usuario mientras tienes la sesión iniciada.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['password'])): ?>
        <div class="alert-success">
            Contraseña restablecida correctamente.
        </div>
    <?php endif; ?>

    <div class="module-actions usuarios-search-bar">
        <input 
            class="search-input" 
            type="text" 
            placeholder="Buscar por ID, nombre o usuario"
            x-model="busquedaInput"
            @keydown.enter="buscar()"
        >

        <div class="custom-select" @click.outside="rolDropdownAbierto = false">
            <button
                type="button"
                class="custom-select-button"
                @click="rolDropdownAbierto = !rolDropdownAbierto"
            >
                <span x-text="rolTexto()"></span>
                <span class="custom-select-arrow">▾</span>
            </button>

            <div
                class="custom-select-menu"
                x-show="rolDropdownAbierto"
                x-cloak
            >
                <button
                    type="button"
                    class="custom-select-option"
                    @click="rolInput = ''; rolDropdownAbierto = false"
                >
                    Filtrar por rol
                </button>

                <?php foreach ($rolesFiltro as $rolFiltro): ?>
                    <button
                        type="button"
                        class="custom-select-option"
                        @click="rolInput = '<?= e($rolFiltro) ?>'; rolDropdownAbierto = false"
                    >
                        <?= e($rolFiltro) ?>
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

        <a class="btn btn-primary" href="<?= base_url('usuarios/crear') ?>">
            Registrar usuario
        </a>
    </div>

    <?php if (!empty($usuarios)): ?>
        <p class="table-helper" x-show="hayFiltrosActivos()" x-cloak>
            Resultados encontrados: <strong x-text="totalCoincidencias"></strong>
        </p>
    <?php endif; ?>

    <div class="table-box">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Cambio contraseña</th>
                    <th>Último acceso</th>
                    <th>Fecha registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody x-ref="usuariosBody">
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="9">No hay usuarios registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuarioItem): ?>
                        <?php
                            $textoBusqueda = trim(
                                $usuarioItem['id_usuario'] . ' ' .
                                $usuarioItem['nombre'] . ' ' .
                                $usuarioItem['nombre_usuario']
                            );
                        ?>

                        <tr
                            data-search="<?= e($textoBusqueda) ?>"
                            data-rol="<?= e($usuarioItem['nombre_rol']) ?>"
                            data-estado="<?= e($usuarioItem['estado']) ?>"
                            x-show="coincide($el)"
                            x-cloak
                        >
                            <td><?= e((string) $usuarioItem['id_usuario']) ?></td>

                            <td class="text-left">
                                <?= e($usuarioItem['nombre']) ?>
                            </td>

                            <td>
                                <?= e($usuarioItem['nombre_usuario']) ?>
                            </td>

                            <td>
                                <?= e($usuarioItem['nombre_rol']) ?>
                            </td>

                            <td>
                                <?php if ($usuarioItem['estado'] === 'activo'): ?>
                                    <span class="badge badge-activo">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= ((int) $usuarioItem['requiere_cambio_contrasena'] === 1) ? 'Sí' : 'No' ?>
                            </td>

                            <td>
                                <?= e($usuarioItem['ultimo_acceso'] ?? 'Sin acceso') ?>
                            </td>

                            <td>
                                <?= e($usuarioItem['fecha_registro']) ?>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        class="btn-table btn-table-edit"
                                        href="<?= base_url('usuarios/editar?id=' . $usuarioItem['id_usuario']) ?>"
                                    >
                                        Editar
                                    </a>

                                    <a
                                        class="btn-table btn-table-edit"
                                        href="<?= base_url('usuarios/contrasena?id=' . $usuarioItem['id_usuario']) ?>"
                                    >
                                        Contraseña
                                    </a>

                                    <form 
                                        method="POST" 
                                        action="<?= base_url('usuarios/cambiar-estado') ?>"
                                        onsubmit="return confirm('¿Seguro que deseas cambiar el estado de este usuario?');"
                                    >
                                        <?= csrf_field() ?>

                                        <input 
                                            type="hidden" 
                                            name="id_usuario" 
                                            value="<?= e((string) $usuarioItem['id_usuario']) ?>"
                                        >

                                        <button class="btn-table btn-table-state" type="submit">
                                            <?= $usuarioItem['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr x-show="totalCoincidencias === 0 && hayFiltrosActivos()" x-cloak>
                        <td colspan="9">
                            No se encontraron usuarios con ese criterio de búsqueda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>
