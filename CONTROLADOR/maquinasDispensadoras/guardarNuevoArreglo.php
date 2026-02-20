<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/guardarNuevoArreglo.php
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_POST['idMaquina'])) {
    header('Location: /maquinasDispensadoras/nuevoArreglo?error=maquina_requerida');
    exit;
}

if (empty($_POST['fecha'])) {
    header('Location: /maquinasDispensadoras/nuevoArreglo?error=fecha_requerida');
    exit;
}

// Combinar diagnóstico seleccionado + texto libre si existe
$diagnostico = trim($_POST['diagnostico'] ?? '');
$otroDiagnostico = trim($_POST['otroDiagnostico'] ?? '');

if ($diagnostico === '' && $otroDiagnostico === '') {
    header('Location: /maquinasDispensadoras/nuevoArreglo?error=diagnostico_requerido');
    exit;
}

$diagnosticoFinal = $diagnostico;
if ($otroDiagnostico !== '') {
    $diagnosticoFinal .= ($diagnosticoFinal !== '' ? ' | ' : '') . $otroDiagnostico;
}

$stmt = $conexionbd->prepare("
    INSERT INTO arreglo_maquina (id_maquina, fecha_arreglo, diagnostico, observaciones)
    VALUES (:id_maquina, :fecha_arreglo, :diagnostico, :observaciones)
");

$stmt->execute([
    ':id_maquina'    => (int) $_POST['idMaquina'],
    ':fecha_arreglo' => $_POST['fecha'],
    ':diagnostico'   => $diagnosticoFinal,
    ':observaciones' => '',
]);

// Cambiar estado de la máquina a 'en_reparacion'
$stmtEstado = $conexionbd->prepare("
    SELECT id_estado FROM estado_maquina WHERE nombre = 'en_reparacion'
");
$stmtEstado->execute();
$estadoReparacion = $stmtEstado->fetch();

if ($estadoReparacion) {
    $stmtUpdate = $conexionbd->prepare("
        UPDATE maquina_dispensadora 
        SET id_estado = :id_estado, updated_at = NOW()
        WHERE id_maquina = :id_maquina
    ");
    $stmtUpdate->execute([
        ':id_estado'  => $estadoReparacion['id_estado'],
        ':id_maquina' => (int) $_POST['idMaquina'],
    ]);
}

header('Location: /maquinasDispensadoras/listado?ok=arreglo');
exit;
