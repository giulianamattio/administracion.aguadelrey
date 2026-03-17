<?php
// ============================================================
//  CONTROLADOR/clientes/guardarPassword.php
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$actual  = trim($_POST['password_actual'] ?? '');
$nueva   = trim($_POST['password_nueva']  ?? '');
$nueva2  = trim($_POST['password_nueva2'] ?? '');

if (empty($actual) || empty($nueva) || empty($nueva2)) {
    header('Location: /clientes/cambiarPassword?error=Completá+todos+los+campos.');
    exit;
}
if (strlen($nueva) < 8) {
    header('Location: /clientes/cambiarPassword?error=La+nueva+contraseña+debe+tener+al+menos+8+caracteres.');
    exit;
}
if ($nueva !== $nueva2) {
    header('Location: /clientes/cambiarPassword?error=Las+contraseñas+nuevas+no+coinciden.');
    exit;
}

// Verificar contraseña actual
$stmt = $conexionbd->prepare("SELECT password_hash FROM cliente WHERE id_cliente = :id");
$stmt->execute([':id' => $_SESSION['cliente_id']]);
$cliente = $stmt->fetch();

if (!password_verify($actual, $cliente['password_hash'])) {
    header('Location: /clientes/cambiarPassword?error=La+contraseña+actual+es+incorrecta.');
    exit;
}

// Actualizar con el nuevo hash
$nuevoHash = password_hash($nueva, PASSWORD_BCRYPT);
$stmtUp = $conexionbd->prepare("
    UPDATE cliente SET password_hash = :hash, updated_at = NOW()
    WHERE id_cliente = :id
");
$stmtUp->execute([':hash' => $nuevoHash, ':id' => $_SESSION['cliente_id']]);

header('Location: /clientes/cambiarPassword?ok=1');
exit;
