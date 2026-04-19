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

$idRuta        = (int)($_POST['id_ruta']        ?? 0);
$fecha         = $_POST['fecha']                ?? '';
$turno         = $_POST['turno']                ?? '';
$idRepartidor  = !empty($_POST['id_repartidor']) ? (int)$_POST['id_repartidor'] : null;
$observaciones = trim($_POST['observaciones']   ?? '');
$pedidosOrden  = trim($_POST['pedidos_orden']   ?? '');
$kmRecorridos = isset($_POST['km_recorridos']) ? (float)$_POST['km_recorridos'] : 0;


if (!$idRuta || !$fecha || !$turno || empty($pedidosOrden)) {
    header('Location: /pedidos/modificarRutaReparto/' . $idRuta . '?error=Datos+incompletos');
    exit;
}

$ids = array_values(array_filter(array_map('intval', explode(',', $pedidosOrden))));
if (empty($ids)) {
    header('Location: /pedidos/modificarRutaReparto/' . $idRuta . '?error=La+ruta+debe+tener+al+menos+una+parada');
    exit;
}

try {
    $conexionbd->beginTransaction();

    // Guardar ids de paradas anteriores para comparar
    $stmtAnteriores = $conexionbd->prepare("SELECT id_pedido FROM parada_ruta WHERE id_ruta = :id");
    $stmtAnteriores->execute([':id' => $idRuta]);
    $idsAnteriores = $stmtAnteriores->fetchAll(PDO::FETCH_COLUMN);

    // Actualizar cabecera de la ruta
    $stmtRuta = $conexionbd->prepare("
        UPDATE ruta_reparto
        SET fecha_planificada = :fecha,
            turno             = :turno,
            id_repartidor     = :rep,
            observaciones     = :obs,
            km_recorridos     = :km_recorridos,   
            updated_at        = NOW()
        WHERE id_ruta = :id
    ");
    $stmtRuta->execute([
        ':fecha' => $fecha,
        ':turno' => $turno,
        ':rep'   => $idRepartidor,
        ':obs'   => $observaciones ?: null,
        ':km_recorridos'  => $kmRecorridos,
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

    // Pedidos que se QUITARON de la ruta → volver a pendiente (estado 1)
    $quitados = array_diff($idsAnteriores, $ids);
    if (!empty($quitados)) {
        $stmtLiberar = $conexionbd->prepare("
            UPDATE pedido SET id_estado = 1, updated_at = NOW()
            WHERE id_pedido = :id
        ");
        foreach ($quitados as $idQuitado) {
            $stmtLiberar->execute([':id' => (int)$idQuitado]);
        }
    }

    // Pedidos que se AGREGARON a la ruta → marcar como en_ruta (estado 2)
    $agregados = array_diff($ids, $idsAnteriores);
    if (!empty($agregados)) {
        $stmtEnRuta = $conexionbd->prepare("
            UPDATE pedido SET id_estado = 2, updated_at = NOW()
            WHERE id_pedido = :id
        ");
        foreach ($agregados as $idAgregado) {
            $stmtEnRuta->execute([':id' => (int)$idAgregado]);
        }
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
