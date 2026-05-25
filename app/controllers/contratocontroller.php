<?php

class ContratoController extends Controller
{
    private array $periodicidadesPermitidas = ['diario', 'semanal', 'quincenal', 'mensual'];
    private int $diasPermitidosParaRenovar = 15;

    public function index(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $contratoModel = new Contrato();
        $empleados = $contratoModel->getResumenPorEmpleado();

        $this->view('contratos/index', [
            'title' => 'Gestión de contratos',
            'empleados' => $empleados
        ]);
    }

    public function create(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $id_empleado = isset($_GET['id_empleado']) ? (int) $_GET['id_empleado'] : 0;

        if ($id_empleado <= 0) {
            $this->redirect('contratos');
        }

        $contratoModel = new Contrato();
        $empleado = $contratoModel->getEmpleadoParaContrato($id_empleado);

        if (!$empleado || $empleado['estado_laboral'] !== 'activo') {
            $this->redirect('contratos');
        }

        $contratoVigente = $contratoModel->getVigenteByEmpleado($id_empleado);
        $renovacionBloqueada = false;
        $mensajeRenovacionBloqueada = '';

        if ($contratoVigente && !$this->puedeRenovarContrato($contratoVigente['fecha_fin'])) {
            $renovacionBloqueada = true;
            $mensajeRenovacionBloqueada = 'Este contrato todavía no puede renovarse. La renovación estará disponible cuando falten 15 días o menos para su vencimiento.';
        }

        $fecha_inicio = $contratoVigente
            ? $this->obtenerFechaInicioRenovacion($contratoVigente['fecha_fin'])
            : date('Y-m-d');

        $fecha_fin = (new DateTimeImmutable($fecha_inicio))->modify('+3 months')->format('Y-m-d');

        $this->view('contratos/create', [
            'title' => $contratoVigente ? 'Renovar contrato' : 'Nuevo contrato',
            'empleado' => $empleado,
            'contratoVigente' => $contratoVigente,
            'renovacionBloqueada' => $renovacionBloqueada,
            'mensajeRenovacionBloqueada' => $mensajeRenovacionBloqueada,
            'errors' => [],
            'old' => [
                'id_empleado' => $id_empleado,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'duracion_meses' => 3,
                'sueldo_diario' => '',
                'periodicidad_pago' => 'semanal',
                'observaciones' => ''
            ]
        ]);
    }

    public function store(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $id_empleado = (int) ($_POST['id_empleado'] ?? 0);

        if ($id_empleado <= 0) {
            $this->redirect('contratos');
        }

        $contratoModel = new Contrato();
        $empleado = $contratoModel->getEmpleadoParaContrato($id_empleado);

        if (!$empleado || $empleado['estado_laboral'] !== 'activo') {
            $this->redirect('contratos');
        }

        $contratoVigente = $contratoModel->getVigenteByEmpleado($id_empleado);

        $old = $this->obtenerDatosFormulario($id_empleado);
        $old['fecha_inicio'] = $contratoVigente
            ? $this->obtenerFechaInicioRenovacion($contratoVigente['fecha_fin'])
            : date('Y-m-d');

        $errors = $this->validarDatosContrato($old);

        if ($contratoVigente && !$this->puedeRenovarContrato($contratoVigente['fecha_fin'])) {
            $errors[] = 'Este contrato todavía no puede renovarse. La renovación estará disponible cuando falten 15 días o menos para su vencimiento.';
        }

        if (!empty($errors)) {
            $this->view('contratos/create', [
                'title' => $contratoVigente ? 'Renovar contrato' : 'Nuevo contrato',
                'empleado' => $empleado,
                'contratoVigente' => $contratoVigente,
                'errors' => $errors,
                'old' => $old
            ]);
            return;
        }

        try {
            $usuarioSesion = current_user();

            $contratoModel->create([
                'id_empleado' => $id_empleado,
                'fecha_inicio' => $old['fecha_inicio'],
                'fecha_fin' => $old['fecha_fin'],
                'duracion_meses' => (int) $old['duracion_meses'],
                'sueldo_diario' => $old['sueldo_diario'],
                'periodicidad_pago' => $old['periodicidad_pago'],
                'observaciones' => $old['observaciones'] !== '' ? $old['observaciones'] : null
            ], (int) $usuarioSesion['id_usuario']);

            $this->redirect('contratos?guardado=1');
        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->view('contratos/create', [
                'title' => $contratoVigente ? 'Renovar contrato' : 'Nuevo contrato',
                'empleado' => $empleado,
                'contratoVigente' => $contratoVigente,
                'errors' => ['No se pudo guardar el contrato. Verifica la información e intenta nuevamente.'],
                'old' => $old
            ]);
        }
    }

    public function edit(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $id_contrato = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id_contrato <= 0) {
            $this->redirect('contratos');
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->getById($id_contrato);

        if (!$contrato || $contrato['estado_contrato'] !== 'vigente') {
            $this->redirect('contratos');
        }

        if ($contratoModel->hasPdfActivo($id_contrato)) {
            $this->redirect('contratos/movimientos?id=' . $id_contrato . '&correccion_bloqueada=1');
        }

        $this->view('contratos/edit', [
            'title' => 'Corregir contrato vigente',
            'contrato' => $contrato,
            'errors' => [],
            'old' => $contrato
        ]);
    }

