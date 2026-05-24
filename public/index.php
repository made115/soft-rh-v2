<?php

require_once __DIR__ . '/../app/init.php';

$router = new Router();

require_once __DIR__ . '/../app/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
