<?php
// ============================================================
//  configuraciones/inicializacion.php
// ============================================================
session_start();
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// $conexionbd (PDO) queda disponible globalmente para todos
// los archivos que hagan require de inicializacion.php