    public function update(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $id_contrato = (int) ($_POST['id_contrato'] ?? 0);

        if ($id_contrato <= 0) {
            $this->redirect('contratos');
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->getById($id_contrato);

        if (!$contrato || $contrato['estado_contrato'] !== 'vigente') {
            $this->redirect('contratos');
        }

        if ($contratoModel->hasPdfActivo($id_contrato)) {
            $this->redirect('contratos/movimientos?id=' . $id_contrato . '&correccion_bloqueada=1');
        }

        $old = $this->obtenerDatosFormulario((int) $contrato['id_empleado']);
        $old['id_contrato'] = $id_contrato;

        $errors = $this->validarDatosContrato($old);

        if (!empty($errors)) {
            $this->view('contratos/edit', [
                'title' => 'Corregir contrato vigente',
                'contrato' => $contrato,
                'errors' => $errors,
                'old' => $old
            ]);
            return;
        }

        try {
            $usuarioSesion = current_user();

            $contratoModel->update([
                'id_contrato' => $id_contrato,
                'fecha_inicio' => $old['fecha_inicio'],
                'fecha_fin' => $old['fecha_fin'],
                'duracion_meses' => (int) $old['duracion_meses'],
                'sueldo_diario' => $old['sueldo_diario'],
                'periodicidad_pago' => $old['periodicidad_pago'],
                'observaciones' => $old['observaciones'] !== '' ? $old['observaciones'] : null
            ], (int) $usuarioSesion['id_usuario']);

            $this->redirect('contratos/historial?id_empleado=' . (int) $contrato['id_empleado'] . '&actualizado=1');
        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->view('contratos/edit', [
                'title' => 'Corregir contrato vigente',
                'contrato' => $contrato,
                'errors' => ['No se pudo actualizar el contrato. Verifica la información e intenta nuevamente.'],
                'old' => $old
            ]);
        }
    }

    public function historial(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $id_empleado = isset($_GET['id_empleado']) ? (int) $_GET['id_empleado'] : 0;

        if ($id_empleado <= 0) {
            $this->redirect('contratos');
        }

        $contratoModel = new Contrato();
        $empleado = $contratoModel->getEmpleadoParaContrato($id_empleado);

        if (!$empleado) {
            $this->redirect('contratos');
        }

        $this->view('contratos/historial', [
            'title' => 'Historial de contratos',
            'empleado' => $empleado,
            'contratos' => $contratoModel->getHistorialByEmpleado($id_empleado),
            'bitacora' => $contratoModel->getBitacoraByEmpleado($id_empleado)
        ]);
    }

    public function movimientos(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $id_contrato = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id_contrato <= 0) {
            $this->redirect('contratos');
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->getById($id_contrato);

        if (!$contrato) {
            $this->redirect('contratos');
        }

        $this->view('contratos/movimientos', [
            'title' => 'Movimientos del contrato',
            'contrato' => $contrato,
            'bitacora' => $contratoModel->getBitacoraByContrato($id_contrato)
        ]);
    }

    private function obtenerDatosFormulario(int $id_empleado): array
    {
        $sueldo_diario = str_replace(',', '.', trim($_POST['sueldo_diario'] ?? ''));

        return [
            'id_empleado' => $id_empleado,
            'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
            'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
            'duracion_meses' => (int) ($_POST['duracion_meses'] ?? 0),
            'sueldo_diario' => $sueldo_diario,
            'periodicidad_pago' => $_POST['periodicidad_pago'] ?? '',
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];
    }

    private function validarDatosContrato(array $data): array
    {
        $errors = [];

        if (!$this->fechaValida($data['fecha_inicio'])) {
            $errors[] = 'La fecha de inicio no es válida.';
        }

        if (!$this->fechaValida($data['fecha_fin'])) {
            $errors[] = 'La fecha de fin no es válida.';
        }

        if ($this->fechaValida($data['fecha_inicio']) && $this->fechaValida($data['fecha_fin'])) {
            if (strtotime($data['fecha_fin']) < strtotime($data['fecha_inicio'])) {
                $errors[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
            }
        }

        if ((int) $data['duracion_meses'] <= 0 || (int) $data['duracion_meses'] > 60) {
            $errors[] = 'La duración debe estar entre 1 y 60 meses.';
        }

        if ($data['sueldo_diario'] === '' || !is_numeric($data['sueldo_diario']) || (float) $data['sueldo_diario'] <= 0) {
            $errors[] = 'El sueldo diario debe ser un número mayor a cero.';
        }

        if (!in_array($data['periodicidad_pago'], $this->periodicidadesPermitidas, true)) {
            $errors[] = 'Selecciona una periodicidad de pago válida.';
        }

        if (mb_strlen($data['observaciones']) > 255) {
            $errors[] = 'Las observaciones no deben superar los 255 caracteres.';
        }

        return $errors;
    }

    private function fechaValida(string $fecha): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $fecha);

        return $date && $date->format('Y-m-d') === $fecha;
    }

    private function puedeRenovarContrato(string $fecha_fin): bool
    {
        $hoy = new DateTimeImmutable('today');
        $fechaFin = new DateTimeImmutable($fecha_fin);

        $diasRestantes = (int) $hoy->diff($fechaFin)->format('%r%a');

        return $diasRestantes <= $this->diasPermitidosParaRenovar;
    }

    private function obtenerFechaInicioRenovacion(string $fecha_fin_anterior): string
    {
        return (new DateTimeImmutable($fecha_fin_anterior))
            ->modify('+1 day')
            ->format('Y-m-d');
    }
}