<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/maquinas.php
//  Lista todas las máquinas dispensadoras con su estado
// ============================================================

$stmt = $conexionbd->prepare("
    SELECT 
        m.id_maquina,
        m.numero_serie,
        m.numero_precinto,
        m.marca,
        m.modelo,
        e.nombre AS estado
    FROM maquina_dispensadora m
    INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
    ORDER BY m.created_at DESC
");
$stmt->execute();
$listadoMaquinas = $stmt->fetchAll();