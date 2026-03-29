<?php
// ============================================================
//  CONTROLADOR/pedidos/datosNuevaRuta.php  (versión con geocodificación)
//  Trae pedidos pendientes con coordenadas y repartidores
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/geocodificacion.php');

// Configuración del galpón desde BD
$stmtGalpon = $conexionbd->prepare("
    SELECT clave, valor FROM configuracion_sistema
    WHERE clave IN ('galpon_latitud', 'galpon_longitud', 'galpon_direccion')
");
$stmtGalpon->execute();
$configRows = $stmtGalpon->fetchAll();
$config = [];
foreach ($configRows as $row) {
    $config[$row['clave']] = $row['valor'];
}
$galpon = [
    'lat' => (float)($config['galpon_latitud']  ?? -31.4267),
    'lng' => (float)($config['galpon_longitud'] ?? -62.0834),
    'dir' => $config['galpon_direccion'] ?? 'Galpón Agua del Rey',
];

// Pedidos pendientes sin ruta asignada, con coordenadas del cliente
$stmtPedidos = $conexionbd->prepare("
    SELECT
        p.id_pedido,
        p.observaciones_cliente,
        p.fecha_entrega_estimada,
        c.nombre,
        c.apellido,
        c.domicilio,
        c.localidad,
        c.latitud,
        c.longitud,
        t.nombre AS turno
    FROM pedido p
    JOIN cliente c ON c.id_cliente = p.id_cliente
    LEFT JOIN turno t ON t.id_turno = p.id_turno_deseado
    WHERE p.id_estado = 1
      AND p.id_pedido NOT IN (
          SELECT pr.id_pedido FROM parada_ruta pr
          JOIN ruta_reparto r ON r.id_ruta = pr.id_ruta
          WHERE r.estado IN ('planificada', 'en_curso')
      )
    ORDER BY p.fecha_entrega_estimada ASC, c.apellido ASC
");
$stmtPedidos->execute();
$pedidosPendientes = $stmtPedidos->fetchAll();

// Repartidores activos
$stmtRep = $conexionbd->prepare("
    SELECT id_empleado, nombre, apellido
    FROM usuario_empleado
    WHERE activo = TRUE
    ORDER BY apellido ASC
");
$stmtRep->execute();
$repartidores = $stmtRep->fetchAll();

// ¿Cuántos pedidos tienen coordenadas?
$conCoordenadas = array_filter($pedidosPendientes, fn($p) => $p['latitud'] && $p['longitud']);
$sinCoordenadas = count($pedidosPendientes) - count($conCoordenadas);
