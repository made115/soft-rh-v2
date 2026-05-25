<?php

$contrato = $contrato ?? null;
$errors = $errors ?? [];
$old = $old ?? [];

$nombreCompleto = '';

if ($contrato) {
    $nombreCompleto = trim(
        $contrato['nombre_empleado'] . ' ' .
        $contrato['apellido_pat_empleado'] . ' ' .
        $contrato['apellido_mat_empleado']
    );
}

require_once __DIR__ . '/../layouts/private_header.php';
?>

<script>
    function contratoEditForm() {
        return {
            fecha_inicio: <?= json_encode((string) ($old['fecha_inicio'] ?? '')) ?>,
            fecha_fin: <?= json_encode((string) ($old['fecha_fin'] ?? '')) ?>,
            duracion_meses: <?= json_encode((string) ($old['duracion_meses'] ?? '3')) ?>,
            periodicidad_pago: <?= json_encode((string) ($old['periodicidad_pago'] ?? 'semanal')) ?>,
            periodicidadDropdownAbierto: false,
            fechaFinManual: true,

            periodicidadTexto() {
                if (this.periodicidad_pago === 'diario') {
                    return 'Diario';
                }

                if (this.periodicidad_pago === 'semanal') {
                    return 'Semanal';
                }

                if (this.periodicidad_pago === 'quincenal') {
                    return 'Quincenal';
                }

                if (this.periodicidad_pago === 'mensual') {
                    return 'Mensual';
                }

                return 'Selecciona periodicidad';
            },

            seleccionarPeriodicidad(valor) {
                this.periodicidad_pago = valor;
                this.periodicidadDropdownAbierto = false;
            },

            sumarMeses(fecha, meses) {
                if (!fecha || !meses || Number(meses) <= 0) {
                    return '';
                }

                const partes = fecha.split('-');

                if (partes.length !== 3) {
                    return '';
                }

                const fechaBase = new Date(
                    Number(partes[0]),
                    Number(partes[1]) - 1,
                    Number(partes[2])
                );

                fechaBase.setMonth(fechaBase.getMonth() + Number(meses));

                const anio = fechaBase.getFullYear();
                const mes = String(fechaBase.getMonth() + 1).padStart(2, '0');
                const dia = String(fechaBase.getDate()).padStart(2, '0');

                return `${anio}-${mes}-${dia}`;
            },

            recalcularFechaFin() {
                const fechaCalculada = this.sumarMeses(this.fecha_inicio, this.duracion_meses);

                if (fechaCalculada !== '') {
                    this.fecha_fin = fechaCalculada;
                }
            }
        };
    }
</script>

<h1 class="page-title">Editar contrato vigente</h1>

<section class="panel-card">
    <div class="form-card form-card-wide">
        <h2>Datos del contrato</h2>
        <p>
            Actualiza únicamente los datos permitidos del contrato vigente.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$contrato): ?>
            <div class="alert-error">
                No se encontró información del contrato.
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="<?= base_url('contratos') ?>">
                    Volver
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= base_url('contratos/actualizar') ?>" x-data="contratoEditForm()">
                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="id_contrato"
                    value="<?= e((string) $contrato['id_contrato']) ?>"
                >

                <div class="form-grid">
                    <div class="form-section-title">
                        Datos no editables
                    </div>

                    <div class="form-group">
                        <label>Empleado</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($nombreCompleto) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Contrato</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="Contrato #<?= e((string) $contrato['id_contrato']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Departamento</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($contrato['nombre_departamento']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Puesto</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($contrato['nombre_puesto']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Registrado por</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($contrato['usuario_registro']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Estado del contrato</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e(ucfirst($contrato['estado_contrato'])) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-section-title">
                        Datos editables
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            x-model="fecha_inicio"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="duracion_meses">Duración en meses</label>
                        <input
                            type="number"
                            id="duracion_meses"
                            name="duracion_meses"
                            min="1"
                            max="60"
                            x-model="duracion_meses"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            x-model="fecha_fin"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="recalcularFechaFin()"
                        >
                            Recalcular fecha fin
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="sueldo_diario">Sueldo diario</label>
                        <input
                            type="text"
                            id="sueldo_diario"
                            name="sueldo_diario"
                            inputmode="decimal"
                            value="<?= e($old['sueldo_diario'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Periodicidad de pago</label>

                        <input
                            type="hidden"
                            name="periodicidad_pago"
                            x-model="periodicidad_pago"
                        >

                        <div class="custom-select form-custom-select" @click.outside="periodicidadDropdownAbierto = false">
                            <button
                                type="button"
                                class="custom-select-button"
                                @click="periodicidadDropdownAbierto = !periodicidadDropdownAbierto"
                            >
                                <span x-text="periodicidadTexto()"></span>
                                <span class="custom-select-arrow">▾</span>
                            </button>

                            <div
                                class="custom-select-menu"
                                x-show="periodicidadDropdownAbierto"
                                x-cloak
                            >
                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="seleccionarPeriodicidad('diario')"
                                >
                                    Diario
                                </button>

                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="seleccionarPeriodicidad('semanal')"
                                >
                                    Semanal
                                </button>

                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="seleccionarPeriodicidad('quincenal')"
                                >
                                    Quincenal
                                </button>

                                <button
                                    type="button"
                                    class="custom-select-option"
                                    @click="seleccionarPeriodicidad('mensual')"
                                >
                                    Mensual
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-full">
                        <label for="observaciones">Observaciones</label>
                        <textarea
                            id="observaciones"
                            name="observaciones"
                            rows="4"
                            maxlength="255"
                        ><?= e($old['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a
                        class="btn btn-secondary"
                        href="<?= base_url('contratos/historial?id_empleado=' . (int) $contrato['id_empleado']) ?>"
                    >
                        Cancelar
                    </a>

                    <button class="btn btn-primary" type="submit">
                        Guardar cambios
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>
