<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/bajaMaquina.php
//  Cambia el estado de la máquina a 'baja' (no elimina el registro)
//  Buena práctica: nunca borrar físicamente, solo dar de baja lógica.
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_GET['idMaquina'])) {
    header('Location: /maquinasDispensadoras/listado?error=id_requerido');
    exit;
}

$idMaquina = (int) $_GET['idMaquina'];

// Buscar el id_estado de 'baja' dinámicamente
$stmtEstado = $conexionbd->prepare("SELECT id_estado FROM estado_maquina WHERE nombre = 'baja'");
$stmtEstado->execute();
$estadoBaja = $stmtEstado->fetch();

if (!$estadoBaja) {
    header('Location: /maquinasDispensadoras/listado?error=estado_baja_no_encontrado');
    exit;
}

$stmt = $conexionbd->prepare("
    UPDATE maquina_dispensadora SET
        id_estado  = :id_estado,
        fecha_baja = CURRENT_DATE,
        updated_at = NOW()
    WHERE id_maquina = :id_maquina
");

$stmt->execute([
    ':id_estado'  => $estadoBaja['id_estado'],
    ':id_maquina' => $idMaquina,
]);

header('Location: /maquinasDispensadoras/listado?ok=baja');
exit;
