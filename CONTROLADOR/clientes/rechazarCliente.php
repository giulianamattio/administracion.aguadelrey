<?php
// ============================================================
//  CONTROLADOR/clientes/rechazarCliente.php
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/session.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_POST['idEspera'])) {
    header('Location: /clientes/listaDeEspera?error=id_requerido');
    exit;
}

$stmt = $conexionbd->prepare("
    UPDATE lista_espera SET estado = 'rechazado' WHERE id_espera = :id
");
$stmt->execute([':id' => (int) $_POST['idEspera']]);

header('Location: /clientes/listaDeEspera?ok=rechazado');
exit;
