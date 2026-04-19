<?php
// ============================================================
//  CONTROLADOR/pedidos/eliminarRuta.php
//  Elimina la ruta y devuelve pedidos a estado pendiente
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

$idRuta = (int)($_POST['idRuta'] ?? 0);
if (!$idRuta) {
    header('Location: /pedidos/gestionarRutaRepartos?error=ID+inválido.');
    exit;
}

// Recuperar pedidos de esa ruta para devolverlos a pendiente
$stmtPedidos = $conexionbd->prepare("
    SELECT id_pedido FROM parada_ruta WHERE id_ruta = :id
");
$stmtPedidos->execute([':id' => $idRuta]);
$pedidosEnRuta = $stmtPedidos->fetchAll();

// Devolver pedidos a estado pendiente (1)
$stmtReset = $conexionbd->prepare("
    UPDATE pedido SET id_estado = 1, updated_at = NOW() WHERE id_pedido = :id
");
foreach ($pedidosEnRuta as $p) {
    $stmtReset->execute([':id' => $p['id_pedido']]);
}

// Cancelar paradas
$conexionbd->prepare("UPDATE parada_ruta SET fecha_baja = NOW() WHERE id_ruta = :id")
           ->execute([':id' => $idRuta]);

// Cancelar ruta
$conexionbd->prepare("UPDATE ruta_reparto SET estado = 4, fecha_baja = NOW() WHERE id_ruta = :id")
           ->execute([':id' => $idRuta]);

header('Location: /pedidos/gestionarRutaRepartos?ok=eliminada');
exit;
