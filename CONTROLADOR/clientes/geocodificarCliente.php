<?php
// ============================================================
//  CONTROLADOR/clientes/geocodificarCliente.php
//  Endpoint AJAX: recibe id_cliente, geocodifica su domicilio
//  y guarda lat/lng en la tabla cliente.
//  Llamado automáticamente al aprobar un cliente nuevo.
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/geocodificacion.php');

header('Content-Type: application/json');

$idCliente = (int)($_POST['id_cliente'] ?? $_GET['id_cliente'] ?? 0);
if (!$idCliente) {
    echo json_encode(['ok' => false, 'error' => 'id_cliente requerido']);
    exit;
}

// Traer domicilio del cliente
$stmt = $conexionbd->prepare("
    SELECT domicilio, localidad, provincia, latitud, longitud
    FROM cliente WHERE id_cliente = :id
");
$stmt->execute([':id' => $idCliente]);
$cliente = $stmt->fetch();

if (!$cliente) {
    echo json_encode(['ok' => false, 'error' => 'Cliente no encontrado']);
    exit;
}

// Si ya tiene coordenadas, no geocodificar de nuevo
if ($cliente['latitud'] && $cliente['longitud']) {
    echo json_encode([
        'ok'  => true,
        'msg' => 'Ya tenía coordenadas',
        'lat' => $cliente['latitud'],
        'lng' => $cliente['longitud'],
    ]);
    exit;
}

if (empty($cliente['domicilio'])) {
    echo json_encode(['ok' => false, 'error' => 'El cliente no tiene domicilio cargado']);
    exit;
}

// Geocodificar
$coords = Geocodificacion::geocodificar(
    $cliente['domicilio'],
    $cliente['localidad'] ?: 'San Francisco',
    $cliente['provincia'] ?: 'Córdoba'
);

if (!$coords) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo geocodificar la dirección']);
    exit;
}

// Guardar en BD
$stmtUp = $conexionbd->prepare("
    UPDATE cliente SET latitud = :lat, longitud = :lng, updated_at = NOW()
    WHERE id_cliente = :id
");
$stmtUp->execute([
    ':lat' => $coords['lat'],
    ':lng' => $coords['lng'],
    ':id'  => $idCliente,
]);

// Respetar límite de Nominatim: 1 req/segundo
sleep(1);

echo json_encode([
    'ok'  => true,
    'msg' => 'Coordenadas guardadas',
    'lat' => $coords['lat'],
    'lng' => $coords['lng'],
]);
