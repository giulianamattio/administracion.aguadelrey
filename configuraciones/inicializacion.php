<?php
// ============================================================
//  configuraciones/inicializacion.php
// ============================================================

// ob_start() DEBE ser la primera instrucción absoluta.
// Decisión: el output buffering captura cualquier output accidental
// (BOM, espacios, echo en archivos incluidos) antes de que llegue
// al cliente, evitando el "headers already sent".
// Si ob_start() ya fue llamado (ej: doble include), no lo llamamos de nuevo.
if (ob_get_level() === 0) {
    ob_start();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');

error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// Protección de rutas
$rutaActual = $_SERVER['REQUEST_URI'];
$esLogin    = ($rutaActual === '/' || strpos($rutaActual, '/index') !== false);

if (!isset($_SESSION['id_empleado']) && !$esLogin) {
    header('Location: /index');
    exit;
}
