<?php

class ErrorController extends Controller
{
    public function forbidden(): void
    {
        require_auth();

        http_response_code(403);

        $this->view('errors/403', [
            'title' => 'Acceso denegado'
        ]);
    }
}