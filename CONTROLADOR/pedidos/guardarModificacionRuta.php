<?php
// ============================================================
//  CONTROLADOR/pedidos/guardarModificacionRuta.php
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pedidos/gestionarRutaRepartos');
    exit;
}

$idRuta       = (int)($_POST['id_ruta']       ?? 0);
$fecha        = $_POST['fecha']               ?? '';
$turno        = $_POST['turno']               ?? '';
$idRepartidor = !empty($_POST['id_repartidor']) ? (int)$_POST['id_repartidor'] : null;
$observaciones= trim($_POST['observaciones']  ?? '');
$pedidosOrden = trim($_POST['pedidos_orden']  ?? '');

if (!$idRuta || !$fecha || !$turno || empty($pedidosOrden)) {
    header('Location: /pedidos/modificarRutaReparto/' . $idRuta . '?error=Datos+incompletos');
    exit;
}

$ids = array_filter(array_map('intval', explode(',', $pedidosOrden)));
if (empty($ids)) {
    header('Location: /pedidos/modificarRutaReparto/' . $idRuta . '?error=La+ruta+debe+tener+al+menos+una+parada');
    exit;
}

try {
    $conexionbd->beginTransaction();

    // Actualizar cabecera de la ruta
    $stmtRuta = $conexionbd->prepare("
        UPDATE ruta_reparto
        SET fecha_planificada = :fecha,
            turno             = :turno,
            id_repartidor     = :rep,
            observaciones     = :obs,
            updated_at        = NOW()
        WHERE id_ruta = :id
    ");
    $stmtRuta->execute([
        ':fecha' => $fecha,
        ':turno' => $turno,
        ':rep'   => $idRepartidor,
        ':obs'   => $observaciones ?: null,
        ':id'    => $idRuta,
    ]);

    // Eliminar paradas anteriores
    $conexionbd->prepare("DELETE FROM parada_ruta WHERE id_ruta = :id")
               ->execute([':id' => $idRuta]);

    // Insertar nuevas paradas con el orden enviado
    $stmtParada = $conexionbd->prepare("
        INSERT INTO parada_ruta (id_ruta, id_pedido, orden)
        VALUES (:ruta, :pedido, :orden)
    ");
    foreach ($ids as $orden => $idPedido) {
        $stmtParada->execute([
            ':ruta'   => $idRuta,
            ':pedido' => $idPedido,
            ':orden'  => $orden + 1,
        ]);
    }

    $conexionbd->commit();
    header('Location: /pedidos/gestionarRutaRepartos?ok=Ruta+modificada+correctamente');
    exit;

} catch (Exception $e) {
    $conexionbd->rollBack();
    error_log('Error guardarModificacionRuta: ' . $e->getMessage());
    header('Location: /pedidos/modificarRutaReparto/' . $idRuta . '?error=Error+al+guardar.+Intente+de+nuevo.');
    exit;
}
