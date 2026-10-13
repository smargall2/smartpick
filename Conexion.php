<?php

// conexion.php

define('DB_HOST', 'localhost');
define('DB_USUARIO', 'root');
define('DB_PASSWORD', '');
define('DB_NOMBRE', 'smartpick');

$conexion = mysqli_connect(
    DB_HOST,
    DB_USUARIO,
    DB_PASSWORD,
    DB_NOMBRE
);

if (!$conexion) {
    die(
        'Error de conexión: ' .
        mysqli_connect_error()
    );
}

mysqli_set_charset(
    $conexion,
    'utf8mb4'
);
