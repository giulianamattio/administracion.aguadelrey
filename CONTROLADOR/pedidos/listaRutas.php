<?php
// ============================================================
//  CONTROLADOR/pedidos/listaRutas.php
//  Trae todas las rutas de reparto con sus paradas
// ============================================================

$stmt = $conexionbd->prepare("
    SELECT
        r.id_ruta,
        r.fecha_planificada,
        r.turno,
        r.estado,
        r.observaciones,
        CONCAT(u.nombre, ' ', u.apellido) AS repartidor,
        COUNT(p.id_parada) AS total_paradas
    FROM ruta_reparto r
    LEFT JOIN usuario_empleado u ON u.id_empleado = r.id_repartidor
    LEFT JOIN parada_ruta p ON p.id_ruta = r.id_ruta
    GROUP BY r.id_ruta, r.fecha_planificada, r.turno, r.estado,
             r.observaciones, u.nombre, u.apellido
    ORDER BY r.fecha_planificada DESC, r.turno ASC
");
$stmt->execute();
$rutas = $stmt->fetchAll();
