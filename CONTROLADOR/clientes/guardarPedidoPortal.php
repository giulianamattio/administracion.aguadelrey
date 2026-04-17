<?php
// ============================================================
//  CONTROLADOR/clientes/guardarPedidoPortal.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
 
$idCliente              = $_SESSION['cliente_id'];
$fecha                  = $_POST['fecha']                  ?? date('Y-m-d');
$observaciones          = $_POST['observaciones']          ?? '';
$cantidadProductoActual = $_POST['cantidadProductoActual'] ?? 0;
$idOrigenPedido         = 2; // portal cliente
$idEstado               = 1; // pendiente
$idTurno                = $_POST['turno'] ?? null;
 
if (!$idTurno || !in_array($idTurno, [1, 2])) {
    header('Location: /clientes/nuevoPedidoPortal?error=' . urlencode('Seleccioná un turno válido.'));
    exit;
}
 
// Verificar pedido duplicado (mismo cliente, misma fecha, pendiente)
$stmtVerifica = $conexionbd->prepare("
    SELECT COUNT(*) 
    FROM pedido 
    WHERE id_cliente = :id_cliente
      AND DATE(fecha_pedido) = :fecha
      AND id_estado = 1
      AND fecha_baja IS NULL
");
$stmtVerifica->execute([':id_cliente' => $idCliente, ':fecha' => $fecha]);
if ($stmtVerifica->fetchColumn() > 0) {
    header('Location: /clientes/nuevoPedidoPortal?error=' . urlencode('Ya tenés un pedido pendiente para esa fecha.'));
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
        header('Location: /clientes/nuevoPedidoPortal?error=' . urlencode('Debés agregar al menos un producto válido.'));
        exit;
    }
 
    // Obtener próximo ID de pedido
    $stmt     = $conexionbd->query("SELECT COALESCE(MAX(id_pedido), 0) + 1 AS proximo FROM pedido");
    $idPedido = $stmt->fetch()['proximo'] ?? 1;
 
    // Insertar pedido con total calculado
    $stmtPedido = $conexionbd->prepare("
        INSERT INTO pedido (id_pedido, id_cliente, fecha_pedido, total, id_origen_pedido, id_estado, observaciones_cliente, id_turno_deseado)
        VALUES (:id_pedido, :id_cliente, :fecha_pedido, :total, :id_origen_pedido, :id_estado, :observaciones, :id_turno)
    ");
    $stmtPedido->execute([
        ':id_pedido'        => $idPedido,
        ':id_cliente'       => $idCliente,
        ':fecha_pedido'     => $fecha,
        ':total'            => $total,
        ':id_origen_pedido' => $idOrigenPedido,
        ':id_estado'        => $idEstado,
        ':observaciones'    => $observaciones,
        ':id_turno'         => $idTurno
    ]);
 
    // Insertar productos
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
 
    header('Location: /clientes/misPedidos?ok=1');
    exit;
 
} catch (PDOException $e) {
    error_log("ERROR INSERT PEDIDO PORTAL: " . $e->getMessage());
    header('Location: /clientes/nuevoPedidoPortal?error=' . urlencode('Ocurrió un error al guardar el pedido. Intentá de nuevo.'));
    exit;
}