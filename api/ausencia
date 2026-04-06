<?php
// ============================================================
//  api/ausencia.php
//  POST /api/ausencia
//  Header: Authorization: Bearer <token>
//  Body JSON: { id_pedido }
//  Response: { "ok": true }
//
//  Registra que el repartidor visitó el domicilio pero el cliente
//  no estaba presente. El pedido queda en estado Pendiente (1)
//  y se registra fecha_ausencia con la fecha/hora del intento.
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Método no permitido', 405);
}

$payload = apiAutenticar();

$body = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    apiError('JSON inválido', 400);
}

$id_pedido = isset($body['id_pedido']) ? intval($body['id_pedido']) : 0;
if ($id_pedido <= 0) {
    apiError('id_pedido requerido', 400);
}

// Verificar que el pedido existe y está en estado procesable
$stmtCheck = $conexionbd->prepare("
    SELECT id_pedido FROM pedido
    WHERE id_pedido = :id_pedido
      AND id_estado IN (1, 2)
      AND fecha_baja IS NULL
");
$stmtCheck->execute([':id_pedido' => $id_pedido]);
if (!$stmtCheck->fetch()) {
    apiError('Pedido no encontrado o ya fue procesado', 404);
}

// Registrar fecha_ausencia — el pedido NO cambia de estado, queda Pendiente
$stmt = $conexionbd->prepare("
    UPDATE pedido
    SET fecha_ausencia = NOW()
    WHERE id_pedido = :id_pedido
");
$stmt->execute([':id_pedido' => $id_pedido]);

apiOk(['mensaje' => 'Ausencia registrada correctamente']);
