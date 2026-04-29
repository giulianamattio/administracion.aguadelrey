<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/guardarNuevaMaquina.php
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_POST['serie'])) {
    header('Location: /maquinasDispensadoras/nuevaMaquinaDispensadora?error=serie_requerida');
    exit;
}

if (empty($_POST['tipo'])) {
    header('Location: /maquinasDispensadoras/nuevaMaquinaDispensadora?error=tipo_requerido');
    exit;
}

if (empty($_POST['estado'])) {
    header('Location: /maquinasDispensadoras/nuevaMaquinaDispensadora?error=estado_requerido');
    exit;
}

$stmt = $conexionbd->prepare("
    INSERT INTO maquina_dispensadora 
        (id_estado, tipo, numero_serie, marca, modelo, numero_precinto, precio_alquiler, observaciones)
    VALUES 
        (:id_estado, :tipo, :numero_serie, :marca, :modelo, :numero_precinto, :precio_alquiler, :observaciones)
");

$stmt->execute([
    ':id_estado'       => (int) $_POST['estado'],
    ':tipo'            => trim($_POST['tipo']),
    ':precio_alquiler' => !empty($_POST['precio']) ? (float) $_POST['precio'] : null,
    ':numero_serie'    => trim($_POST['serie']),
    ':marca'           => trim($_POST['descripcion']),
    ':modelo'          => '',
    ':numero_precinto' => trim($_POST['precinto']),
    ':observaciones'   => '',
]);

header('Location: /maquinasDispensadoras/listado?ok=1');
exit;
