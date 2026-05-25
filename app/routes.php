<?php

$router->get('/', 'HomeController@index');

$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@authenticate');
$router->post('/logout', 'AuthController@logout');


$router->get('/usuarios', 'UsuarioController@index');
$router->get('/empleados', 'EmpleadoController@index');
$router->get('/usuarios/crear', 'UsuarioController@create');
$router->post('/usuarios/guardar', 'UsuarioController@store');
$router->get('/usuarios/editar', 'UsuarioController@edit');
$router->post('/usuarios/actualizar', 'UsuarioController@update');
$router->post('/usuarios/cambiar-estado', 'UsuarioController@changeStatus');
$router->get('/usuarios/contrasena', 'UsuarioController@password');
$router->post('/usuarios/actualizar-contrasena', 'UsuarioController@updatePassword');
$router->get('/sin-permiso', 'ErrorController@forbidden');

$router->get('/actas', 'ModuloController@actas');
$router->get('/vacaciones', 'ModuloController@vacaciones');
$router->get('/seguimientos', 'ModuloController@seguimientos');
$router->get('/mi-cuenta', 'ModuloController@miCuenta');
$router->get('/cambiar-contrasena', 'AuthController@changePassword');
$router->post('/actualizar-mi-contrasena', 'AuthController@updateOwnPassword');

$router->get('/empleados', 'EmpleadoController@index');
$router->get('/empleados/crear', 'EmpleadoController@create');
$router->post('/empleados/guardar', 'EmpleadoController@store');
$router->get('/empleados/detalle', 'empleadocontroller@detalle');
$router->get('/empleados/editar', 'empleadocontroller@edit');
$router->post('/empleados/actualizar', 'empleadocontroller@update');
$router->get('/empleados/estado', 'empleadocontroller@estado');
$router->post('/empleados/actualizar-estado', 'empleadocontroller@actualizarEstado');

$router->get('/contratos', 'ContratoController@index');
$router->get('/contratos/crear', 'ContratoController@create');
$router->post('/contratos/guardar', 'ContratoController@store');
$router->get('/contratos/editar', 'ContratoController@edit');
$router->post('/contratos/actualizar', 'ContratoController@update');
$router->get('/contratos/historial', 'ContratoController@historial');
$router->get('/contratos/movimientos', 'ContratoController@movimientos');
$router->get('/contratos/pdf', 'ContratoController@pdf');
$router->post('/mi-cuenta/actualizar-contrasena', 'AuthController@updateAccountPassword');
