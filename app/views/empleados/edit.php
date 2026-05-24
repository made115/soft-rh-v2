<?php

$empleado = $empleado ?? null;
$departamentos = $departamentos ?? [];
$puestos = $puestos ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

require_once __DIR__ . '/../layouts/private_header.php';

$idEmpleado = (int) ($old['id_empleado'] ?? $empleado['id_empleado'] ?? 0);
$idDepartamentoSeleccionado = (int) ($old['id_departamento'] ?? $empleado['id_departamento'] ?? 0);
$idPuestoSeleccionado = (int) ($old['id_puesto'] ?? $empleado['id_puesto'] ?? 0);
?>

<h1 class="page-title">Editar empleado</h1>

<section class="panel-card form-card-wide">
    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <strong>Revisa la información:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$empleado): ?>
        <div class="alert-error">
            No se encontró información del empleado.
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= base_url('empleados') ?>">
                Volver al listado
            </a>
        </div>
    <?php else: ?>

        <form
            method="POST"
            action="<?= base_url('empleados/actualizar') ?>"
            x-data='{
                departamentos: <?= json_encode($departamentos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                puestos: <?= json_encode($puestos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,

                id_departamento: <?= json_encode((string) $idDepartamentoSeleccionado) ?>,
                id_puesto: <?= json_encode((string) $idPuestoSeleccionado) ?>,

                departamentoDropdownAbierto: false,
                puestoDropdownAbierto: false,

                get puestosFiltrados() {
                    if (this.id_departamento === "") {
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

                    return departamento ? departamento.nombre_departamento : "Selecciona un departamento";
                },

                puestoTexto() {
                    const puesto = this.puestos.find((puesto) => {
                        return String(puesto.id_puesto) === String(this.id_puesto);
                    });

                    return puesto ? puesto.nombre_puesto : "Selecciona un puesto";
                },

                seleccionarDepartamento(idDepartamento) {
                    this.id_departamento = String(idDepartamento);
                    this.id_puesto = "";
                    this.departamentoDropdownAbierto = false;
                },

                seleccionarPuesto(idPuesto) {
                    this.id_puesto = String(idPuesto);
                    this.puestoDropdownAbierto = false;
                }
            }'
        >
            <?= csrf_field() ?>

            <input 
                type="hidden" 
                name="id_empleado" 
                value="<?= e((string) $idEmpleado) ?>"
            >

            <div class="form-notice">
                La fecha de ingreso y el estado laboral no se editan desde este formulario.
            </div>

            <div class="form-grid">
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
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Selecciona una opción</option>
                        <option value="masculino" <?= (($old['sexo'] ?? '') === 'masculino') ? 'selected' : '' ?>>
                            Masculino
                        </option>
                        <option value="femenino" <?= (($old['sexo'] ?? '') === 'femenino') ? 'selected' : '' ?>>
                            Femenino
                        </option>
                        <option value="no_especificado" <?= (($old['sexo'] ?? '') === 'no_especificado') ? 'selected' : '' ?>>
                            No especificado
                        </option>
                    </select>
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
                        maxlength="30"
                        value="<?= e($old['numero_preafiliacion_imss'] ?? '') ?>"
                    >
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
                    <label for="correo">Correo</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        maxlength="120"
                        value="<?= e($old['correo'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Fecha de ingreso</label>
                    <input
                        type="text"
                        value="<?= e($empleado['fecha_ingreso']) ?>"
                        disabled
                    >
                </div>

                <div class="form-group">
                    <label>Estado laboral</label>
                    <input
                        type="text"
                        value="<?= e(ucfirst($empleado['estado_laboral'])) ?>"
                        disabled
                    >
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="<?= base_url('empleados/detalle?id=' . $idEmpleado) ?>">
                    Cancelar
                </a>

                <button class="btn btn-primary" type="submit">
                    Guardar cambios
                </button>
            </div>
        </form>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>