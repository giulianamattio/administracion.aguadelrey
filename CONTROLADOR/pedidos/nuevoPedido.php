<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$fecha                  = $_POST["fecha"];
$cliente                = $_POST["cliente"];
$total                  = $_POST["total"];
$observaciones          = $_POST["observaciones"];
$cantidadProductoActual = $_POST["cantidadProductoActual"];
$idTurnoDeseado         = $_POST["id_turno_deseado"];
$idOrigenPedido         = 1; // adm
$idEstado               = 1; // Pendiente

// Verificar si ya existe pedido pendiente para ese cliente y fecha
$stmtVerifica = $conexionbd->prepare("
    SELECT COUNT(*) as total 
    FROM pedido 
    WHERE id_cliente = :id_cliente 
    AND DATE(fecha_pedido) = :fecha 
    AND id_estado = 1
    AND fecha_baja IS NULL
");
$stmtVerifica->execute([
    ':id_cliente' => $cliente,
    ':fecha'      => $fecha
]);
$rowVerifica = $stmtVerifica->fetch();

if ($rowVerifica['total'] > 0) {
    header('Location: /pedidos/nuevoPedido?error=pedido_duplicado');
    exit;
}

try {

    $stmt     = $conexionbd->query("SELECT COALESCE(MAX(id_pedido), 0) + 1 AS proximo FROM pedido");
    $row      = $stmt->fetch();
    $idPedido = $row['proximo'] ?? 1;

    $stmtInsertPedido = $conexionbd->prepare("
        INSERT INTO pedido (id_pedido, id_cliente, fecha_pedido, total, id_origen_pedido, id_estado, observaciones_internas, id_turno_deseado) 
        VALUES (:id_pedido, :id_cliente, :fecha_pedido, :total, :id_origen_pedido, :id_estado, :observaciones_internas, :id_turno_deseado) 
        RETURNING id_pedido
    ");
    $stmtInsertPedido->execute([
        ':id_pedido'              => $idPedido,
        ':id_cliente'             => $cliente,
        ':fecha_pedido'           => $fecha,
        ':total'                  => $total,
        ':id_origen_pedido'       => $idOrigenPedido,
        ':id_estado'              => $idEstado,
        ':observaciones_internas' => $observaciones,
        ':id_turno_deseado'       => $idTurnoDeseado,
    ]);

    if ($cantidadProductoActual >= 1) {
        $stmtInsertDetalle = $conexionbd->prepare("
            INSERT INTO pedido_producto (id_pedido_producto, id_pedido, id_producto, cantidad) 
            VALUES (:id_pedido_producto, :id_pedido, :id_producto, :cantidad)
        ");

        for ($i = 1; $i <= $cantidadProductoActual; $i++) {
            $producto = $_POST['producto' . $i];
            $cantidad = $_POST['cantidad' . $i];

            $stmtMax = $conexionbd->query("SELECT nextval('seq_pedido_producto') AS proximo");
            $rowMax  = $stmtMax->fetch();
            $idPedidoProducto = $rowMax['proximo'];

            $stmtInsertDetalle->execute([
                ':id_pedido_producto' => $idPedidoProducto,
                ':id_pedido'          => $idPedido,
                ':id_producto'        => $producto,
                ':cantidad'           => $cantidad
            ]);
        }
    }

    header('Location: /pedidos/nuevoPedido?exito=1');
    exit;

} catch (PDOException $e) {
    error_log("ERROR INSERT PEDIDO: " . $e->getMessage());
    header('Location: /pedidos/nuevoPedido?error=1');
    exit;
}
?>