<?php
// ============================================================
//  CONTROLADOR/clientes/guardarCoordenadas.php
//  Recibe lat/lng desde el navegador (JS) y guarda en BD.
//  No hace ninguna llamada externa — solo un UPDATE.
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

header('Content-Type: application/json');

$idCliente = (int)($_POST['id_cliente'] ?? 0);
$lat       = (float)($_POST['lat']       ?? 0);
$lng       = (float)($_POST['lng']       ?? 0);

if (!$idCliente || !$lat || !$lng) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Validar rango de coordenadas básico (Argentina)
if ($lat < -55 || $lat > -21 || $lng < -73 || $lng > -53) {
    echo json_encode(['ok' => false, 'error' => 'Coordenadas fuera de rango para Argentina']);
    exit;
}

$stmt = $conexionbd->prepare("
    UPDATE cliente
    SET latitud = :lat, longitud = :lng, updated_at = NOW()
    WHERE id_cliente = :id
");
$stmt->execute([':lat' => $lat, ':lng' => $lng, ':id' => $idCliente]);

echo json_encode(['ok' => true, 'lat' => $lat, 'lng' => $lng]);
