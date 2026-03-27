<?php
/**
 * POST /api/entrega
 *
 * Registra la confirmación de entrega de un pedido.
 * Requiere header Authorization: Bearer <jwt>
 *
 * Body JSON esperado:
 * {
 *   "id_pedido":        1,
 *   "productos":        [ { "id_detalle": 1, "cantidad_entregada": 2 }, ... ],
 *   "monto_cobrado":    850.00,
 *   "dni_receptor":     "12345678",
 *   "observaciones":    "Texto libre opcional"
 * }
 *
 * Decisión de diseño:
 * - Usamos una TRANSACCIÓN porque tocamos dos tablas (pedido + detalle_pedido).
 *   Si cualquier paso falla, hacemos ROLLBACK — nunca quedamos con datos parciales.
 *   Esto es un principio ACID fundamental que en UTN se enseña en Bases de Datos II.
 * - Actualizamos cantidad en detalle_pedido para reflejar lo realmente entregado
 *   (puede diferir si el repartidor ajustó +/- en la app).
 * - Recalculamos el total en base a las cantidades reales entregadas.
 * - Cambiamos id_estado a 3 (Entregado).
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// ── 1. Autenticación ──────────────────────────────────────────────────────────
$payload = verificarJWT();
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
    exit;
}

// ── 2. Validar método ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// ── 3. Leer y validar body ────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

$id_pedido    = isset($body['id_pedido'])    ? intval($body['id_pedido'])       : 0;
$productos    = isset($body['productos'])    ? $body['productos']               : [];
$monto_cobrado= isset($body['monto_cobrado'])? floatval($body['monto_cobrado']) : 0.0;
$dni_receptor = isset($body['dni_receptor']) ? trim($body['dni_receptor'])      : '';
$observaciones= isset($body['observaciones'])? trim($body['observaciones'])     : '';

// Validaciones mínimas
if ($id_pedido <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'id_pedido requerido']);
    exit;
}
if (empty($productos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere al menos un producto entregado']);
    exit;
}

// ── 4. Transacción ────────────────────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    // 4a. Verificar que el pedido existe y está en estado procesable
    $stmtCheck = $pdo->prepare("
        SELECT id_pedido, id_estado
        FROM pedido
        WHERE id_pedido = :id_pedido
          AND id_estado IN (1, 2)
        FOR UPDATE
    ");
    // FOR UPDATE: lock de fila para evitar doble entrega concurrente.
    // Dos repartidores no podrán confirmar el mismo pedido simultáneamente.
    $stmtCheck->execute([':id_pedido' => $id_pedido]);
    $pedidoExiste = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$pedidoExiste) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'El pedido no existe o ya fue procesado']);
        exit;
    }

    // 4b. Actualizar cantidades entregadas en detalle_pedido y calcular total real
    $total_real = 0.0;
    $stmtUpdateDetalle = $pdo->prepare("
        UPDATE detalle_pedido
        SET cantidad = :cantidad
        WHERE id_detalle = :id_detalle
          AND id_pedido  = :id_pedido
    ");
    $stmtPrecio = $pdo->prepare("
        SELECT precio_unitario FROM detalle_pedido
        WHERE id_detalle = :id_detalle AND id_pedido = :id_pedido
    ");

    foreach ($productos as $prod) {
        $id_detalle        = intval($prod['id_detalle']);
        $cantidad_entregada= intval($prod['cantidad_entregada']);

        if ($id_detalle <= 0 || $cantidad_entregada < 0) continue;

        // Obtener precio histórico del detalle para recalcular total
        $stmtPrecio->execute([
            ':id_detalle' => $id_detalle,
            ':id_pedido'  => $id_pedido
        ]);
        $row = $stmtPrecio->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $total_real += $cantidad_entregada * floatval($row['precio_unitario']);
        }

        // Actualizar cantidad real entregada
        $stmtUpdateDetalle->execute([
            ':cantidad'   => $cantidad_entregada,
            ':id_detalle' => $id_detalle,
            ':id_pedido'  => $id_pedido
        ]);
    }

    // 4c. Actualizar cabecera del pedido
    // - id_estado = 3 (Entregado)
    // - fecha_entrega_real = NOW()
    // - total = recalculado con cantidades reales
    // - observaciones_internas = dni + monto + texto libre (auditoria)
    $obs_auditoria = "DNI receptor: {$dni_receptor} | Cobrado: \${$monto_cobrado}";
    if (!empty($observaciones)) {
        $obs_auditoria .= " | {$observaciones}";
    }

    $stmtPedido = $pdo->prepare("
        UPDATE pedido
        SET id_estado            = 3,
            fecha_entrega_real   = NOW(),
            total                = :total,
            observaciones_internas = :observaciones
        WHERE id_pedido = :id_pedido
    ");
    $stmtPedido->execute([
        ':total'        => $total_real,
        ':observaciones'=> $obs_auditoria,
        ':id_pedido'    => $id_pedido
    ]);

    // ── 5. Commit y respuesta ─────────────────────────────────────────────────
    $pdo->commit();

    echo json_encode([
        'success'     => true,
        'id_pedido'   => $id_pedido,
        'total_final' => $total_real,
        'mensaje'     => 'Entrega registrada correctamente'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
