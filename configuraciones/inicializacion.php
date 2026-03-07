<?php
// ============================================================
//  configuraciones/inicializacion.php
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');

error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// Protección de rutas
$rutaActual = $_SERVER['REQUEST_URI'];
$esLogin = ($rutaActual === '/' || strpos($rutaActual, '/index') !== false);

if (!isset($_SESSION['id_empleado']) && !$esLogin) {
    header('Location: /index');
    exit;
}
