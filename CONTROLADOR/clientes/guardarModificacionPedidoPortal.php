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
    // Actualizar turno y observaciones
    $stmtUpdate = $conexionbd->prepare("
        UPDATE pedido 
        SET id_turno_deseado      = :turno,
            observaciones_cliente = :observaciones
        WHERE id_pedido  = :id_pedido
          AND id_cliente = :id_cliente
    ");
    $stmtUpdate->execute([
        ':turno'         => $turno,
        ':observaciones' => $observaciones,
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
    if ($cantidadProductoActual >= 1) {
        $stmtDetalle = $conexionbd->prepare("
            INSERT INTO pedido_producto (id_pedido_producto, id_pedido, id_producto, cantidad)
            VALUES (:id_pedido_producto, :id_pedido, :id_producto, :cantidad)
        ");

        for ($i = 1; $i <= $cantidadProductoActual; $i++) {
            $producto = $_POST['producto' . $i] ?? null;
            $cantidad = $_POST['cantidad' . $i] ?? null;

            if (!$producto || $producto == 0 || !$cantidad || $cantidad < 1) continue;

            $stmtMax = $conexionbd->query("SELECT nextval('seq_pedido_producto') AS proximo");
            $idPedidoProducto = $stmtMax->fetch()['proximo'];

            $stmtDetalle->execute([
                ':id_pedido_producto' => $idPedidoProducto,
                ':id_pedido'          => $idPedido,
                ':id_producto'        => $producto,
                ':cantidad'           => $cantidad
            ]);
        }
    }

    header('Location: /clientes/misPedidos?modificado=1');
    exit;

} catch (PDOException $e) {
    error_log("ERROR UPDATE PEDIDO PORTAL: " . $e->getMessage());
    header('Location: /clientes/modificarPedidoPortal/' . $idPedido . '?error=' . urlencode('Ocurrió un error al guardar los cambios.'));
    exit;
}

?>