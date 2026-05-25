<?php

$empleado = $empleado ?? null;
$contratoVigente = $contratoVigente ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$renovacionBloqueada = $renovacionBloqueada ?? false;
$mensajeRenovacionBloqueada = $mensajeRenovacionBloqueada ?? '';

$nombreCompleto = '';

if ($empleado) {
    $nombreCompleto = trim(
        $empleado['nombre_empleado'] . ' ' .
        $empleado['apellido_pat_empleado'] . ' ' .
        $empleado['apellido_mat_empleado']
    );
}

function mostrarDatoContratoCreate($valor): string
{
    if ($valor === null || $valor === '') {
        return 'No registrado';
    }

    return e((string) $valor);
}

require_once __DIR__ . '/../layouts/private_header.php';
?>

<script>
    function contratoForm() {
        return {
            fecha_inicio: <?= json_encode((string) ($old['fecha_inicio'] ?? date('Y-m-d'))) ?>,
            fecha_fin: <?= json_encode((string) ($old['fecha_fin'] ?? date('Y-m-d'))) ?>,
            duracion_meses: <?= json_encode((string) ($old['duracion_meses'] ?? '3')) ?>,
            periodicidad_pago: <?= json_encode((string) ($old['periodicidad_pago'] ?? 'semanal')) ?>,
            periodicidadDropdownAbierto: false,
            fechaFinManual: false,

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

            actualizarFechaFin() {
                const fechaCalculada = this.sumarMeses(this.fecha_inicio, this.duracion_meses);

                if (fechaCalculada !== '') {
                    this.fecha_fin = fechaCalculada;
                }
            },

            formatearSueldo() {
                const input = this.$refs.sueldoDiario;
                let valor = input.value.replace(/[^0-9.]/g, '');

                if (valor === '') {
                    input.value = '';
                    return;
                }

                const numero = Number(valor);

                if (!Number.isNaN(numero)) {
                    input.value = numero.toFixed(2);
                }
            }
        };
    }
</script>

<h1 class="page-title">
    <?= $contratoVigente ? 'Renovar contrato' : 'Nuevo contrato' ?>
</h1>

<section class="panel-card">
    <div class="form-card form-card-wide">
        <h2>
            <?= $contratoVigente ? 'Renovación de contrato' : 'Registro de contrato' ?>
        </h2>

        <p>
            Revisa la información precargada del empleado y captura los datos del contrato.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($contratoVigente && !$renovacionBloqueada): ?>
            <div class="form-info" style="margin-bottom: 18px;">
                Este empleado ya tiene un contrato vigente. Al guardar, el contrato anterior se marcará como renovado y el nuevo contrato iniciará un día después de la fecha fin anterior.
            </div>
        <?php endif; ?>

        <?php if ($empleado && $renovacionBloqueada): ?>
            <div class="alert-error">
                <?= e($mensajeRenovacionBloqueada) ?>
            </div>

            <div class="form-actions">
                <a
                    class="btn btn-secondary"
                    href="<?= base_url('contratos/historial?id_empleado=' . (int) $empleado['id_empleado']) ?>"
                >
                    Volver al historial
                </a>
            </div>
        <?php endif; ?>

        <?php if (!$empleado): ?>
            <div class="alert-error">
                No se encontró información del empleado.
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="<?= base_url('contratos') ?>">
                    Volver
                </a>
            </div>
        <?php else: ?>
            <?php if ($empleado && !$renovacionBloqueada): ?>
                <form method="POST" action="<?= base_url('contratos/guardar') ?>" x-data="contratoForm()">
                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="id_empleado"
                    value="<?= e((string) $empleado['id_empleado']) ?>"
                >

                <div class="form-grid">
                    <div class="form-section-title">
                        Datos del empleado
                    </div>

                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($nombreCompleto) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Estado laboral</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e(ucfirst($empleado['estado_laboral'])) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Departamento</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($empleado['nombre_departamento']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Puesto</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($empleado['nombre_puesto']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>CURP</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($empleado['curp']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>RFC</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($empleado['rfc']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>NSS</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($empleado['nss']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Fecha de ingreso laboral</label>
                        <input
                            class="readonly-input"
                            type="text"
                            value="<?= e($empleado['fecha_ingreso']) ?>"
                            disabled
                        >
                    </div>

                    <div class="form-section-title">
                        Datos del contrato
                    </div>

                    <div class="form-group">
                        <label>Fecha de inicio</label>

                        <input
                            class="readonly-input"
                            type="date"
                            value="<?= e($old['fecha_inicio'] ?? date('Y-m-d')) ?>"
                            disabled
                        >

                        <input
                            type="hidden"
                            name="fecha_inicio"
                            x-model="fecha_inicio"
                        >

                        <small class="form-help">
                            Se asigna automáticamente con la fecha actual.
                        </small>
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
                            @input="actualizarFechaFin()"
                            required
                        >
                        <small class="form-help">
                            Por defecto se utiliza contrato trimestral.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            x-model="fecha_fin"
                            @input="fechaFinManual = true"
                            required
                        >
                        <small class="form-help">
                            Puedes modificarla manualmente si el periodo no es exacto.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="sueldo_diario">Sueldo diario</label>

                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700;">
                                $
                            </span>

                            <input
                                type="text"
                                id="sueldo_diario"
                                name="sueldo_diario"
                                inputmode="decimal"
                                x-ref="sueldoDiario"
                                value="<?= e($old['sueldo_diario'] ?? '') ?>"
                                placeholder="350.00"
                                style="padding-left: 32px;"
                                @blur="formatearSueldo()"
                                required
                            >
                        </div>

                        <small class="form-help">
                            Monto en moneda nacional MXN. Si capturas una cantidad cerrada, se guardará con .00.
                        </small>
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
                            placeholder="Observaciones internas del contrato"
                        ><?= e($old['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="<?= base_url('contratos') ?>">
                        Cancelar
                    </a>

                    <button class="btn btn-primary" type="submit">
                        Guardar contrato
                    </button>
                </div>
            </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>