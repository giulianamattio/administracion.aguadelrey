<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/maquinas.php
//  Lista máquinas con filtro opcional por estado
// ============================================================

// Traer todos los estados para el select del filtro
$stmtEstados = $conexionbd->prepare("SELECT id_estado, nombre FROM estado_maquina ORDER BY nombre");
$stmtEstados->execute();
$estados = $stmtEstados->fetchAll();

// Si viene un filtro por estado lo aplicamos, sino traemos todas
$filtroEstado = isset($_GET['estado']) && $_GET['estado'] !== '' ? (int)$_GET['estado'] : null;

if ($filtroEstado) {
    $stmt = $conexionbd->prepare("
        SELECT m.id_maquina, m.numero_serie, m.numero_precinto, m.marca, e.nombre AS estado
        FROM maquina_dispensadora m
        INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
        WHERE m.id_estado = :estado
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([':estado' => $filtroEstado]);
} else {
    $stmt = $conexionbd->prepare("
        SELECT m.id_maquina, m.numero_serie, m.numero_precinto, m.marca, e.nombre AS estado
        FROM maquina_dispensadora m
        INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
        ORDER BY m.created_at DESC
    ");
    $stmt->execute();
}

$listadoMaquinas = $stmt->fetchAll();