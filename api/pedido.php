<?php
// ============================================================
//  api/pedido.php
//  GET /api/pedido?id_pedido=X
//  Header: Authorization: Bearer <token>
//  Response: { "ok": true, "pedido": {...} }
//
//  Decisión de diseño:
//  El sistema web guarda productos en pedido_producto (sin precio_unitario).
//  La API obtiene el precio desde producto.precio_unitario via JOIN.
//  Esto evita tocar el controlador web que ya funciona correctamente.
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiError('Método no permitido', 405);
}

$payload = apiAutenticar();

$id_pedido = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : 0;
if ($id_pedido <= 0) {
    apiError('id_pedido requerido', 400);
}

// Cabecera del pedido
$stmtPedido = $conexionbd->prepare("
    SELECT
        p.id_pedido,
        p.id_cliente,
        p.id_estado,
        ep.nombre           AS estado_nombre,
        p.fecha_entrega_estimada,
        p.observaciones_cliente,
        p.total,
        c.nombre            AS cliente_nombre,
        c.domicilio         AS cliente_direccion
    FROM pedido p
    INNER JOIN estado_pedido ep ON ep.id_estado = p.id_estado
    INNER JOIN cliente c        ON c.id_cliente = p.id_cliente
    WHERE p.id_pedido = :id_pedido
      AND p.id_estado IN (1, 2)
      AND p.fecha_baja IS NULL
");
$stmtPedido->execute([':id_pedido' => $id_pedido]);
$pedido = $stmtPedido->fetch();

if (!$pedido) {
    apiError('Pedido no encontrado o ya fue procesado', 404);
}

// Detalle del pedido desde pedido_producto + JOIN con producto para precio
// pedido_producto no tiene precio_unitario propio, se toma de producto.precio_unitario
$stmtDetalle = $conexionbd->prepare("
    SELECT
        pp.id_pedido_producto   AS id_detalle,
        pp.id_producto,
        pr.nombre               AS producto_nombre,
        pp.cantidad,
        pr.precio_unitario
    FROM pedido_producto pp
    INNER JOIN producto pr ON pr.id_producto = pp.id_producto
    WHERE pp.id_pedido  = :id_pedido
      AND pp.fecha_baja IS NULL
    ORDER BY pp.id_pedido_producto ASC
");
$stmtDetalle->execute([':id_pedido' => $id_pedido]);
$detalle = $stmtDetalle->fetchAll();

// Calcular total desde el detalle (fuente de verdad por ítem)
$total_calculado = array_reduce($detalle, function($carry, $item) {
    return $carry + ($item['cantidad'] * $item['precio_unitario']);
}, 0);

$productos = array_map(function($d) {
    return [
        'id_detalle'      => (int)   $d['id_detalle'],
        'id_producto'     => (int)   $d['id_producto'],
        'nombre'          =>         $d['producto_nombre'],
        'cantidad'        => (int)   $d['cantidad'],
        'precio_unitario' => (float) $d['precio_unitario'],
    ];
}, $detalle);

apiOk([
    'pedido' => [
        'id_pedido'              => (int)   $pedido['id_pedido'],
        'id_cliente'             => (int)   $pedido['id_cliente'],
        'id_estado'              => (int)   $pedido['id_estado'],
        'estado_nombre'          =>         $pedido['estado_nombre'],
        'fecha_entrega_estimada' =>         $pedido['fecha_entrega_estimada'],
        'observaciones_cliente'  =>         $pedido['observaciones_cliente'],
        'total'                  => (float) $total_calculado,
        'cliente_nombre'         =>         $pedido['cliente_nombre'],
        'cliente_direccion'      =>         $pedido['cliente_direccion'],
        'productos'              =>         $productos,
    ]
]);
