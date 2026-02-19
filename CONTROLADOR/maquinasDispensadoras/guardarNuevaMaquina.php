<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('/var/lib/php/sessions');
    session_start();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_POST['serie'])) {
    header('Location: /maquinasDispensadoras/nuevaMaquinaDispensadora?error=serie_requerida');
    exit;
}

$stmt = $conexionbd->prepare("
    INSERT INTO maquina_dispensadora 
        (id_estado, numero_serie, marca, modelo, numero_precinto, observaciones)
    VALUES 
        (:id_estado, :numero_serie, :marca, :modelo, :numero_precinto, :observaciones)
");

$stmt->execute([
    ':id_estado'       => $_POST['tipo'],
    ':numero_serie'    => trim($_POST['serie']),
    ':marca'           => trim($_POST['descripcion']),
    ':modelo'          => '',
    ':numero_precinto' => trim($_POST['precinto']),
    ':observaciones'   => '',
]);

header('Location: /maquinasDispensadoras/listado?ok=1');
exit;