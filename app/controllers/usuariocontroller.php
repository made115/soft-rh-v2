<?php

class UsuarioController extends Controller
{
    public function index(): void
    {
        require_role(['administrador']);

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->getAll();

        $this->view('usuarios/index', [
            'title' => 'Gestión de usuarios',
            'usuarios' => $usuarios
        ]);
    }

    public function create(): void
    {
        require_role(['administrador']);

        $rolModel = new Rol();
        $roles = $rolModel->getAll();

        $this->view('usuarios/create', [
            'title' => 'Registrar usuario',
            'roles' => $roles,
            'errors' => [],
            'old' => []
        ]);
    }

    public function store(): void
    {
        require_role(['administrador']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $nombre_usuario = strtolower(trim($_POST['nombre_usuario'] ?? ''));
        $id_rol = (int) ($_POST['id_rol'] ?? 0);
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
        $estado = $_POST['estado'] ?? 'activo';

        $old = [
            'nombre' => $nombre,
            'nombre_usuario' => $nombre_usuario,
            'id_rol' => $id_rol,
            'estado' => $estado
        ];

        $errors = [];

        if ($nombre === '') {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if ($nombre_usuario === '') {
            $errors[] = 'El nombre de usuario es obligatorio.';
        } elseif (!preg_match('/^[a-z0-9._-]{3,60}$/', $nombre_usuario)) {
            $errors[] = 'El usuario debe tener de 3 a 60 caracteres y solo puede usar letras minúsculas, números, punto, guion o guion bajo.';
        }

        $rolModel = new Rol();

        if ($id_rol <= 0 || !$rolModel->exists($id_rol)) {
            $errors[] = 'Selecciona un rol válido.';
        }

        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            $errors[] = 'Selecciona un estado válido.';
        }

        if (strlen($contrasena) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $contrasena)) {
            $errors[] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }

        if (!preg_match('/[a-z]/', $contrasena)) {
            $errors[] = 'La contraseña debe incluir al menos una letra minúscula.';
        }

        if (!preg_match('/[0-9]/', $contrasena)) {
            $errors[] = 'La contraseña debe incluir al menos un número.';
        }

        if ($contrasena !== $confirmar_contrasena) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        $usuarioModel = new Usuario();

        if ($nombre_usuario !== '' && $usuarioModel->usernameExists($nombre_usuario)) {
            $errors[] = 'El nombre de usuario ya está registrado.';
        }

        if (!empty($errors)) {
            $this->view('usuarios/create', [
                'title' => 'Registrar usuario',
                'roles' => $rolModel->getAll(),
                'errors' => $errors,
                'old' => $old
            ]);
            return;
        }

        $usuarioModel->create([
            'id_rol' => $id_rol,
            'nombre' => $nombre,
            'nombre_usuario' => $nombre_usuario,
            'contrasena_hash' => password_hash($contrasena, PASSWORD_DEFAULT),
            'estado' => $estado,
            'requiere_cambio_contrasena' => 1
        ]);

        $this->redirect('usuarios?creado=1');
    }

