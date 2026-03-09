<?php
// ============================================================
//  CONTROLADOR/clientes/procesarLogin.php
//  Autentica al cliente contra la tabla cliente
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
session_start();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /clientes/login');
    exit;
}

$email = trim($_POST['email']    ?? '');
$pass  = trim($_POST['password'] ?? '');

if (empty($email) || empty($pass)) {
    header('Location: /clientes/login?error=Completá+todos+los+campos.');
    exit;
}

// Buscar cliente activo por email
$stmt = $conexionbd->prepare("
    SELECT id_cliente, nombre, apellido, email, password_hash, estado
    FROM cliente
    WHERE email = :email
");
$stmt->execute([':email' => $email]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: /clientes/login?error=Email+o+contraseña+incorrectos.');
    exit;
}

if ($cliente['estado'] !== 'activo') {
    header('Location: /clientes/login?error=Tu+cuenta+aún+no+fue+aprobada+por+el+administrador.');
    exit;
}

if (!password_verify($pass, $cliente['password_hash'])) {
    header('Location: /clientes/login?error=Email+o+contraseña+incorrectos.');
    exit;
}

// Login exitoso — guardar sesión
$_SESSION['cliente_id']       = $cliente['id_cliente'];
$_SESSION['cliente_nombre']   = $cliente['nombre'];
$_SESSION['cliente_apellido'] = $cliente['apellido'];
$_SESSION['cliente_email']    = $cliente['email'];

header('Location: /clientes/home');
exit;
