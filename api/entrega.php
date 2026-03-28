<?php
// ============================================================
//  api/entrega.php
//  POST /api/entrega
//  Header: Authorization: Bearer <token>
//  Body JSON: { id_pedido, productos:[{id_detalle, cantidad_entregada}],
//               monto_cobrado, dni_receptor, observaciones }
//  Response: { "ok": true, "total_final": X }
// ============================================================
ob_start();
ini_set('html_errors', '0');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Método no permitido', 405);
}

$payload = apiAutenticar();

// Leer y validar body JSON
$body = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    apiError('JSON inválido', 400);
}

$id_pedido     = isset($body['id_pedido'])     ? intval($body['id_pedido'])       : 0;
$productos     = isset($body['productos'])     ? $body['productos']               : [];
$monto_cobrado = isset($body['monto_cobrado']) ? floatval($body['monto_cobrado']) : 0.0;
$dni_receptor  = isset($body['dni_receptor'])  ? trim($body['dni_receptor'])      : '';
$observaciones = isset($body['observaciones']) ? trim($body['observaciones'])     : '';

if ($id_pedido <= 0) {
    apiError('id_pedido requerido', 400);
}
if (empty($productos)) {
    apiError('Se requiere al menos un producto', 400);
}

// Transacción: tocamos pedido + detalle_pedido — si algo falla, ROLLBACK.
// Principio ACID: nunca quedamos con datos parciales.
try {
    $conexionbd->beginTransaction();

    // Verificar que el pedido existe y está en estado procesable (lock de fila)
    $stmtCheck = $conexionbd->prepare("
        SELECT id_pedido FROM pedido
        WHERE id_pedido = :id_pedido
          AND id_estado IN (1, 2)
        FOR UPDATE
    ");
    $stmtCheck->execute([':id_pedido' => $id_pedido]);
    if (!$stmtCheck->fetch()) {
        $conexionbd->rollBack();
        apiError('Pedido no encontrado o ya fue procesado', 409);
    }

    // Actualizar cantidades entregadas y calcular total real
    $total_real = 0.0;
    $stmtPrecio = $conexionbd->prepare("
        SELECT precio_unitario FROM detalle_pedido
        WHERE id_detalle = :id_detalle AND id_pedido = :id_pedido
    ");
    $stmtUpdate = $conexionbd->prepare("
        UPDATE detalle_pedido
        SET cantidad = :cantidad
        WHERE id_detalle = :id_detalle AND id_pedido = :id_pedido
    ");

    foreach ($productos as $prod) {
        $id_detalle         = intval($prod['id_detalle']);
        $cantidad_entregada = intval($prod['cantidad_entregada']);

        // Productos extra (id_detalle negativo) → INSERT nuevo detalle
        if ($id_detalle < 0 && isset($prod['id_producto'])) {
            $id_producto = intval($prod['id_producto']);
            $stmtInsert  = $conexionbd->prepare("
                INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario)
                SELECT :id_pedido, :id_producto, :cantidad, precio_unitario
                FROM producto WHERE id_producto = :id_producto2
            ");
            $stmtInsert->execute([
                ':id_pedido'   => $id_pedido,
                ':id_producto' => $id_producto,
                ':cantidad'    => $cantidad_entregada,
                ':id_producto2'=> $id_producto,
            ]);
            // Obtener precio para sumar al total
            $stmtPrecioExtra = $conexionbd->prepare(
                "SELECT precio_unitario FROM producto WHERE id_producto = :id"
            );
            $stmtPrecioExtra->execute([':id' => $id_producto]);
            $rowExtra = $stmtPrecioExtra->fetch();
            if ($rowExtra) {
                $total_real += $cantidad_entregada * floatval($rowExtra['precio_unitario']);
            }
            continue;
        }

        if ($id_detalle <= 0 || $cantidad_entregada < 0) continue;

        $stmtPrecio->execute([':id_detalle' => $id_detalle, ':id_pedido' => $id_pedido]);
        $row = $stmtPrecio->fetch();
        if ($row) {
            $total_real += $cantidad_entregada * floatval($row['precio_unitario']);
        }

        $stmtUpdate->execute([
            ':cantidad'   => $cantidad_entregada,
            ':id_detalle' => $id_detalle,
            ':id_pedido'  => $id_pedido,
        ]);
    }

    // Actualizar cabecera del pedido
    $obs_auditoria = "DNI receptor: {$dni_receptor} | Cobrado: \${$monto_cobrado}";
    if (!empty($observaciones)) {
        $obs_auditoria .= " | {$observaciones}";
    }

    $stmtPedido = $conexionbd->prepare("
        UPDATE pedido
        SET id_estado              = 3,
            fecha_entrega_real     = NOW(),
            total                  = :total,
            observaciones_internas = :observaciones
        WHERE id_pedido = :id_pedido
    ");
    $stmtPedido->execute([
        ':total'        => $total_real,
        ':observaciones'=> $obs_auditoria,
        ':id_pedido'    => $id_pedido,
    ]);

    $conexionbd->commit();

    apiOk([
        'id_pedido'   => $id_pedido,
        'total_final' => $total_real,
        'mensaje'     => 'Entrega registrada correctamente',
    ]);

} catch (PDOException $e) {
    $conexionbd->rollBack();
    apiError('Error de base de datos: ' . $e->getMessage(), 500);
}
