<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$idPedido = $_GET['id'] ?? null;

if (!$idPedido || !is_numeric($idPedido)) {
    header('Location: /pedidos/listado?error=id_invalido');
    exit;
}

$stmt = $conexionbd->prepare("
    UPDATE pedido 
    SET id_estado = 3 
    WHERE id_pedido = :id_pedido AND fecha_baja IS NULL
");
$stmt->execute([':id_pedido' => $idPedido]);

header('Location: /pedidos/listado?exito=finalizado');
exit;
?>