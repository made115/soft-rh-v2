<?php

class EmpleadoController extends Controller
{
    public function index(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $empleadoModel = new Empleado();
        $empleados = $empleadoModel->getAll();

        $this->view('empleados/index', [
            'title' => 'Gestión de empleados',
            'empleados' => $empleados
        ]);
    }

    public function create(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $departamentoModel = new Departamento();
        $puestoModel = new Puesto();

        $this->view('empleados/create', [
            'title' => 'Registrar empleado',
            'departamentos' => $departamentoModel->getActive(),
            'puestos' => $puestoModel->getActiveWithDepartment(),
            'errors' => [],
            'old' => []
        ]);
    }

    public function store(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $nombre_empleado = trim($_POST['nombre_empleado'] ?? '');
        $apellido_pat_empleado = trim($_POST['apellido_pat_empleado'] ?? '');
        $apellido_mat_empleado = trim($_POST['apellido_mat_empleado'] ?? '');
        $sexo = $_POST['sexo'] ?? '';
        $curp = strtoupper(trim($_POST['curp'] ?? ''));
        $rfc = strtoupper(trim($_POST['rfc'] ?? ''));
        $nss = trim($_POST['nss'] ?? '');
        $numero_preafiliacion_imss = trim($_POST['numero_preafiliacion_imss'] ?? '');
        $id_departamento = (int) ($_POST['id_departamento'] ?? 0);
        $id_puesto = (int) ($_POST['id_puesto'] ?? 0);
        $fecha_ingreso = date('Y-m-d');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = strtolower(trim($_POST['correo'] ?? ''));

        $old = [
            'nombre_empleado' => $nombre_empleado,
            'apellido_pat_empleado' => $apellido_pat_empleado,
            'apellido_mat_empleado' => $apellido_mat_empleado,
            'sexo' => $sexo,
            'curp' => $curp,
            'rfc' => $rfc,
            'nss' => $nss,
            'numero_preafiliacion_imss' => $numero_preafiliacion_imss,
            'id_departamento' => $id_departamento,
            'id_puesto' => $id_puesto,
            'fecha_ingreso' => $fecha_ingreso,
            'telefono' => $telefono,
            'correo' => $correo
        ];

        $errors = [];

        $nameRegex = "/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'.-]{2,100}$/u";

        if ($nombre_empleado === '' || !preg_match($nameRegex, $nombre_empleado)) {
            $errors[] = 'El nombre del empleado es obligatorio y solo debe contener letras.';
        }

        if ($apellido_pat_empleado === '' || !preg_match($nameRegex, $apellido_pat_empleado)) {
            $errors[] = 'El apellido paterno es obligatorio y solo debe contener letras.';
        }

        if ($apellido_mat_empleado === '' || !preg_match($nameRegex, $apellido_mat_empleado)) {
            $errors[] = 'El apellido materno es obligatorio y solo debe contener letras.';
        }

        if (!in_array($sexo, ['masculino', 'femenino', 'no_especificado'], true)) {
            $errors[] = 'Selecciona un sexo válido.';
        }

        if (!preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/', $curp)) {
            $errors[] = 'La CURP no tiene un formato válido.';
        }

        if (!preg_match('/^[A-ZÑ&]{4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
            $errors[] = 'El RFC debe tener 13 caracteres con formato válido para persona física.';
        }

        if (!preg_match('/^[0-9]{11}$/', $nss)) {
            $errors[] = 'El NSS debe contener exactamente 11 dígitos.';
        }

        $departamentoModel = new Departamento();
        $puestoModel = new Puesto();

        if ($id_departamento <= 0 || !$departamentoModel->existsActive($id_departamento)) {
            $errors[] = 'Selecciona un departamento válido.';
        }

        if ($id_puesto <= 0 || !$puestoModel->existsActiveInDepartment($id_puesto, $id_departamento)) {
            $errors[] = 'Selecciona un puesto válido para el departamento elegido.';
        }

        if ($telefono !== '' && !preg_match('/^[0-9]{10,20}$/', $telefono)) {
            $errors[] = 'El teléfono debe contener solo números y tener entre 10 y 20 dígitos.';
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no tiene un formato válido.';
        }

        $empleadoModel = new Empleado();

        if ($curp !== '' && $empleadoModel->existsByField('curp', $curp)) {
            $errors[] = 'La CURP ya está registrada.';
        }

        if ($rfc !== '' && $empleadoModel->existsByField('rfc', $rfc)) {
            $errors[] = 'El RFC ya está registrado.';
        }

        if ($nss !== '' && $empleadoModel->existsByField('nss', $nss)) {
            $errors[] = 'El NSS ya está registrado.';
        }

        if ($numero_preafiliacion_imss !== '' && $empleadoModel->existsByField('numero_preafiliacion_imss', $numero_preafiliacion_imss)) {
            $errors[] = 'El número de preafiliación IMSS ya está registrado.';
        }

        if ($correo !== '' && $empleadoModel->existsByField('correo', $correo)) {
            $errors[] = 'El correo electrónico ya está registrado.';
        }

        if (!empty($errors)) {
            $this->view('empleados/create', [
                'title' => 'Registrar empleado',
                'departamentos' => $departamentoModel->getActive(),
                'puestos' => $puestoModel->getActiveWithDepartment(),
                'errors' => $errors,
                'old' => $old
            ]);
            return;
        }

        try {
            $usuarioSesion = current_user();

            $id_empleado_creado = $empleadoModel->create([
                'nombre_empleado' => $nombre_empleado,
                'apellido_pat_empleado' => $apellido_pat_empleado,
                'apellido_mat_empleado' => $apellido_mat_empleado,
                'sexo' => $sexo,
                'curp' => $curp,
                'rfc' => $rfc,
                'nss' => $nss,
                'numero_preafiliacion_imss' => $numero_preafiliacion_imss !== '' ? $numero_preafiliacion_imss : null,
                'id_puesto' => $id_puesto,
                'fecha_ingreso' => $fecha_ingreso,
                'telefono' => $telefono !== '' ? $telefono : null,
                'correo' => $correo !== '' ? $correo : null
            ], (int) $usuarioSesion['id_usuario']);

            $this->redirect('empleados/detalle?id=' . $id_empleado_creado . '&creado=1&contrato_pendiente=registro');

        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->view('empleados/create', [
                'title' => 'Registrar empleado',
                'departamentos' => $departamentoModel->getActive(),
                'puestos' => $puestoModel->getActiveWithDepartment(),
                'errors' => ['No se pudo registrar el empleado. Verifica la información e intenta nuevamente.'],
                'old' => $old
            ]);
        }
    }

    public function detalle(): void
    {
        require_auth();
        require_role(['administrador', 'recursos_humanos']);

        $id_empleado = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id_empleado <= 0) {
            $this->redirect('empleados');
        }

        $empleadoModel = new Empleado();
        $empleado = $empleadoModel->getDetalleById($id_empleado);

        if (!$empleado) {
            $this->redirect('empleados');
        }

        $historialLaboral = $empleadoModel->getHistorialLaboralByEmpleado($id_empleado);
        $ultimoMovimiento = $empleadoModel->getUltimoMovimientoByEmpleado($id_empleado);

        $this->view('empleados/detail', [
            'empleado' => $empleado,
            'historialLaboral' => $historialLaboral,
            'ultimoMovimiento' => $ultimoMovimiento
        ]); 
    }

