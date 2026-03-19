<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$idPedido = $_GET['id'] ?? null;

if (!$idPedido || !is_numeric($idPedido)) {
    header('Location: /pedidos/listado?error=id_invalido');
    exit;
}

// Baja lógica en pedido_producto
$stmtDetalle = $conexionbd->prepare("
    UPDATE pedido_producto 
    SET fecha_baja = NOW() 
    WHERE id_pedido = :id_pedido AND fecha_baja IS NULL
");
$stmtDetalle->execute([':id_pedido' => $idPedido]);

// Baja lógica en pedido
$stmtPedido = $conexionbd->prepare("
    UPDATE pedido 
    SET fecha_baja = NOW(), id_estado = 4
    WHERE id_pedido = :id_pedido AND fecha_baja IS NULL
");
$stmtPedido->execute([':id_pedido' => $idPedido]);

header('Location: /pedidos/listado?exito=eliminado');
exit;

?>