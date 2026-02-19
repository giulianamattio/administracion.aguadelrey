<?php
// ob_start() PRIMERO — activa el buffer de output.
// Esto evita "headers already sent" sin importar qué pase después.
ob_start();

// session_status() evita llamar session_start() dos veces
// si el archivo se incluye más de una vez en el mismo request.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);

// require_once en lugar de require — misma protección contra doble inclusión
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// Protección de rutas: si no hay sesión activa y no es la página de login,
// redirigir al login. Esto reemplaza el redirect que estaba en menu.php.
$rutaActual = $_SERVER['REQUEST_URI'];
$esLogin = ($rutaActual === '/' || strpos($rutaActual, '/index') !== false);

if (!isset($_SESSION['id_empleado']) && !$esLogin) {
    header('Location: /index');
    exit;
}