    public function edit(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $id_empleado = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id_empleado <= 0) {
            $this->redirect('empleados');
        }

        $empleadoModel = new Empleado();
        $empleado = $empleadoModel->getDetalleById($id_empleado);

        if (!$empleado) {
            $this->redirect('empleados');
        }

        $departamentoModel = new Departamento();
        $puestoModel = new Puesto();

        $this->view('empleados/edit', [
            'title' => 'Editar empleado',
            'empleado' => $empleado,
            'departamentos' => $departamentoModel->getActive(),
            'puestos' => $puestoModel->getActiveWithDepartment(),
            'errors' => [],
            'old' => $empleado
        ]);
    }

    public function update(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $id_empleado = (int) ($_POST['id_empleado'] ?? 0);

        if ($id_empleado <= 0) {
            $this->redirect('empleados');
        }

        $nombre_empleado = trim($_POST['nombre_empleado'] ?? '');
        $apellido_pat_empleado = trim($_POST['apellido_pat_empleado'] ?? '');
        $apellido_mat_empleado = trim($_POST['apellido_mat_empleado'] ?? '');
        $sexo = $_POST['sexo'] ?? '';
        $curp = strtoupper(trim($_POST['curp'] ?? ''));
        $rfc = strtoupper(trim($_POST['rfc'] ?? ''));
        $nss = trim($_POST['nss'] ?? '');
        $numero_preafiliacion_imss = trim($_POST['numero_preafiliacion_imss'] ?? '');
        $id_departamento = (int) ($_POST['id_departamento'] ?? 0);
        $id_puesto = (int) ($_POST['id_puesto'] ?? 0);
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = strtolower(trim($_POST['correo'] ?? ''));

        $old = [
            'id_empleado' => $id_empleado,
            'nombre_empleado' => $nombre_empleado,
            'apellido_pat_empleado' => $apellido_pat_empleado,
            'apellido_mat_empleado' => $apellido_mat_empleado,
            'sexo' => $sexo,
            'curp' => $curp,
            'rfc' => $rfc,
            'nss' => $nss,
            'numero_preafiliacion_imss' => $numero_preafiliacion_imss,
            'id_departamento' => $id_departamento,
            'id_puesto' => $id_puesto,
            'telefono' => $telefono,
            'correo' => $correo
        ];

        $errors = [];

        $nameRegex = "/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'.-]{2,100}$/u";

        if ($nombre_empleado === '' || !preg_match($nameRegex, $nombre_empleado)) {
            $errors[] = 'El nombre del empleado es obligatorio y solo debe contener letras.';
        }

        if ($apellido_pat_empleado === '' || !preg_match($nameRegex, $apellido_pat_empleado)) {
            $errors[] = 'El apellido paterno es obligatorio y solo debe contener letras.';
        }

        if ($apellido_mat_empleado === '' || !preg_match($nameRegex, $apellido_mat_empleado)) {
            $errors[] = 'El apellido materno es obligatorio y solo debe contener letras.';
        }

        if (!in_array($sexo, ['masculino', 'femenino', 'no_especificado'], true)) {
            $errors[] = 'Selecciona un sexo válido.';
        }

        if (!preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/', $curp)) {
            $errors[] = 'La CURP no tiene un formato válido.';
        }

        if (!preg_match('/^[A-ZÑ&]{4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
            $errors[] = 'El RFC debe tener 13 caracteres con formato válido para persona física.';
        }

        if (!preg_match('/^[0-9]{11}$/', $nss)) {
            $errors[] = 'El NSS debe contener exactamente 11 dígitos.';
        }

        $empleadoModel = new Empleado();
        $departamentoModel = new Departamento();
        $puestoModel = new Puesto();

        $empleadoActual = $empleadoModel->getDetalleById($id_empleado);

        if (!$empleadoActual) {
            $this->redirect('empleados');
        }

        if ($id_departamento <= 0 || !$departamentoModel->existsActive($id_departamento)) {
            $errors[] = 'Selecciona un departamento válido.';
        }

        if ($id_puesto <= 0 || !$puestoModel->existsActiveInDepartment($id_puesto, $id_departamento)) {
            $errors[] = 'Selecciona un puesto válido para el departamento elegido.';
        }

        if ($telefono !== '' && !preg_match('/^[0-9]{10,20}$/', $telefono)) {
            $errors[] = 'El teléfono debe contener solo números y tener entre 10 y 20 dígitos.';
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no tiene un formato válido.';
        }

        if ($curp !== '' && $empleadoModel->existsByFieldExceptId('curp', $curp, $id_empleado)) {
            $errors[] = 'La CURP ya está registrada en otro empleado.';
        }

        if ($rfc !== '' && $empleadoModel->existsByFieldExceptId('rfc', $rfc, $id_empleado)) {
            $errors[] = 'El RFC ya está registrado en otro empleado.';
        }

        if ($nss !== '' && $empleadoModel->existsByFieldExceptId('nss', $nss, $id_empleado)) {
            $errors[] = 'El NSS ya está registrado en otro empleado.';
        }

        if ($numero_preafiliacion_imss !== '' && $empleadoModel->existsByFieldExceptId('numero_preafiliacion_imss', $numero_preafiliacion_imss, $id_empleado)) {
            $errors[] = 'El número de preafiliación IMSS ya está registrado en otro empleado.';
        }

        if ($correo !== '' && $empleadoModel->existsByFieldExceptId('correo', $correo, $id_empleado)) {
            $errors[] = 'El correo electrónico ya está registrado en otro empleado.';
        }

        if (!empty($errors)) {
            $this->view('empleados/edit', [
                'title' => 'Editar empleado',
                'empleado' => $empleadoActual,
                'departamentos' => $departamentoModel->getActive(),
                'puestos' => $puestoModel->getActiveWithDepartment(),
                'errors' => $errors,
                'old' => $old
            ]);
            return;
        }

        $normalizar = function ($valor): string {
            return trim((string) ($valor ?? ''));
        };

        $hayCambios =
            $normalizar($empleadoActual['nombre_empleado']) !== $normalizar($nombre_empleado) ||
            $normalizar($empleadoActual['apellido_pat_empleado']) !== $normalizar($apellido_pat_empleado) ||
            $normalizar($empleadoActual['apellido_mat_empleado']) !== $normalizar($apellido_mat_empleado) ||
            $normalizar($empleadoActual['sexo']) !== $normalizar($sexo) ||
            $normalizar($empleadoActual['curp']) !== $normalizar($curp) ||
            $normalizar($empleadoActual['rfc']) !== $normalizar($rfc) ||
            $normalizar($empleadoActual['nss']) !== $normalizar($nss) ||
            $normalizar($empleadoActual['numero_preafiliacion_imss']) !== $normalizar($numero_preafiliacion_imss) ||
            (int) $empleadoActual['id_puesto'] !== (int) $id_puesto ||
            $normalizar($empleadoActual['telefono']) !== $normalizar($telefono) ||
            $normalizar($empleadoActual['correo']) !== $normalizar($correo);

        if (!$hayCambios) {
            $this->redirect('empleados/detalle?id=' . $id_empleado . '&sin_cambios=1');
        }

        try {
            $usuarioSesion = current_user();

            $empleadoModel->update([
                'id_empleado' => $id_empleado,
                'nombre_empleado' => $nombre_empleado,
                'apellido_pat_empleado' => $apellido_pat_empleado,
                'apellido_mat_empleado' => $apellido_mat_empleado,
                'sexo' => $sexo,
                'curp' => $curp,
                'rfc' => $rfc,
                'nss' => $nss,
                'numero_preafiliacion_imss' => $numero_preafiliacion_imss !== '' ? $numero_preafiliacion_imss : null,
                'id_puesto' => $id_puesto,
                'telefono' => $telefono !== '' ? $telefono : null,
                'correo' => $correo !== '' ? $correo : null
            ], (int) $usuarioSesion['id_usuario']);

            $this->redirect('empleados/detalle?id=' . $id_empleado . '&actualizado=1');
        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->view('empleados/edit', [
                'title' => 'Editar empleado',
                'empleado' => $empleadoActual,
                'departamentos' => $departamentoModel->getActive(),
                'puestos' => $puestoModel->getActiveWithDepartment(),
                'errors' => ['No se pudo actualizar el empleado. Verifica la información e intenta nuevamente.'],
                'old' => $old
            ]);
        }
    }

    public function estado(): void
    {
        require_auth();
        require_role(['administrador', 'recursos_humanos']);

        $id_empleado = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id_empleado <= 0) {
            $this->redirect('empleados');
        }

        $empleadoModel = new Empleado();
        $empleado = $empleadoModel->getDetalleById($id_empleado);

        if (!$empleado) {
            $this->redirect('empleados');
        }

        $this->view('empleados/status', [
            'empleado' => $empleado,
            'errors' => []
        ]);
    }

    public function actualizarEstado(): void
    {
        require_auth();
        require_role(['administrador', 'recursos_humanos']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $id_empleado = (int) ($_POST['id_empleado'] ?? 0);
        $accion_estado = $_POST['accion_estado'] ?? '';
        $motivo_baja = trim($_POST['motivo_baja'] ?? '');
        $contrasena_confirmacion = $_POST['contrasena_confirmacion'] ?? '';

        if ($id_empleado <= 0) {
            $this->redirect('empleados');
        }

        $empleadoModel = new Empleado();
        $empleado = $empleadoModel->getDetalleById($id_empleado);

        if (!$empleado) {
            $this->redirect('empleados');
        }

        $errors = [];

        if ($accion_estado === 'inactivar') {
            if ($empleado['estado_laboral'] !== 'activo') {
                $errors[] = 'El empleado no se encuentra activo.';
            }

            if ($motivo_baja === '') {
                $errors[] = 'El motivo de baja es obligatorio.';
            }

            if (mb_strlen($motivo_baja) < 10 || mb_strlen($motivo_baja) > 255) {
                $errors[] = 'El motivo de baja debe tener entre 10 y 255 caracteres.';
            }
        } elseif ($accion_estado === 'reactivar') {
            if ($empleado['estado_laboral'] !== 'inactivo') {
                $errors[] = 'El empleado no se encuentra inactivo.';
            }
        } else {
            $errors[] = 'Acción de estado no válida.';
        }

        /*
        * Confirmación de contraseña para cualquier cambio de estado:
        * inactivar o reactivar.
        */
        if (in_array($accion_estado, ['inactivar', 'reactivar'], true)) {
            if ($contrasena_confirmacion === '') {
                $errors[] = 'Debes ingresar tu contraseña para confirmar el cambio de estado.';
            } else {
                $usuarioSesion = current_user();
                $usuarioModel = new Usuario();
                $usuarioActual = $usuarioModel->findByIdWithPasswordHash((int) $usuarioSesion['id_usuario']);

                if (
                    !$usuarioActual ||
                    empty($usuarioActual['contrasena_hash']) ||
                    !password_verify($contrasena_confirmacion, $usuarioActual['contrasena_hash'])
                ) {
                    $errors[] = 'La contraseña ingresada no es correcta.';
                }
            }
        }

        if (!empty($errors)) {
            $this->view('empleados/status', [
                'empleado' => $empleado,
                'errors' => $errors
            ]);
            return;
        }

        try {
            $usuarioSesion = current_user();
            $id_usuario_accion = (int) $usuarioSesion['id_usuario'];

            if ($accion_estado === 'inactivar') {
                $empleadoModel->inactivar($id_empleado, $motivo_baja, $id_usuario_accion);
                $this->redirect('empleados/detalle?id=' . $id_empleado . '&inactivado=1');
            }

            if ($accion_estado === 'reactivar') {
                $empleadoModel->reactivar($id_empleado, $id_usuario_accion);
                $this->redirect('empleados/detalle?id=' . $id_empleado . '&reactivado=1&contrato_pendiente=reactivacion');
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());

            $this->view('empleados/status', [
                'empleado' => $empleado,
                'errors' => ['No se pudo actualizar el estado del empleado. Intenta nuevamente.']
            ]);
        }
    }
}