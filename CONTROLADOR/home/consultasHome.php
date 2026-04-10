<?php
// Rutas planificadas
$stmt = $conexionbd->query("SELECT COUNT(*) AS total FROM ruta_reparto WHERE estado = 'planificada'");
$rutasPlanificadas = $stmt->fetch()['total'];

// Pedidos pendientes
$stmt = $conexionbd->query("SELECT COUNT(*) AS total FROM pedido WHERE id_estado = 1");
$pedidosPendientes = $stmt->fetch()['total'];

// Clientes en lista de espera
$stmt = $conexionbd->query("SELECT COUNT(*) AS total FROM lista_espera WHERE estado = 'pendiente'");
$clientesEspera = $stmt->fetch()['total'];

// Máquinas activas
$stmt = $conexionbd->query("SELECT COUNT(*) AS total FROM maquina_dispensadora WHERE fecha_baja IS NULL AND id_estado IN (1, 2)");
$maquinasActivas = $stmt->fetch()['total'];
?>