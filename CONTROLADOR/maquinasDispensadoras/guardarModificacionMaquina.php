<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/guardarModificacionMaquina.php
//  Procesa el POST del formulario modificarMaquina
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_POST['idMaquina'])) {
    header('Location: /maquinasDispensadoras/listado?error=id_requerido');
    exit;
}

if (empty($_POST['serie'])) {
    header('Location: /maquinasDispensadoras/modificarMaquinaDispensadora/' . (int)$_POST['idMaquina'] . '?error=serie_requerida');
    exit;
}

$stmt = $conexionbd->prepare("
    UPDATE maquina_dispensadora SET
        id_estado       = :id_estado,
        numero_serie    = :numero_serie,
        marca           = :marca,
        numero_precinto = :numero_precinto,
        updated_at      = NOW()
    WHERE id_maquina = :id_maquina
");

$stmt->execute([
    ':id_estado'       => (int) $_POST['tipo'],
    ':numero_serie'    => trim($_POST['serie']),
    ':marca'           => trim($_POST['descripcion']),
    ':numero_precinto' => trim($_POST['precinto']),
    ':id_maquina'      => (int) $_POST['idMaquina'],
]);

header('Location: /maquinasDispensadoras/listado?ok=modificado');
exit;
