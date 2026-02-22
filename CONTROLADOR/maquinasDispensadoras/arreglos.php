<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/arreglos.php
//  Trae todos los arreglos con datos de máquina y reparador
// ============================================================

$stmt = $conexionbd->prepare("
    SELECT 
        a.id_arreglo,
        a.fecha_ingreso,
        a.fecha_egreso,
        a.descripcion,
        a.resuelto,
        a.observaciones,
        m.numero_serie,
        m.numero_precinto,
        e.nombre AS nombre_empleado,
        e.apellido AS apellido_empleado
    FROM arreglo_maquina a
    INNER JOIN maquina_dispensadora m ON m.id_maquina = a.id_maquina
    INNER JOIN usuario_empleado e ON e.id_empleado = a.id_reparador
    ORDER BY a.fecha_ingreso DESC
");
$stmt->execute();
$listadoArreglos = $stmt->fetchAll();
