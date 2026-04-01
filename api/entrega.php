<?php
// ============================================================
//  api/entrega.php
//  POST /api/entrega
//  Header: Authorization: Bearer <token>
//  Body JSON: { id_pedido, productos:[{id_detalle, id_producto, cantidad_entregada}],
//               monto_cobrado, dni_receptor, observaciones }
//  Response: { "ok": true, "total_final": X }
//
//  Decisión de diseño:
//  Los ítems del pedido viven en pedido_producto (id_detalle = id_pedido_producto).
//  Al confirmar entrega actualizamos cantidad en pedido_producto y cabecera en pedido.
//  Los productos extra (id_detalle negativo) se insertan en pedido_producto.
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Método no permitido', 405);
}

$payload = apiAutenticar();

$body = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    apiError('JSON inválido', 400);
}

$id_pedido     = isset($body['id_pedido'])      ? intval($body['id_pedido'])       : 0;
$productos     = isset($body['productos'])      ? $body['productos']               : [];
$monto_cobrado = isset($body['monto_cobrado'])  ? floatval($body['monto_cobrado']) : 0.0;
$dni_receptor  = isset($body['dni_receptor'])   ? trim($body['dni_receptor'])      : '';
$bidones_vacios= isset($body['bidones_vacios']) ? intval($body['bidones_vacios'])  : 0;
$observaciones = isset($body['observaciones'])  ? trim($body['observaciones'])     : '';

if ($id_pedido <= 0)   apiError('id_pedido requerido', 400);
if (empty($productos)) apiError('Se requiere al menos un producto', 400);

try {
    $conexionbd->beginTransaction();

    // Verificar que el pedido existe y está en estado procesable
    $stmtCheck = $conexionbd->prepare("
        SELECT id_pedido FROM pedido
        WHERE id_pedido = :id_pedido
          AND id_estado IN (1, 2)
          AND fecha_baja IS NULL
        FOR UPDATE
    ");
    $stmtCheck->execute([':id_pedido' => $id_pedido]);
    if (!$stmtCheck->fetch()) {
        $conexionbd->rollBack();
        apiError('Pedido no encontrado o ya fue procesado', 409);
    }

    $total_real = 0.0;

    // Precio unitario de un ítem existente en pedido_producto
    $stmtPrecio = $conexionbd->prepare("
        SELECT pr.precio_unitario
        FROM pedido_producto pp
        INNER JOIN producto pr ON pr.id_producto = pp.id_producto
        WHERE pp.id_pedido_producto = :id_detalle
          AND pp.id_pedido          = :id_pedido
    ");

    // Actualizar cantidad entregada en pedido_producto
    $stmtUpdate = $conexionbd->prepare("
        UPDATE pedido_producto
        SET cantidad = :cantidad
        WHERE id_pedido_producto = :id_detalle
          AND id_pedido          = :id_pedido
    ");

    foreach ($productos as $prod) {
        $id_detalle         = intval($prod['id_detalle']);
        $cantidad_entregada = intval($prod['cantidad_entregada']);
        $id_producto        = isset($prod['id_producto']) ? intval($prod['id_producto']) : 0;

        // Producto extra (id_detalle negativo) → INSERT en pedido_producto
        if ($id_detalle < 0 && $id_producto > 0) {
            $stmtPrecioExtra = $conexionbd->prepare("
                SELECT precio_unitario FROM producto WHERE id_producto = :id
            ");
            $stmtPrecioExtra->execute([':id' => $id_producto]);
            $rowExtra = $stmtPrecioExtra->fetch();
            $precioExtra = $rowExtra ? floatval($rowExtra['precio_unitario']) : 0.0;

            $stmtNextval = $conexionbd->query("SELECT nextval('seq_pedido_producto') AS proximo");
            $rowNextval  = $stmtNextval->fetch();
            $nuevoId     = $rowNextval['proximo'];

            $stmtInsert = $conexionbd->prepare("
                INSERT INTO pedido_producto (id_pedido_producto, id_pedido, id_producto, cantidad)
                VALUES (:id_pp, :id_pedido, :id_producto, :cantidad)
            ");
            $stmtInsert->execute([
                ':id_pp'      => $nuevoId,
                ':id_pedido'  => $id_pedido,
                ':id_producto'=> $id_producto,
                ':cantidad'   => $cantidad_entregada,
            ]);

            $total_real += $cantidad_entregada * $precioExtra;
            continue;
        }

        if ($id_detalle <= 0 || $cantidad_entregada < 0) continue;

        // Obtener precio para calcular total
        $stmtPrecio->execute([':id_detalle' => $id_detalle, ':id_pedido' => $id_pedido]);
        $row = $stmtPrecio->fetch();
        if ($row) {
            $total_real += $cantidad_entregada * floatval($row['precio_unitario']);
        }

        // Actualizar cantidad real entregada
        $stmtUpdate->execute([
            ':cantidad'   => $cantidad_entregada,
            ':id_detalle' => $id_detalle,
            ':id_pedido'  => $id_pedido,
        ]);
    }

    // Actualizar cabecera del pedido — estado 3 (Entregado)
    $obs_auditoria = "DNI receptor: {$dni_receptor} | Cobrado: \${$monto_cobrado}";
    if (!empty($observaciones)) $obs_auditoria .= " | {$observaciones}";

    $stmtPedido = $conexionbd->prepare("
        UPDATE pedido
        SET id_estado              = 3,
            fecha_entrega_real     = NOW(),
            total                  = :total,
            bidones_vacios         = :bidones_vacios,
            observaciones_internas = :observaciones
        WHERE id_pedido = :id_pedido
    ");
    $stmtPedido->execute([
        ':total'         => $total_real,
        ':bidones_vacios'=> $bidones_vacios,
        ':observaciones' => $obs_auditoria,
        ':id_pedido'     => $id_pedido,
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
