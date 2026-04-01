<?php
// ============================================================
//  CONTROLADOR/clientes/guardarNuevaPassword.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$token    = $_POST['token']            ?? '';
$password = $_POST['password']         ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

if (empty($token) || empty($password) || $password !== $confirm) {
    header('Location: /clientes/resetPassword?token=' . urlencode($token) . '&error=' . urlencode('Datos inválidos.'));
    exit;
}

if (strlen($password) < 8) {
    header('Location: /clientes/resetPassword?token=' . urlencode($token) . '&error=' . urlencode('La contraseña debe tener al menos 8 caracteres.'));
    exit;
}

// Verificar token
$stmt = $conexionbd->prepare("
    SELECT id_cliente FROM password_reset_tokens
    WHERE token     = :token
      AND usado     = FALSE
      AND expira_en > NOW()
");
$stmt->execute([':token' => $token]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: /clientes/resetPassword?token=' . urlencode($token) . '&error=' . urlencode('El link expiró. Solicitá uno nuevo.'));
    exit;
}

// Actualizar contraseña
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmtUpdate = $conexionbd->prepare("
    UPDATE cliente SET password = :password WHERE id_cliente = :id_cliente
");
$stmtUpdate->execute([':password' => $hash, ':id_cliente' => $row['id_cliente']]);

// Marcar token como usado
$stmtUsado = $conexionbd->prepare("
    UPDATE password_reset_tokens SET usado = TRUE WHERE token = :token
");
$stmtUsado->execute([':token' => $token]);

header('Location: /clientes/resetPassword?token=' . urlencode($token) . '&ok=1');
exit;

?>