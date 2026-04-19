<?php
// CONTROLADOR/pedidos/actualizarEstadoRutas.php

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    /*
     * Busca todas las rutas que:
     *   - NO estén ya en estado 3 (completada)
     *   - Tengan al menos una parada
     *   - TODOS sus pedidos estén en estado 3 (entregado) o 4 (cancelado) o 5 (no entregado)
     *
     * La subconsulta con COUNT + FILTER verifica que no quede ningún pedido
     * en otro estado distinto a 3 o 4.
     */
    $sqlBuscar = "
        SELECT r.id_ruta
        FROM ruta_reparto r
        WHERE r.estado != 3
          AND r.fecha_baja IS NULL
          AND EXISTS (
              SELECT 1 FROM parada_ruta pr WHERE pr.id_ruta = r.id_ruta
          )
          AND NOT EXISTS (
              SELECT 1
              FROM parada_ruta pr
              JOIN pedido p ON p.id_pedido = pr.id_pedido
              WHERE pr.id_ruta = r.id_ruta
                AND p.id_estado NOT IN (3, 4, 5)
          )
    ";

    $stmt  = $conexionbd->query($sqlBuscar);
    $rutas = $stmt->fetchAll();

    if (empty($rutas)) {
        echo json_encode(['actualizadas' => 0]);
        exit;
    }

    $ids = array_column($rutas, 'id_ruta');

    // Construir placeholders (:id0, :id1, ...)
    $placeholders = implode(',', array_map(fn($i) => ":id$i", array_keys($ids)));

    $sqlUpdate = "
        UPDATE ruta_reparto
        SET estado = 3, fecha_baja = NOW()
        WHERE id_ruta IN ($placeholders)
    ";

    $stmtUpdate = $conexionbd->prepare($sqlUpdate);

    foreach ($ids as $i => $id) {
        $stmtUpdate->bindValue(":id$i", $id, PDO::PARAM_INT);
    }

    $stmtUpdate->execute();
    $actualizadas = $stmtUpdate->rowCount();

    echo json_encode(['actualizadas' => $actualizadas]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>