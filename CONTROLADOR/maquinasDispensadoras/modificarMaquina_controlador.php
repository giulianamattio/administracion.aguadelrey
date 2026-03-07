<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/modificarMaquina.php
//  Recibe el id_maquina por GET, consulta la BD y deja
//  los datos disponibles para que la VISTA los muestre.
// ============================================================

if (empty($_GET['idMaquina'])) {
    header('Location: /maquinasDispensadoras/listado?error=id_requerido');
    exit;
}

$idMaquina = (int) $_GET['idMaquina'];

// Traer datos actuales de la máquina
$stmt = $conexionbd->prepare("
    SELECT m.*, e.nombre AS estado_nombre
    FROM maquina_dispensadora m
    INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
    WHERE m.id_maquina = :id
");
$stmt->execute([':id' => $idMaquina]);
$maquina = $stmt->fetch();

if (!$maquina) {
    header('Location: /maquinasDispensadoras/listado?error=no_encontrada');
    exit;
}

// Traer todos los estados para el select
$stmtEstados = $conexionbd->prepare("SELECT id_estado, nombre FROM estado_maquina ORDER BY nombre");
$stmtEstados->execute();
$estados = $stmtEstados->fetchAll();
