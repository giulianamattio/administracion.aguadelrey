<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$idCliente              = $_SESSION['cliente_id'];
$idPedido               = $_POST['id_pedido']               ?? null;
$turno                  = $_POST['turno']                   ?? null;
$observaciones          = $_POST['observaciones']           ?? '';
$cantidadProductoActual = $_POST['cantidadProductoActual']  ?? 0;

if (!$idPedido || !is_numeric($idPedido)) {
    header('Location: /clientes/misPedidos');
    exit;
}

// Verificar que el pedido pertenece al cliente, está pendiente y es de hoy o futuro
$stmtVerifica = $conexionbd->prepare("
    SELECT COUNT(*) FROM pedido
    WHERE id_pedido  = :id_pedido
      AND id_cliente = :id_cliente
      AND id_estado  = 1
      AND fecha_baja IS NULL
      AND DATE(fecha_pedido) >= CURRENT_DATE
");
$stmtVerifica->execute([':id_pedido' => $idPedido, ':id_cliente' => $idCliente]);
if ($stmtVerifica->fetchColumn() == 0) {
    header('Location: /clientes/misPedidos');
    exit;
}

if (!$turno || !in_array($turno, [1, 2])) {
    header('Location: /clientes/modificarPedidoPortal/' . $idPedido . '?error=' . urlencode('Seleccioná un turno válido.'));
    exit;
}

try {
    // Recolectar productos válidos y calcular total desde la BD
    $productosValidos = [];
    $total            = 0;

    $stmtPrecio = $conexionbd->prepare("
        SELECT precio_unitario FROM producto 
        WHERE id_producto = :id_producto AND fecha_baja IS NULL
    ");

    for ($i = 1; $i <= $cantidadProductoActual; $i++) {
        $producto = $_POST['producto' . $i] ?? null;
        $cantidad = (int)($_POST['cantidad' . $i] ?? 0);

        if (!$producto || $producto == 0 || $cantidad < 1) continue;

        // Consultar precio desde la BD (nunca del POST)
        $stmtPrecio->execute([':id_producto' => $producto]);
        $precio = $stmtPrecio->fetchColumn();

        if ($precio === false) continue; // producto no existe o fue dado de baja

        $total += $precio * $cantidad;
        $productosValidos[] = ['id_producto' => $producto, 'cantidad' => $cantidad];
    }

    if (empty($productosValidos)) {
        header('Location: /clientes/modificarPedidoPortal/' . $idPedido . '?error=' . urlencode('Debés agregar al menos un producto válido.'));
        exit;
    }

    // Actualizar turno, observaciones y total recalculado
    $stmtUpdate = $conexionbd->prepare("
        UPDATE pedido 
        SET id_turno_deseado      = :turno,
            observaciones_cliente = :observaciones,
            total                 = :total
        WHERE id_pedido  = :id_pedido
          AND id_cliente = :id_cliente
    ");
    $stmtUpdate->execute([
        ':turno'         => $turno,
        ':observaciones' => $observaciones,
        ':total'         => $total,
        ':id_pedido'     => $idPedido,
        ':id_cliente'    => $idCliente
    ]);

    // Baja lógica de productos anteriores
    $stmtBaja = $conexionbd->prepare("
        UPDATE pedido_producto 
        SET fecha_baja = NOW()
        WHERE id_pedido = :id_pedido AND fecha_baja IS NULL
    ");
    $stmtBaja->execute([':id_pedido' => $idPedido]);

    // Insertar productos nuevos
    $stmtDetalle = $conexionbd->prepare("
        INSERT INTO pedido_producto (id_pedido_producto, id_pedido, id_producto, cantidad)
        VALUES (:id_pedido_producto, :id_pedido, :id_producto, :cantidad)
    ");

    foreach ($productosValidos as $prod) {
        $stmtMax          = $conexionbd->query("SELECT nextval('seq_pedido_producto') AS proximo");
        $idPedidoProducto = $stmtMax->fetch()['proximo'];

        $stmtDetalle->execute([
            ':id_pedido_producto' => $idPedidoProducto,
            ':id_pedido'          => $idPedido,
            ':id_producto'        => $prod['id_producto'],
            ':cantidad'           => $prod['cantidad']
        ]);
    }

    header('Location: /clientes/misPedidos?modificado=1');
    exit;

} catch (PDOException $e) {
    error_log("ERROR UPDATE PEDIDO PORTAL: " . $e->getMessage());
    header('Location: /clientes/modificarPedidoPortal/' . $idPedido . '?error=' . urlencode('Ocurrió un error al guardar los cambios.'));
    exit;
}
?>