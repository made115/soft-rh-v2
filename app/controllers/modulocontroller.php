<?php

class ModuloController extends Controller
{
    public function actas(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $this->view('modulos/working', [
            'title' => 'Actas administrativas',
            'icon' => '📋',
            'heading' => 'Actas administrativas',
            'description' => 'Aquí se desarrollará la gestión de actas administrativas del personal.',
            'status' => 'Pendiente para etapa posterior'
        ]);
    }

    public function vacaciones(): void
    {
        require_role(['administrador', 'recursos_humanos']);

        $this->view('modulos/working', [
            'title' => 'Vacaciones',
            'icon' => '🌴',
            'heading' => 'Vacaciones',
            'description' => 'Aquí se desarrollará la gestión de vacaciones y parámetros de días correspondientes.',
            'status' => 'Pendiente para etapa posterior'
        ]);
    }

    public function seguimientos(): void
    {
        require_role(['administrador', 'psicologia']);

        $this->view('modulos/working', [
            'title' => 'Seguimiento psicológico',
            'icon' => '🧠',
            'heading' => 'Seguimiento psicológico',
            'description' => 'Aquí se desarrollará el módulo de seguimiento psicológico del personal.',
            'status' => 'Pendiente para etapa posterior'
        ]);
    }

    public function miCuenta(): void
    {
        require_auth();

        $this->view('modulos/mi_cuenta', [
            'title' => 'Mi cuenta'
        ]);
    }
}