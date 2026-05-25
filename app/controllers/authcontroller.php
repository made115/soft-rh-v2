<?php

class AuthController extends Controller
{
    public function login(): void
    {
        require_guest();

        $this->view('auth/login', [
            'title' => 'Iniciar sesión'
        ]);
    }

    public function authenticate(): void
    {
        require_guest();

        if (!csrf_validate()) {
            $this->view('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'La solicitud no es válida. Intenta nuevamente.'
            ]);
            return;
        }

        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if ($nombre_usuario === '' || $contrasena === '') {
            $this->view('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Ingresa tu usuario y contraseña.'
            ]);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByUsername($nombre_usuario);

        if (!$usuario || !password_verify($contrasena, $usuario['contrasena_hash'])) {
            $this->view('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Usuario o contraseña incorrectos.'
            ]);
            return;
        }

        if ($usuario['estado'] !== 'activo') {
            $this->view('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'El usuario se encuentra inactivo.'
            ]);
            return;
        }

        login_user($usuario);
        $usuarioModel->updateLastAccess((int) $usuario['id_usuario']);

        if ((int) $usuario['requiere_cambio_contrasena'] === 1) {
            $this->redirect('cambiar-contrasena');
        }

        $this->redirect('/');
    }

    public function logout(): void
    {
        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        logout_user();

        $this->redirect('login');
        exit;
    }

    public function changePassword(): void
    {
        require_auth();

        $this->view('auth/change_password', [
            'title' => 'Cambiar contraseña',
            'errors' => []
        ]);
    }

    public function updateOwnPassword(): void
    {
        require_auth();

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

        $usuarioSesion = current_user();

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByIdWithPasswordHash((int) $usuarioSesion['id_usuario']);

        if (!$usuario) {
            logout_user();
            $this->redirect('login');
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

        if (!empty($usuario['contrasena_hash']) && password_verify($contrasena, $usuario['contrasena_hash'])) {
            $errors[] = 'La nueva contraseña debe ser diferente a la contraseña temporal.';
        }

        if (!empty($errors)) {
            $this->view('auth/change_password', [
                'title' => 'Cambiar contraseña',
                'errors' => $errors
            ]);
            return;
        }

        $usuarioModel->updateOwnPassword(
            (int) $usuarioSesion['id_usuario'],
            password_hash($contrasena, PASSWORD_DEFAULT)
        );

        mark_password_change_completed();

        $this->redirect('/');
    }

    public function updateAccountPassword(): void
    {
        require_auth();

        if (!csrf_validate()) {
            die('Solicitud no válida.');
        }

        $contrasena_actual = $_POST['contrasena_actual'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

        $usuarioSesion = current_user();

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByIdWithPasswordHash((int) $usuarioSesion['id_usuario']);
        $usuarioVista = $usuarioModel->findById((int) $usuarioSesion['id_usuario']);

        if (!$usuario || !$usuarioVista) {
            logout_user();
            $this->redirect('login');
        }

        $errors = [];

        if ($contrasena_actual === '') {
            $errors[] = 'Debes ingresar tu contraseña actual para confirmar el cambio.';
        } elseif (!password_verify($contrasena_actual, $usuario['contrasena_hash'])) {
            $errors[] = 'La contraseña actual no es correcta.';
        }

        if (strlen($contrasena) < 8) {
            $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $contrasena)) {
            $errors[] = 'La nueva contraseña debe incluir al menos una letra mayúscula.';
        }

        if (!preg_match('/[a-z]/', $contrasena)) {
            $errors[] = 'La nueva contraseña debe incluir al menos una letra minúscula.';
        }

        if (!preg_match('/[0-9]/', $contrasena)) {
            $errors[] = 'La nueva contraseña debe incluir al menos un número.';
        }

        if ($contrasena !== $confirmar_contrasena) {
            $errors[] = 'La nueva contraseña y la confirmación no coinciden.';
        }

        if (!empty($usuario['contrasena_hash']) && password_verify($contrasena, $usuario['contrasena_hash'])) {
            $errors[] = 'La nueva contraseña debe ser diferente a la contraseña actual.';
        }

        if (!empty($errors)) {
            $this->view('modulos/mi_cuenta', [
                'title' => 'Mi cuenta',
                'usuario' => $usuarioVista,
                'passwordErrors' => $errors,
                'passwordSuccess' => null
            ]);
            return;
        }

        $usuarioModel->updateOwnPassword(
            (int) $usuarioSesion['id_usuario'],
            password_hash($contrasena, PASSWORD_DEFAULT)
        );

        mark_password_change_completed();

        $this->view('modulos/mi_cuenta', [
            'title' => 'Mi cuenta',
            'usuario' => $usuarioVista,
            'passwordErrors' => [],
            'passwordSuccess' => 'La contraseña se actualizó correctamente.'
        ]);
    }
}