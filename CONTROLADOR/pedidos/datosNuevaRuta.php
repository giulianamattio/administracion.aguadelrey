<?php
// ============================================================
//  CONTROLADOR/pedidos/datosNuevaRuta.php
//  Trae pedidos pendientes (sin ruta asignada) y repartidores
// ============================================================

// Pedidos pendientes que aún no tienen parada asignada en una ruta activa
$stmtPedidos = $conexionbd->prepare("
    SELECT
        p.id_pedido,
        p.observaciones_cliente,
        p.fecha_entrega_estimada,
        c.nombre,
        c.apellido,
        c.domicilio,
        c.localidad
    FROM pedido p
    JOIN cliente c ON c.id_cliente = p.id_cliente
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

// Repartidores disponibles (empleados activos)
$stmtRep = $conexionbd->prepare("
    SELECT id_empleado, nombre, apellido
    FROM usuario_empleado
    WHERE activo = TRUE OR baja_fecha IS NULL
    ORDER BY apellido ASC
");
$stmtRep->execute();
$repartidores = $stmtRep->fetchAll();
