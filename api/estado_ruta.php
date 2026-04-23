<?php
// ============================================================
//  api/estado_ruta.php
//  POST /api/estado-ruta
//  Header: Authorization: Bearer <token>
//  Body JSON: { "id_ruta": 5, "estado": 2 }
//
//  estados válidos desde la app móvil:
//    2 = En curso   (repartidor inicia la ruta)
//    3 = Completada (repartidor finaliza la ruta)
//
//  Reglas de negocio:
//    - Solo el repartidor asignado puede cambiar el estado (o un admin)
//    - Para pasar a 3 (Completada), todos los pedidos deben estar
//      en estado 3 (Entregado), 4 (Cancelado) o 5 (No entregado)
//    - No se puede retroceder de estado (3 → 2 no permitido)
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Método no permitido', 405);
}

$payload    = apiAutenticar();
$idEmpleado = $payload['id_empleado'];
$idRol      = $payload['id_rol'];

$body = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    apiError('JSON inválido', 400);
}

$id_ruta     = isset($body['id_ruta'])  ? intval($body['id_ruta'])  : 0;
$nuevoEstado = isset($body['estado'])   ? intval($body['estado'])   : 0;

if ($id_ruta <= 0)                        apiError('id_ruta requerido', 400);
if (!in_array($nuevoEstado, [2, 3]))      apiError('Estado inválido. Valores permitidos: 2 (En curso), 3 (Completada)', 400);

try {
    // Verificar que la ruta existe y pertenece al repartidor (salvo admin)
    if ($idRol == 1) {
        $stmtCheck = $conexionbd->prepare("
            SELECT id_ruta, estado FROM ruta_reparto
            WHERE id_ruta = :id_ruta AND fecha_baja IS NULL
        ");
        $stmtCheck->execute([':id_ruta' => $id_ruta]);
    } else {
        $stmtCheck = $conexionbd->prepare("
            SELECT id_ruta, estado FROM ruta_reparto
            WHERE id_ruta = :id_ruta
              AND id_repartidor = :id_empleado
              AND fecha_baja IS NULL
        ");
        $stmtCheck->execute([':id_ruta' => $id_ruta, ':id_empleado' => $idEmpleado]);
    }

    $ruta = $stmtCheck->fetch();
    if (!$ruta) {
        apiError('Ruta no encontrada o sin permiso para modificarla', 404);
    }

    $estadoActual = intval($ruta['estado']);

    // No permitir retroceder de estado
    if ($nuevoEstado <= $estadoActual) {
        apiError('No se puede retroceder el estado de la ruta', 409);
    }

    // Para completar (estado 3), verificar que no queden pedidos pendientes
    if ($nuevoEstado === 3) {
        $stmtPendientes = $conexionbd->prepare("
            SELECT COUNT(*) AS pendientes
            FROM parada_ruta pr
            JOIN pedido p ON p.id_pedido = pr.id_pedido
            WHERE pr.id_ruta = :id_ruta
              AND p.id_estado NOT IN (3, 4, 5)
        ");
        $stmtPendientes->execute([':id_ruta' => $id_ruta]);
        $row = $stmtPendientes->fetch();

        if ($row && intval($row['pendientes']) > 0) {
            apiError(
                'No se puede completar la ruta: quedan ' . $row['pendientes'] . ' pedido(s) sin procesar',
                409
            );
        }

        // Completar → registrar fecha_baja como cierre de ruta
        $stmt = $conexionbd->prepare("
            UPDATE ruta_reparto
            SET estado = 3, fecha_baja = NOW()
            WHERE id_ruta = :id_ruta
        ");
    } else {
        // En curso → solo actualizar estado
        $stmt = $conexionbd->prepare("
            UPDATE ruta_reparto
            SET estado = :estado
            WHERE id_ruta = :id_ruta
        ");
        $stmt->bindValue(':estado', $nuevoEstado, PDO::PARAM_INT);
    }

    $stmt->bindValue(':id_ruta', $id_ruta, PDO::PARAM_INT);
    $stmt->execute();

    $nombreEstado = $nuevoEstado === 2 ? 'En curso' : 'Completada';
    apiOk(['mensaje' => "Ruta marcada como: $nombreEstado"]);

} catch (PDOException $e) {
    apiError('Error de base de datos', 500);
}