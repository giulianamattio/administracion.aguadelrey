<?php
// ============================================================
//  configuraciones/conexionBD.php
//  Conexión PDO a PostgreSQL — Agua del Rey
//  Las credenciales se leen de variables de entorno de Render.
//  Nunca hardcodear usuarios/contraseñas en el código.
// ============================================================

$host     = getenv('DB_HOST');
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME');
$user     = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conexionbd = new PDO($dsn, $user, $password, $opciones);
} catch (PDOException $e) {
    error_log('Error de conexión BD: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}