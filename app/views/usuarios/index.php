<?php

$usuarios = $usuarios ?? [];
function rol_label(string $rol): string
{
    return match ($rol) {
        'administrador' => 'Administrador',
        'recursos_humanos' => 'Recursos Humanos',
        'psicologia' => 'Psicología',
        default => ucfirst(str_replace('_', ' ', $rol)),
    };
}

$rolesFiltro = [];

foreach ($usuarios as $usuarioItem) {
    if (!empty($usuarioItem['nombre_rol'])) {
        $rolesFiltro[] = rol_label($usuarioItem['nombre_rol']);
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
        modalEstadoUsuario: {
            abierto: false,
            id: '',
            nombre: '',
            estado: '',
            accion: ''
        },

        abrirModalEstadoUsuario(id, nombre, estado) {
            this.modalEstadoUsuario.id = id;
            this.modalEstadoUsuario.nombre = nombre;
            this.modalEstadoUsuario.estado = estado;
            this.modalEstadoUsuario.accion = estado === 'activo' ? 'inactivar' : 'activar';
            this.modalEstadoUsuario.abierto = true;
        },

        cerrarModalEstadoUsuario() {
            this.modalEstadoUsuario.abierto = false;
            this.modalEstadoUsuario.id = '';
            this.modalEstadoUsuario.nombre = '';
            this.modalEstadoUsuario.estado = '';
            this.modalEstadoUsuario.accion = '';
        },

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

    <?php if (($_GET['error'] ?? '') === 'status_password_required'): ?>
        <div class="alert-error">
            Debes ingresar tu contraseña para confirmar la inactivación del usuario.
        </div>
    <?php endif; ?>

    <?php if (($_GET['error'] ?? '') === 'status_wrong_password'): ?>
        <div class="alert-error">
            La contraseña ingresada no es correcta.
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

        <a href="<?= base_url('usuarios/create') ?>" class="btn btn-warning">
            Registrar usuario
        </a>
    </div>

    <?php if (!empty($usuarios)): ?>
        <p class="table-helper usuarios-results-count" x-show="hayFiltrosActivos()" x-cloak>
            Resultados encontrados: <strong x-text="totalCoincidencias"></strong>
        </p>
    <?php endif; ?>

    <div class="table-box">
        <table class="data-table usuarios-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody x-ref="usuariosBody">
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="7">No hay usuarios registrados.</td>
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
                            data-rol="<?= e(rol_label($usuarioItem['nombre_rol'])) ?>"
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
                                <?= e(rol_label($usuarioItem['nombre_rol'])) ?>
                            </td>

                            <td>
                                <?php if ($usuarioItem['estado'] === 'activo'): ?>
                                    <span class="badge badge-activo">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= e($usuarioItem['ultimo_acceso'] ?? 'Sin acceso') ?>
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

                                    <button
                                        class="btn-table btn-table-state"
                                        type="button"
                                        @click='abrirModalEstadoUsuario(
                                            <?= (int) $usuarioItem['id_usuario'] ?>,
                                            <?= json_encode($usuarioItem['nombre'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                                            <?= json_encode($usuarioItem['estado'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                                        )'
                                    >
                                        <?= $usuarioItem['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr x-show="totalCoincidencias === 0 && hayFiltrosActivos()" x-cloak>
                        <td colspan="7">
                            No se encontraron usuarios con ese criterio de búsqueda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="modal-overlay" x-show="modalEstadoUsuario.abierto" x-cloak>
        <div class="modal-card">
            <h2 x-text="modalEstadoUsuario.accion === 'inactivar' ? 'Inactivar usuario' : 'Activar usuario'"></h2>

            <template x-if="modalEstadoUsuario.accion === 'inactivar'">
                <div>
                    <p class="modal-text">
                        ¿Está seguro que quiere inactivar a este usuario?
                    </p>

                    <p class="modal-warning">
                        El usuario ya no tendrá acceso al sistema.
                    </p>
                </div>
            </template>

            <template x-if="modalEstadoUsuario.accion === 'activar'">
                <p class="modal-text">
                    ¿Está seguro que quiere activar nuevamente a este usuario?
                </p>
            </template>

            <p class="modal-user">
                Usuario: <strong x-text="modalEstadoUsuario.nombre"></strong>
            </p>

            <form method="POST" action="<?= base_url('usuarios/cambiar-estado') ?>">
                <?= csrf_field() ?>

                <input type="hidden" name="id_usuario" :value="modalEstadoUsuario.id">

                <div class="form-group" x-show="modalEstadoUsuario.accion === 'inactivar'">
                    <label for="contrasena_confirmacion">Confirma tu contraseña</label>
                    <input
                        type="password"
                        id="contrasena_confirmacion"
                        name="contrasena_confirmacion"
                        autocomplete="current-password"
                        :required="modalEstadoUsuario.accion === 'inactivar'"
                        placeholder="Ingresa tu contraseña para confirmar"
                    >
                </div>

                <div class="modal-actions">
                    <button class="btn btn-secondary" type="button" @click="cerrarModalEstadoUsuario()">
                        Cancelar
                    </button>

                    <button
                        class="btn"
                        :class="modalEstadoUsuario.accion === 'inactivar' ? 'btn-danger' : 'btn-warning'"
                        type="submit"
                        x-text="modalEstadoUsuario.accion === 'inactivar' ? 'Inactivar usuario' : 'Activar usuario'"
                    ></button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>
