<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/nuevoArreglo.php
//  Trae las máquinas activas para el select del formulario
// ============================================================

// Solo máquinas que no están en baja
$stmt = $conexionbd->prepare("
    SELECT m.id_maquina, m.numero_serie, m.numero_precinto
    FROM maquina_dispensadora m
    INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
    WHERE e.nombre != 'baja'
    ORDER BY m.numero_serie
");
$stmt->execute();
$maquinasDisponibles = $stmt->fetchAll();
