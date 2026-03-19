<?php
// ============================================================
//  api/login.php
//  POST /api/login
//  Body: { "email": "...", "password": "..." }
//  Response: { "ok": true, "token": "...", "empleado": {...} }
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Método no permitido', 405);
}

// Leer body JSON (Android envía JSON, no form-data)
$body = json_decode(file_get_contents('php://input'), true);

$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

if (empty($email) || empty($password)) {
    apiError('Email y contraseña son requeridos');
}

// Buscar empleado activo
$stmt = $conexionbd->prepare("
    SELECT id_empleado, nombre, apellido, email, password_hash, id_rol, activo
    FROM usuario_empleado
    WHERE email = :email AND activo = TRUE
");
$stmt->execute([':email' => $email]);
$empleado = $stmt->fetch();

if (!$empleado) {
    apiError('Credenciales incorrectas', 401);
}

if (!password_verify($password, $empleado['password_hash'])) {
    apiError('Credenciales incorrectas', 401);
}

// Generar JWT con datos del empleado
$token = JWT::generar([
    'id_empleado' => $empleado['id_empleado'],
    'nombre'      => $empleado['nombre'] . ' ' . $empleado['apellido'],
    'email'       => $empleado['email'],
    'id_rol'      => $empleado['id_rol'],
]);

apiOk([
    'token' => $token,
    'empleado' => [
        'id'       => $empleado['id_empleado'],
        'nombre'   => $empleado['nombre'],
        'apellido' => $empleado['apellido'],
        'email'    => $empleado['email'],
        'id_rol'   => $empleado['id_rol'],
    ]
]);
