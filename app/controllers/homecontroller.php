<?php

class HomeController extends Controller
{
    public function index(): void
    {
        require_auth();

        $this->view('home/index', [
            'title' => 'Inicio'
        ]);
    }
}