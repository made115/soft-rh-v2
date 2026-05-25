<?php

$departamentos = $departamentos ?? [];
$puestos = $puestos ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

require_once __DIR__ . '/../layouts/private_header.php';
?>

<script>
    function empleadoForm() {
        return {
            departamentos: <?= json_encode($departamentos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            puestos: <?= json_encode($puestos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,

            id_departamento: <?= json_encode((string) ($old['id_departamento'] ?? '')) ?>,
            id_puesto: <?= json_encode((string) ($old['id_puesto'] ?? '')) ?>,

            departamentoDropdownAbierto: false,
            puestoDropdownAbierto: false,
            sexo: <?= json_encode((string) ($old['sexo'] ?? '')) ?>,
            sexoDropdownAbierto: false,

            sexoTexto() {
                if (this.sexo === 'masculino') {
                    return 'Masculino';
                }

                if (this.sexo === 'femenino') {
                    return 'Femenino';
                }

                if (this.sexo === 'no_especificado') {
                    return 'No especificado';
                }

                return 'Selecciona una opción';
            },

            seleccionarSexo(valor) {
                this.sexo = valor;
                this.sexoDropdownAbierto = false;
            },

            get puestosFiltrados() {
                if (this.id_departamento === '') {
                    return [];
                }

                return this.puestos.filter((puesto) => {
                    return String(puesto.id_departamento) === String(this.id_departamento);
                });
            },

            departamentoTexto() {
                const departamento = this.departamentos.find((dep) => {
                    return String(dep.id_departamento) === String(this.id_departamento);
                });

                return departamento ? departamento.nombre_departamento : 'Selecciona un departamento';
            },

            puestoTexto() {
                const puesto = this.puestos.find((puesto) => {
                    return String(puesto.id_puesto) === String(this.id_puesto);
                });

                return puesto ? puesto.nombre_puesto : 'Selecciona un puesto';
            },

            seleccionarDepartamento(idDepartamento) {
                this.id_departamento = String(idDepartamento);
                this.id_puesto = '';
                this.departamentoDropdownAbierto = false;
            },

            seleccionarPuesto(idPuesto) {
                this.id_puesto = String(idPuesto);
                this.puestoDropdownAbierto = false;
            }
        };
    }
</script>

<h1 class="page-title">Registrar empleado</h1>

<section class="panel-card">
    <div class="form-card form-card-wide">
        <h2>Nuevo empleado</h2>
        <p>Captura la información general del empleado.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('empleados/guardar') ?>" x-data="empleadoForm()">
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-section-title">
                    Datos personales
                </div>

                <div class="form-group">
                    <label for="nombre_empleado">Nombre</label>
                    <input
                        type="text"
                        id="nombre_empleado"
                        name="nombre_empleado"
                        value="<?= e($old['nombre_empleado'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="apellido_pat_empleado">Apellido paterno</label>
                    <input
                        type="text"
                        id="apellido_pat_empleado"
                        name="apellido_pat_empleado"
                        value="<?= e($old['apellido_pat_empleado'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="apellido_mat_empleado">Apellido materno</label>
                    <input
                        type="text"
                        id="apellido_mat_empleado"
                        name="apellido_mat_empleado"
                        value="<?= e($old['apellido_mat_empleado'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Sexo</label>

                    <input
                        type="hidden"
                        name="sexo"
                        x-model="sexo"
                    >

                    <div class="custom-select form-custom-select" @click.outside="sexoDropdownAbierto = false">
                        <button
                            type="button"
                            class="custom-select-button"
                            @click="sexoDropdownAbierto = !sexoDropdownAbierto"
                        >
                            <span x-text="sexoTexto()"></span>
                            <span class="custom-select-arrow">▾</span>
                        </button>

                        <div
                            class="custom-select-menu"
                            x-show="sexoDropdownAbierto"
                            x-cloak
                        >
                            <button
                                type="button"
                                class="custom-select-option"
                                @click="seleccionarSexo('masculino')"
                            >
                                Masculino
                            </button>

                            <button
                                type="button"
                                class="custom-select-option"
                                @click="seleccionarSexo('femenino')"
                            >
                                Femenino
                            </button>

                            <button
                                type="button"
                                class="custom-select-option"
                                @click="seleccionarSexo('no_especificado')"
                            >
                                No especificado
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-section-title">
                    Identificación y seguridad social
                </div>

                <div class="form-group">
                    <label for="curp">CURP</label>
                    <input
                        type="text"
                        id="curp"
                        name="curp"
                        maxlength="18"
                        value="<?= e($old['curp'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="rfc">RFC</label>
                    <input
                        type="text"
                        id="rfc"
                        name="rfc"
                        maxlength="13"
                        value="<?= e($old['rfc'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="nss">NSS</label>
                    <input
                        type="text"
                        id="nss"
                        name="nss"
                        maxlength="11"
                        value="<?= e($old['nss'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="numero_preafiliacion_imss">Número de preafiliación IMSS</label>
                    <input
                        type="text"
                        id="numero_preafiliacion_imss"
                        name="numero_preafiliacion_imss"
                        value="<?= e($old['numero_preafiliacion_imss'] ?? '') ?>"
                    >
                </div>

                <div class="form-section-title">
                    Puesto y contacto
                </div>

                <div class="form-group">
                    <label>Departamento</label>

                    <input
                        type="hidden"
                        name="id_departamento"
                        x-model="id_departamento"
                    >

                    <div class="custom-select form-custom-select" @click.outside="departamentoDropdownAbierto = false">
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
                            <?php foreach ($departamentos as $departamento): ?>
                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="seleccionarDepartamento('<?= e((string) $departamento['id_departamento']) ?>')"
                                >
                                    <?= e($departamento['nombre_departamento']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Puesto</label>

                    <input
                        type="hidden"
                        name="id_puesto"
                        x-model="id_puesto"
                    >

                    <div class="custom-select form-custom-select" @click.outside="puestoDropdownAbierto = false">
                        <button
                            type="button"
                            class="custom-select-button"
                            :class="{ 'custom-select-disabled': id_departamento === '' }"
                            :disabled="id_departamento === ''"
                            @click="puestoDropdownAbierto = !puestoDropdownAbierto"
                        >
                            <span x-text="id_departamento === '' ? 'Primero selecciona un departamento' : puestoTexto()"></span>
                            <span class="custom-select-arrow">▾</span>
                        </button>

                        <div
                            class="custom-select-menu"
                            x-show="puestoDropdownAbierto && id_departamento !== ''"
                            x-cloak
                        >
                            <template x-for="puesto in puestosFiltrados" :key="puesto.id_puesto">
                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="seleccionarPuesto(puesto.id_puesto)"
                                    x-text="puesto.nombre_puesto"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="form-info form-full">
                    La fecha de ingreso se asignará automáticamente con la fecha del registro:
                    <strong><?= e(date('d/m/Y')) ?></strong>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        maxlength="20"
                        value="<?= e($old['telefono'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        value="<?= e($old['correo'] ?? '') ?>"
                    >
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-warning" type="submit">Guardar empleado</button>
                <a class="btn btn-secondary" href="<?= base_url('empleados') ?>">Cancelar</a>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>