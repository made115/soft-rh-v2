<?php

/** @var array<string, mixed> $contrato */
$contrato = $contrato ?? [];

$nombreCompleto = trim(
    ($contrato['nombre_empleado'] ?? '') . ' ' .
    ($contrato['apellido_pat_empleado'] ?? '') . ' ' .
    ($contrato['apellido_mat_empleado'] ?? '')
);

function pdfContratoFechaLarga(?string $fecha): string
{
    if (!$fecha) {
        return 'No registrada';
    }

    $timestamp = strtotime($fecha);

    if (!$timestamp) {
        return 'No registrada';
    }

    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $dia = date('d', $timestamp);
    $mes = $meses[(int) date('n', $timestamp)];
    $anio = date('Y', $timestamp);

    return $dia . ' de ' . $mes . ' de ' . $anio;
}

function pdfContratoPeriodicidad(?string $periodicidad): string
{
    return match ($periodicidad) {
        'diario' => 'diaria',
        'semanal' => 'semanal',
        'quincenal' => 'quincenal',
        'mensual' => 'mensual',
        default => 'semanal'
    };
}

function pdfContratoTextoSeguro($valor): string
{
    if ($valor === null || $valor === '') {
        return 'No registrado';
    }

    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$fechaInicioLarga = pdfContratoFechaLarga($contrato['fecha_inicio'] ?? null);
$fechaFinLarga = pdfContratoFechaLarga($contrato['fecha_fin'] ?? null);
$periodicidadTexto = pdfContratoPeriodicidad($contrato['periodicidad_pago'] ?? null);
$sueldoDiario = number_format((float) ($contrato['sueldo_diario'] ?? 0), 2);
$empresaNombre = 'PIAXTECO';
$empresaCiudad = 'Atlixco, Puebla';
$representanteLegal = 'Francisco Oliva';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 42px 48px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.55;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 14px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 6px 0 0;
            font-size: 11px;
            color: #4b5563;
        }

        .folio {
            text-align: right;
            font-size: 11px;
            margin-bottom: 18px;
            color: #374151;
        }

        .company-box {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            padding: 10px 12px;
            margin-bottom: 16px;
        }

        .company-box h2 {
            font-size: 13px;
            margin: 0 0 6px;
            text-transform: uppercase;
        }

        .company-box p {
            margin: 2px 0;
            font-size: 11px;
        }

        .section-title {
            background: #f3f4f6;
            border-left: 4px solid #f59e0b;
            padding: 7px 10px;
            font-weight: bold;
            margin: 18px 0 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            vertical-align: top;
        }

        .info-table .label {
            width: 28%;
            background: #f9fafb;
            font-weight: bold;
            color: #374151;
        }

        .paragraph {
            text-align: justify;
            margin: 10px 0;
        }

        .clause-title {
            font-weight: bold;
            margin-top: 12px;
        }

        .signature-table {
            width: 100%;
            margin-top: 72px;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 0 34px;
            vertical-align: bottom;
        }

        .signature-space {
            height: 44px;
        }

        .signature-line {
            border-top: 1.2px solid #111827;
            padding-top: 8px;
            font-weight: bold;
        }

        .signature-role {
            font-size: 10px;
            color: #4b5563;
            margin-top: 3px;
        }

        .small {
            font-size: 10px;
            color: #6b7280;
        }

        .footer-note {
            margin-top: 28px;
            font-size: 10px;
            color: #6b7280;
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Contrato individual de trabajo por tiempo determinado</h1>
        <p>SOFT RH V2 - Documento generado desde el sistema</p>
    </div>

    <div class="folio">
        <strong>Contrato #<?= pdfContratoTextoSeguro($contrato['id_contrato'] ?? '') ?></strong>
        <br>
        Fecha de generación: <?= pdfContratoFechaLarga(date('Y-m-d')) ?>
    </div>

    <div class="company-box">
        <h2>Datos de la empresa</h2>
        <p><strong>Empresa:</strong> <?= pdfContratoTextoSeguro($empresaNombre) ?></p>
        <p><strong>Representante legal:</strong> <?= pdfContratoTextoSeguro($representanteLegal) ?></p>
        <p><strong>Ciudad:</strong> <?= pdfContratoTextoSeguro($empresaCiudad) ?></p>
    </div>

    <div class="section-title">Datos del trabajador</div>

    <table class="info-table">
        <tr>
            <td class="label">Nombre completo</td>
            <td><?= pdfContratoTextoSeguro($nombreCompleto) ?></td>
        </tr>
        <tr>
            <td class="label">CURP</td>
            <td><?= pdfContratoTextoSeguro($contrato['curp'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">RFC</td>
            <td><?= pdfContratoTextoSeguro($contrato['rfc'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">NSS</td>
            <td><?= pdfContratoTextoSeguro($contrato['nss'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Preafiliación IMSS</td>
            <td><?= pdfContratoTextoSeguro($contrato['numero_preafiliacion_imss'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Departamento</td>
            <td><?= pdfContratoTextoSeguro($contrato['nombre_departamento'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Puesto</td>
            <td><?= pdfContratoTextoSeguro($contrato['nombre_puesto'] ?? '') ?></td>
        </tr>
    </table>

    <div class="section-title">Datos del contrato</div>

    <table class="info-table">
        <tr>
            <td class="label">Fecha de inicio</td>
            <td><?= pdfContratoTextoSeguro($fechaInicioLarga) ?></td>
        </tr>
        <tr>
            <td class="label">Fecha de fin</td>
            <td><?= pdfContratoTextoSeguro($fechaFinLarga) ?></td>
        </tr>
        <tr>
            <td class="label">Duración</td>
            <td><?= pdfContratoTextoSeguro(($contrato['duracion_meses'] ?? '') . ' meses') ?></td>
        </tr>
        <tr>
            <td class="label">Sueldo diario</td>
            <td>$<?= pdfContratoTextoSeguro($sueldoDiario) ?> MXN</td>
        </tr>
        <tr>
            <td class="label">Periodicidad de pago</td>
            <td><?= pdfContratoTextoSeguro(ucfirst($periodicidadTexto)) ?></td>
        </tr>
    </table>

    <div class="section-title">Cláusulas generales</div>

    <p class="paragraph">
        En la ciudad de <strong><?= pdfContratoTextoSeguro($empresaCiudad) ?></strong>, se celebra el presente
        contrato individual de trabajo por tiempo determinado entre la empresa
        <strong><?= pdfContratoTextoSeguro($empresaNombre) ?></strong>, representada legalmente por
        <strong><?= pdfContratoTextoSeguro($representanteLegal) ?></strong>, y la persona trabajadora
        <strong><?= pdfContratoTextoSeguro($nombreCompleto) ?></strong>, quien desempeñará el puesto de
        <strong><?= pdfContratoTextoSeguro($contrato['nombre_puesto'] ?? '') ?></strong> dentro del departamento de
        <strong><?= pdfContratoTextoSeguro($contrato['nombre_departamento'] ?? '') ?></strong>.
    </p>

    <p class="clause-title">Primera. Vigencia del contrato.</p>
    <p class="paragraph">
        El presente contrato tendrá vigencia a partir del día <strong><?= pdfContratoTextoSeguro($fechaInicioLarga) ?></strong>
        y concluirá el día <strong><?= pdfContratoTextoSeguro($fechaFinLarga) ?></strong>, salvo que exista renovación,
        terminación anticipada o cualquier otra modificación documentada por el área correspondiente.
    </p>

    <p class="clause-title">Segunda. Puesto y actividades.</p>
    <p class="paragraph">
        La persona trabajadora se compromete a desempeñar las funciones propias del puesto asignado,
        cumpliendo con las políticas internas, indicaciones de supervisión, reglamentos aplicables y medidas
        de seguridad establecidas por la empresa.
    </p>

    <p class="clause-title">Tercera. Salario.</p>
    <p class="paragraph">
        La persona trabajadora percibirá un salario diario de
        <strong>$<?= pdfContratoTextoSeguro($sueldoDiario) ?> MXN</strong>,
        pagadero de forma <strong><?= pdfContratoTextoSeguro($periodicidadTexto) ?></strong>,
        conforme a los procedimientos internos de pago de la empresa.
    </p>

    <p class="clause-title">Cuarta. Confidencialidad y resguardo de información.</p>
    <p class="paragraph">
        La persona trabajadora deberá mantener confidencialidad sobre la información, procesos, documentos,
        datos internos y cualquier otro elemento relacionado con la operación de la empresa al que tenga acceso
        durante la relación laboral.
    </p>

    <p class="clause-title">Quinta. Aceptación.</p>
    <p class="paragraph">
        Ambas partes manifiestan estar de acuerdo con el contenido del presente documento, firmándolo para
        constancia en la fecha de inicio indicada.
    </p>

    <?php if (!empty($contrato['observaciones'])): ?>
        <div class="section-title">Observaciones internas</div>
        <p class="paragraph">
            <?= pdfContratoTextoSeguro($contrato['observaciones']) ?>
        </p>
    <?php endif; ?>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-space"></div>
                <div class="signature-line">
                    <?= pdfContratoTextoSeguro($representanteLegal) ?>
                </div>
                <div class="signature-role">
                    Representante legal de <?= pdfContratoTextoSeguro($empresaNombre) ?>
                </div>
                <div class="small">
                    Nombre y firma
                </div>
            </td>

            <td>
                <div class="signature-space"></div>
                <div class="signature-line">
                    <?= pdfContratoTextoSeguro($nombreCompleto) ?>
                </div>
                <div class="signature-role">
                    Trabajador
                </div>
                <div class="small">
                    Nombre y firma
                </div>
            </td>
        </tr>
    </table>

    <p class="footer-note">
        Este documento fue generado automáticamente por SOFT RH V2 con base en la información registrada
        en el sistema. La empresa deberá revisar y validar el contenido antes de su firma formal.
    </p>
</body>
</html>