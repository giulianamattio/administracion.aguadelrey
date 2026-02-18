<?php
// ============================================================
//  configuraciones/inicializacion.php
// ============================================================
session_start();
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// $conexionbd (PDO) queda disponible globalmente para todos
// los archivos que hagan require de inicializacion.php

session_start();
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// Si no hay sesión activa y no es la página de login, redirigir
$rutaActual = $_SERVER['REQUEST_URI'];
$esLogin = (strpos($rutaActual, '/index') !== false || $rutaActual === '/');

if (!isset($_SESSION['id_empleado']) && !$esLogin) {
    header('Location: /index');
    exit;
}