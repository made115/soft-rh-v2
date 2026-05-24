<?php

class ContratoController extends Controller
{
    public function index(): void
    {
        require_role(['administrador']);

        $this->view('contratos/index', [
            'title' => 'Gestión de contratos'
        ]);
    }
}