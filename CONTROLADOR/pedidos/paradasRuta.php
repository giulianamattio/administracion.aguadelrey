<?php
// ============================================================
//  CONTROLADOR/pedidos/paradasRuta.php
//  Devuelve las paradas de una ruta en JSON para el modal
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');
header('Content-Type: application/json');

$idRuta = (int)($_GET['id'] ?? 0);
if (!$idRuta) { echo json_encode([]); exit; }

$stmt = $conexionbd->prepare("
    SELECT
        pr.orden,
        c.nombre,
        c.apellido,
        c.domicilio,
        COALESCE(p.observaciones_cliente, '') AS observaciones
    FROM parada_ruta pr
    JOIN pedido p  ON p.id_pedido  = pr.id_pedido
    JOIN cliente c ON c.id_cliente = p.id_cliente
    WHERE pr.id_ruta = :id
    ORDER BY pr.orden ASC
");
$stmt->execute([':id' => $idRuta]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));