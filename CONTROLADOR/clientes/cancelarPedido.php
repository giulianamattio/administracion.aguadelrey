<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$idPedido  = $_GET['id'] ?? null;
$idCliente = $_SESSION['cliente_id'];

if (!$idPedido || !is_numeric($idPedido)) {
    header('Location: /clientes/misPedidos');
    exit;
}

// Verificar que el pedido pertenece al cliente logueado y está pendiente
$stmtVerifica = $conexionbd->prepare("
    SELECT COUNT(*) FROM pedido
    WHERE id_pedido  = :id_pedido
      AND id_cliente = :id_cliente
      AND id_estado  = 1
      AND fecha_baja IS NULL
");
$stmtVerifica->execute([':id_pedido' => $idPedido, ':id_cliente' => $idCliente]);

if ($stmtVerifica->fetchColumn() == 0) {
    header('Location: /clientes/misPedidos');
    exit;
}

// Cambiar estado a cancelado (id_estado = 4)
$stmt = $conexionbd->prepare("
    UPDATE pedido SET id_estado = 4
    WHERE id_pedido = :id_pedido AND id_cliente = :id_cliente
");
$stmt->execute([':id_pedido' => $idPedido, ':id_cliente' => $idCliente]);

header('Location: /clientes/misPedidos?cancelado=1');
exit;
?>