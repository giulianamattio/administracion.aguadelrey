<?php
/**
 * GET /api/productos
 *
 * Devuelve la lista de productos activos para que el repartidor
 * pueda agregar un producto extra al pedido desde la app móvil.
 * Requiere header Authorization: Bearer <jwt>
 *
 * Decisión: devolvemos solo productos con activo=true.
 * No tiene sentido ofrecer productos dados de baja para una entrega.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiError('Método no permitido', 405);
}

$payload = apiAutenticar();
if (!$payload) {
    apiError('Token inválido o expirado', 401);
}

try {
    $stmt = $conexionbd->prepare("
        SELECT id_producto, nombre, precio_unitario
        FROM producto
        WHERE activo = true
        ORDER BY nombre ASC
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = array_map(function($p) {
        return [
            'id_producto'     => (int)$p['id_producto'],
            'nombre'          => $p['nombre'],
            'precio_unitario' => (float)$p['precio_unitario']
        ];
    }, $productos);

    apiOk(['productos' => $result]);

} catch (PDOException $e) {
    apiError('Error de base de datos: ' . $e->getMessage(), 500);
}
