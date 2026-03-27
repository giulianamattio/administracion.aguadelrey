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
    header('Location: /clientes/nuevoPedido?error=' . urlencode('Ya tenés un pedido pendiente para esa fecha.'));
    exit;
}

try {
    // Obtener próximo ID de pedido
    $stmt     = $conexionbd->query("SELECT COALESCE(MAX(id_pedido), 0) + 1 AS proximo FROM pedido");
    $idPedido = $stmt->fetch()['proximo'] ?? 1;

    // Insertar pedido (total en 0, lo carga el admin después)
    $stmtPedido = $conexionbd->prepare("
        INSERT INTO pedido (id_pedido, id_cliente, fecha_pedido, total, id_origen_pedido, id_estado, observaciones_cliente, id_turno_deseado)
        VALUES (:id_pedido, :id_cliente, :fecha_pedido, 0, :id_origen_pedido, :id_estado, :observaciones, :id_turno)
    ");
    $stmtPedido->execute([
        ':id_pedido'        => $idPedido,
        ':id_cliente'       => $idCliente,
        ':fecha_pedido'     => $fecha,
        ':id_origen_pedido' => $idOrigenPedido,
        ':id_estado'        => $idEstado,
        ':observaciones'    => $observaciones,
        ':id_turno'         => $idTurno
    ]);

    // Insertar productos
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

    header('Location: /clientes/misPedidos?ok=1');
    exit;

} catch (PDOException $e) {
    error_log("ERROR INSERT PEDIDO PORTAL: " . $e->getMessage());
    header('Location: /clientes/nuevoPedido?error=' . urlencode('Ocurrió un error al guardar el pedido. Intentá de nuevo.'));
    exit;
}