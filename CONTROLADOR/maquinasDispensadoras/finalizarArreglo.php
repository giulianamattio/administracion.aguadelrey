<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/finalizarArreglo.php
//  Carga el arreglo actual para pre-llenar el formulario
// ============================================================

if (empty($_GET['idArreglo'])) {
    header('Location: /maquinasDispensadoras/reportes?error=id_requerido');
    exit;
}

$idArreglo = (int) $_GET['idArreglo'];

$stmt = $conexionbd->prepare("
    SELECT a.*, m.numero_serie, m.numero_precinto
    FROM arreglo_maquina a
    INNER JOIN maquina_dispensadora m ON m.id_maquina = a.id_maquina
    WHERE a.id_arreglo = :id
");
$stmt->execute([':id' => $idArreglo]);
$arreglo = $stmt->fetch();

if (!$arreglo) {
    header('Location: /maquinasDispensadoras/reportes?error=no_encontrado');
    exit;
}

if ($arreglo['resuelto']) {
    header('Location: /maquinasDispensadoras/reportes?error=ya_resuelto');
    exit;
}
