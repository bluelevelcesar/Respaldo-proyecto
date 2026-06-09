<?php
// html/logout.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include __DIR__ . '/config/conexion.php';
include __DIR__ . '/controllers/funcion_log.php';

// Registrar cierre sesión
if (
    isset($_SESSION['id_usuario']) &&
    isset($_SESSION['usuario'])
) {

    $log = new SistemaLog($conexion);

    $log->cerrarSesion(
        $_SESSION['id_usuario'],
        $_SESSION['usuario']
    );
}

// Limpiar sesión
$_SESSION = [];

session_destroy();

header('Location: login.php');
exit;
?>                          