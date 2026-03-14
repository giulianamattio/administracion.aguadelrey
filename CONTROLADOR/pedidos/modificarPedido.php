<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$idPedido              = $_POST['id_pedido']              ?? null;
$fecha                 = $_POST['fecha']                  ?? null;
$cliente               = $_POST['cliente']                ?? null;
$total                 = $_POST['total']                  ?? null;
$observaciones         = $_POST['observaciones']          ?? null;
$cantidadProductoActual = $_POST['cantidadProductoActual'] ?? 0;

if (!$idPedido || !is_numeric($idPedido)) {
    header('Location: /pedidos/listado?error=id_invalido');
    exit;
}

// Verificar duplicado (excluyendo el pedido actual)
$stmtVerifica = $conexionbd->prepare("
    SELECT COUNT(*) as total 
    FROM pedido 
    WHERE id_cliente = :id_cliente 
    AND DATE(fecha_pedido) = :fecha 
    AND id_estado = 1
    AND fecha_baja IS NULL
    AND id_pedido != :id_pedido
");
$stmtVerifica->execute([
    ':id_cliente' => $cliente,
    ':fecha'      => $fecha,
    ':id_pedido'  => $idPedido
]);
$rowVerifica = $stmtVerifica->fetch();

if ($rowVerifica['total'] > 0) {
    header('Location: /pedidos/modificarPedido/' . $idPedido . '?error=pedido_duplicado');
    exit;
}

try {
    // Actualizar cabecera del pedido
    $stmtUpdate = $conexionbd->prepare("
        UPDATE pedido 
        SET id_cliente            = :id_cliente,
            fecha_pedido          = :fecha_pedido,
            total                 = :total,
            observaciones_internas = :observaciones
        WHERE id_pedido = :id_pedido AND fecha_baja IS NULL
    ");
    $stmtUpdate->execute([
        ':id_cliente'    => $cliente,
        ':fecha_pedido'  => $fecha,
        ':total'         => $total,
        ':observaciones' => $observaciones,
        ':id_pedido'     => $idPedido
    ]);

    // Baja lógica de todos los productos anteriores
    $stmtBajaProductos = $conexionbd->prepare("
        UPDATE pedido_producto 
        SET fecha_baja = NOW() 
        WHERE id_pedido = :id_pedido AND fecha_baja IS NULL
    ");
    $stmtBajaProductos->execute([':id_pedido' => $idPedido]);

    // Insertar los productos nuevos
    if ($cantidadProductoActual >= 1) {
        $stmtInsertDetalle = $conexionbd->prepare("
            INSERT INTO pedido_producto (id_pedido_producto, id_pedido, id_producto, cantidad) 
            VALUES (:id_pedido_producto, :id_pedido, :id_producto, :cantidad)
        ");

        for ($i = 1; $i <= $cantidadProductoActual; $i++) {
            $producto = $_POST['producto' . $i] ?? null;
            $cantidad = $_POST['cantidad' . $i] ?? null;

            if (!$producto || $producto == 0 || !$cantidad || $cantidad < 1) continue;

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

    header('Location: /pedidos/listado?exito=modificado');
    exit;

} catch (PDOException $e) {

    /*die("ERROR: " . $e->getMessage());*/
   error_log("ERROR UPDATE PEDIDO: " . $e->getMessage());
    header('Location: /pedidos/modificarPedido/' . $idPedido . '?error=1');
    exit;
}
?>