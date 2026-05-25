<?php

$usuario = $usuario ?? [];
$roles = $roles ?? [];
$errors = $errors ?? [];

require_once __DIR__ . '/../layouts/private_header.php';
?>

<h1 class="page-title">Editar usuario</h1>

<section class="panel-card">
    <div class="form-card">
        <h2>Modificar usuario</h2>
        <p>Actualiza los datos generales del usuario seleccionado.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form 
            method="POST" 
            action="<?= base_url('usuarios/actualizar') ?>"
            x-data="usuarioEditForm()"
        >
            <?= csrf_field() ?>

            <input
                type="hidden"
                name="id_usuario"
                value="<?= e((string) ($usuario['id_usuario'] ?? '')) ?>"
            >

            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?= e($usuario['nombre'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="nombre_usuario">Nombre de usuario</label>
                    <input
                        type="text"
                        id="nombre_usuario"
                        name="nombre_usuario"
                        value="<?= e($usuario['nombre_usuario'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Rol</label>

                    <input
                        type="hidden"
                        name="id_rol"
                        x-model="id_rol"
                    >

                    <div class="custom-select form-custom-select" @click.outside="rolDropdownAbierto = false">
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
                            <?php foreach ($roles as $rol): ?>
                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="seleccionarRol('<?= e((string) $rol['id_rol']) ?>')"
                                >
                                    <?= e($rol['nombre_rol']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group usuario-estado-field">
                    <label>Estado</label>
                    <input
                        type="hidden"
                        name="estado"
                        value="<?= e($old['estado'] ?? $usuario['estado'] ?? 'activo') ?>"
                    >

                    <input
                        type="text"
                        value="<?= (($old['estado'] ?? $usuario['estado'] ?? 'activo') === 'activo') ? 'Activo' : 'Inactivo' ?>"
                        readonly
                        class="readonly-input"
                    >

                    <small class="form-help">
                        El estado del usuario se modifica desde la tabla general.
                    </small>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-warning" type="submit">Guardar cambios</button>
                <a class="btn btn-secondary" href="<?= base_url('usuarios') ?>">Cancelar</a>
            </div>
        </form>
    </div>
</section>

<script>
    function usuarioEditForm() {
        return {
            roles: <?= json_encode($roles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,

            id_rol: <?= json_encode((string) ($old['id_rol'] ?? $usuario['id_rol'] ?? '')) ?>,

            rolDropdownAbierto: false,

            rolTexto() {
                const rol = this.roles.find((rol) => {
                    return String(rol.id_rol) === String(this.id_rol);
                });

                return rol ? rol.nombre_rol : 'Selecciona un rol';
            },

            seleccionarRol(idRol) {
                this.id_rol = String(idRol);
                this.rolDropdownAbierto = false;
            }
        };
    }
</script>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>
