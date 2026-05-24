<?php require_once __DIR__ . '/../layouts/private_header.php'; ?>

<h1 class="page-title">Gestión de usuarios</h1>

<section class="panel-card">
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

    <div class="module-actions">
        <input 
            class="search-input" 
            type="text" 
            placeholder="Buscar usuario..."
            x-data
            disabled
        >

        <a class="btn btn-primary" href="<?= base_url('usuarios/crear') ?>">
            Registrar usuario
        </a>
    </div>

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

            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="9">No hay usuarios registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuarioItem): ?>
                        <tr>
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
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>
