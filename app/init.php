<?php

require_once __DIR__ . '/../config/config.php';

require_once __DIR__ . '/helpers/urlhelper.php';
require_once __DIR__ . '/helpers/securityhelper.php';
require_once __DIR__ . '/helpers/authhelper.php';

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/controller.php';
require_once __DIR__ . '/core/router.php';

// Sesión segura
session_name('soft_rh_v2_session');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autocarga básica de clases
spl_autoload_register(function ($class) {
    $class_file = strtolower($class) . '.php';

    $paths = [
        __DIR__ . '/controllers/' . $class_file,
        __DIR__ . '/models/' . $class_file,
        __DIR__ . '/core/' . $class_file
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});