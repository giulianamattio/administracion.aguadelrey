<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/guardarFinalizacionArreglo.php
//  Cierra el arreglo y devuelve la máquina a estado disponible
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_POST['idArreglo']) || empty($_POST['idMaquina'])) {
    header('Location: /maquinasDispensadoras/reportes?error=datos_incompletos');
    exit;
}

if (empty($_POST['fecha_egreso'])) {
    header('Location: /maquinasDispensadoras/finalizarArreglo/' . (int)$_POST['idArreglo'] . '?error=fecha_requerida');
    exit;
}

// 1 — Cerrar el arreglo
$stmt = $conexionbd->prepare("
    UPDATE arreglo_maquina SET
        fecha_egreso  = :fecha_egreso,
        observaciones = :observaciones,
        resuelto      = TRUE
    WHERE id_arreglo = :id_arreglo
");
$stmt->execute([
    ':fecha_egreso'  => $_POST['fecha_egreso'],
    ':observaciones' => trim($_POST['observaciones'] ?? ''),
    ':id_arreglo'    => (int) $_POST['idArreglo'],
]);

// 2 — Devolver la máquina a estado 'disponible'
$stmtEstado = $conexionbd->prepare("
    SELECT id_estado FROM estado_maquina WHERE nombre = 'disponible'
");
$stmtEstado->execute();
$estadoDisponible = $stmtEstado->fetch();

if ($estadoDisponible) {
    $stmtMaquina = $conexionbd->prepare("
        UPDATE maquina_dispensadora SET
            id_estado  = :id_estado,
            updated_at = NOW()
        WHERE id_maquina = :id_maquina
    ");
    $stmtMaquina->execute([
        ':id_estado'  => $estadoDisponible['id_estado'],
        ':id_maquina' => (int) $_POST['idMaquina'],
    ]);
}

header('Location: /maquinasDispensadoras/reportes?ok=resuelto');
exit;
