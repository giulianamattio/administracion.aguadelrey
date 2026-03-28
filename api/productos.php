<?php
// ============================================================
//  api/productos.php
//  GET /api/productos
//  Header: Authorization: Bearer <token>
//  Response: { "ok": true, "productos": [...] }
// ============================================================
ob_start();
ini_set('html_errors', '0');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiError('Método no permitido', 405);
}

$payload = apiAutenticar();

$stmt = $conexionbd->prepare("
    SELECT id_producto, nombre, precio_unitario
    FROM producto
    WHERE activo = true
    ORDER BY nombre ASC
");
$stmt->execute();
$productos = $stmt->fetchAll();

$result = array_map(function($p) {
    return [
        'id_producto'     => (int)   $p['id_producto'],
        'nombre'          =>         $p['nombre'],
        'precio_unitario' => (float) $p['precio_unitario'],
    ];
}, $productos);

apiOk(['productos' => $result]);