    public function edit(): void
    {
        require_role(['administrador']);

        $id_usuario = (int) ($_GET['id'] ?? 0);

        if ($id_usuario <= 0) {
            $this->redirect('usuarios');
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($id_usuario);

        if (!$usuario) {
            $this->redirect('usuarios');
        }

        $rolModel = new Rol();
        $roles = $rolModel->getAll();

        $this->view('usuarios/edit', [
            'title' => 'Editar usuario',
            'usuario' => $usuario,
            'roles' => $roles,
            'errors' => []
        ]);
    }

    public function update(): void
    {
        require_role(['administrador']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $id_usuario = (int) ($_POST['id_usuario'] ?? 0);

        if ($id_usuario <= 0) {
            $this->redirect('usuarios');
        }

        $usuarioModel = new Usuario();
        $usuarioActual = $usuarioModel->findById($id_usuario);

        if (!$usuarioActual) {
            $this->redirect('usuarios');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $nombre_usuario = strtolower(trim($_POST['nombre_usuario'] ?? ''));
        $id_rol = (int) ($_POST['id_rol'] ?? 0);
        $estado = $_POST['estado'] ?? 'activo';

        $usuario = [
            'id_usuario' => $id_usuario,
            'nombre' => $nombre,
            'nombre_usuario' => $nombre_usuario,
            'id_rol' => $id_rol,
            'estado' => $estado
        ];

        $errors = [];

        if ($nombre === '') {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if ($nombre_usuario === '') {
            $errors[] = 'El nombre de usuario es obligatorio.';
        } elseif (!preg_match('/^[a-z0-9._-]{3,60}$/', $nombre_usuario)) {
            $errors[] = 'El usuario debe tener de 3 a 60 caracteres y solo puede usar letras minúsculas, números, punto, guion o guion bajo.';
        }

        $rolModel = new Rol();

        if ($id_rol <= 0 || !$rolModel->exists($id_rol)) {
            $errors[] = 'Selecciona un rol válido.';
        }

        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            $errors[] = 'Selecciona un estado válido.';
        }

        if ($usuarioModel->usernameExistsExcept($nombre_usuario, $id_usuario)) {
            $errors[] = 'El nombre de usuario ya está registrado por otro usuario.';
        }

        $usuarioSesion = current_user();

        if (
            isset($usuarioSesion['id_usuario']) &&
            (int) $usuarioSesion['id_usuario'] === $id_usuario &&
            $estado === 'inactivo'
        ) {
            $errors[] = 'No puedes inactivar tu propio usuario mientras tienes la sesión iniciada.';
        }

        if (!empty($errors)) {
            $this->view('usuarios/edit', [
                'title' => 'Editar usuario',
                'usuario' => $usuario,
                'roles' => $rolModel->getAll(),
                'errors' => $errors
            ]);
            return;
        }

        $usuarioModel->update($id_usuario, [
            'id_rol' => $id_rol,
            'nombre' => $nombre,
            'nombre_usuario' => $nombre_usuario,
            'estado' => $estado
        ]);

        $this->redirect('usuarios?actualizado=1');
    }

    public function changeStatus(): void
    {
        require_role(['administrador']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $id_usuario = (int) ($_POST['id_usuario'] ?? 0);
        $contrasena_confirmacion = $_POST['contrasena_confirmacion'] ?? '';

        if ($id_usuario <= 0) {
            $this->redirect('usuarios');
        }

        $usuarioSesion = current_user();

        if (
            isset($usuarioSesion['id_usuario']) &&
            (int) $usuarioSesion['id_usuario'] === $id_usuario
        ) {
            $this->redirect('usuarios?error=self_status');
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($id_usuario);

        if (!$usuario) {
            $this->redirect('usuarios');
        }

        $nuevoEstado = $usuario['estado'] === 'activo' ? 'inactivo' : 'activo';

        if ($nuevoEstado === 'inactivo') {
            if ($contrasena_confirmacion === '') {
                $this->redirect('usuarios?error=status_password_required');
            }

            $usuarioActual = $usuarioModel->findByIdWithPasswordHash((int) $usuarioSesion['id_usuario']);

            if (
                !$usuarioActual ||
                empty($usuarioActual['contrasena_hash']) ||
                !password_verify($contrasena_confirmacion, $usuarioActual['contrasena_hash'])
            ) {
                $this->redirect('usuarios?error=status_wrong_password');
            }
        }

        $usuarioModel->updateStatus($id_usuario, $nuevoEstado);

        $this->redirect('usuarios?estado=1');
    }

    public function password(): void
    {
        require_role(['administrador']);

        $id_usuario = (int) ($_GET['id'] ?? 0);

        if ($id_usuario <= 0) {
            $this->redirect('usuarios');
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($id_usuario);

        if (!$usuario) {
            $this->redirect('usuarios');
        }

        $this->view('usuarios/password', [
            'title' => 'Restablecer contraseña',
            'usuario' => $usuario,
            'errors' => []
        ]);
    }

    public function updatePassword(): void
    {
        require_role(['administrador']);

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $id_usuario = (int) ($_POST['id_usuario'] ?? 0);
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

        if ($id_usuario <= 0) {
            $this->redirect('usuarios');
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($id_usuario);

        if (!$usuario) {
            $this->redirect('usuarios');
        }

        $errors = [];

        if (strlen($contrasena) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $contrasena)) {
            $errors[] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }

        if (!preg_match('/[a-z]/', $contrasena)) {
            $errors[] = 'La contraseña debe incluir al menos una letra minúscula.';
        }

        if (!preg_match('/[0-9]/', $contrasena)) {
            $errors[] = 'La contraseña debe incluir al menos un número.';
        }

        if ($contrasena !== $confirmar_contrasena) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errors)) {
            $this->view('usuarios/password', [
                'title' => 'Restablecer contraseña',
                'usuario' => $usuario,
                'errors' => $errors
            ]);
            return;
        }

        $usuarioModel->updatePassword(
            $id_usuario,
            password_hash($contrasena, PASSWORD_DEFAULT)
        );

        $this->redirect('usuarios?password=1');
    }
}