<?php
/**
 * GET /api/pedido?id_pedido=X
 *
 * Devuelve el pedido con su detalle de productos.
 * Requiere header Authorization: Bearer <jwt>
 *
 * Decisión de diseño (UTN reasoning):
 * - Recibimos id_pedido directo porque parada_ruta ya lo tiene resuelto.
 *   Evitamos un JOIN extra por cliente y hacemos la consulta más simple y performante.
 * - Devolvemos también id_estado para que la app pueda validar
 *   que el pedido sigue Pendiente (1) o En ruta (2) antes de mostrar la pantalla.
 * - El JOIN con producto nos da nombre y precio_unitario vigente,
 *   pero mostramos el precio de detalle_pedido (precio pactado al crear el pedido)
 *   para respetar el precio histórico — importante en negocio de reparto.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require_once __DIR__ . '/auth.php';   // valida JWT y retorna payload
require_once __DIR__ . '/db.php';     // retorna PDO en $pdo

// ── 1. Autenticación ─────────────────────────────────────────────────────────
$payload = verificarJWT();
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
    exit;
}

// ── 2. Validar parámetro ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$id_pedido = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : 0;
if ($id_pedido <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'id_pedido requerido']);
    exit;
}

// ── 3. Consulta principal ─────────────────────────────────────────────────────
// Decisión: un solo query con JOIN en lugar de dos queries separados.
// Más eficiente y atómico — si el pedido no existe, no devolvemos nada.
try {
    // 3a. Cabecera del pedido
    $stmtPedido = $pdo->prepare("
        SELECT
            p.id_pedido,
            p.id_cliente,
            p.id_estado,
            ep.nombre            AS estado_nombre,
            p.fecha_entrega_estimada,
            p.observaciones_cliente,
            p.total,
            c.nombre             AS cliente_nombre,
            c.direccion          AS cliente_direccion
        FROM pedido p
        INNER JOIN estado_pedido ep ON ep.id_estado = p.id_estado
        INNER JOIN cliente c        ON c.id_cliente = p.id_cliente
        WHERE p.id_pedido = :id_pedido
          AND p.id_estado IN (1, 2)
    ");
    // Nota: filtramos estados 1 (Pendiente) y 2 (En ruta).
    // Si el pedido está Entregado (3) o Cancelado (4), no lo mostramos —
    // evitamos que el repartidor entregue dos veces por error.

    $stmtPedido->execute([':id_pedido' => $id_pedido]);
    $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        http_response_code(404);
        echo json_encode(['error' => 'Pedido no encontrado o ya fue procesado']);
        exit;
    }

    // 3b. Detalle del pedido (productos)
    $stmtDetalle = $pdo->prepare("
        SELECT
            dp.id_detalle,
            dp.id_producto,
            pr.nombre            AS producto_nombre,
            dp.cantidad,
            dp.precio_unitario
        FROM detalle_pedido dp
        INNER JOIN producto pr ON pr.id_producto = dp.id_producto
        WHERE dp.id_pedido = :id_pedido
        ORDER BY dp.id_detalle ASC
    ");
    $stmtDetalle->execute([':id_pedido' => $id_pedido]);
    $detalle = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

    // 3c. Calcular total del lado servidor (fuente de verdad)
    // Decisión: aunque 'total' existe en pedido, lo recalculamos desde detalle
    // para que la app siempre muestre el valor correcto aunque el total esté NULL.
    $total_calculado = array_reduce($detalle, function($carry, $item) {
        return $carry + ($item['cantidad'] * $item['precio_unitario']);
    }, 0);

    // ── 4. Respuesta ─────────────────────────────────────────────────────────
    echo json_encode([
        'success' => true,
        'pedido'  => [
            'id_pedido'               => (int) $pedido['id_pedido'],
            'id_cliente'              => (int) $pedido['id_cliente'],
            'id_estado'               => (int) $pedido['id_estado'],
            'estado_nombre'           => $pedido['estado_nombre'],
            'fecha_entrega_estimada'  => $pedido['fecha_entrega_estimada'],
            'observaciones_cliente'   => $pedido['observaciones_cliente'],
            'total'                   => (float) $total_calculado,
            'cliente_nombre'          => $pedido['cliente_nombre'],
            'cliente_direccion'       => $pedido['cliente_direccion'],
            'productos'               => array_map(function($d) {
                return [
                    'id_detalle'      => (int) $d['id_detalle'],
                    'id_producto'     => (int) $d['id_producto'],
                    'nombre'          => $d['producto_nombre'],
                    'cantidad'        => (int) $d['cantidad'],
                    'precio_unitario' => (float) $d['precio_unitario'],
                ];
            }, $detalle)
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
