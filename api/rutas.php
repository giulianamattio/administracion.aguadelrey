<?php
// ============================================================
//  api/rutas.php
//  GET /api/rutas
//  Header: Authorization: Bearer <token>
//  Response: { "ok": true, "rutas": [...] }
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/apiHelper.php');

apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiError('Método no permitido', 405);
}

// Validar token y obtener datos del empleado
$payload     = apiAutenticar();
$idEmpleado  = $payload['id_empleado'];
$idRol       = $payload['id_rol'];

// Admin (rol 1) ve todas las rutas planificadas de hoy
// Repartidor (rol 2) ve solo sus rutas
$hoy = date('Y-m-d');

if ($idRol == 1) {
    $stmtRutas = $conexionbd->prepare("
        SELECT r.id_ruta, r.estado, r.fecha_planificada, r.turno,
               r.observaciones,
               COALESCE(u.nombre || ' ' || u.apellido, 'Sin asignar') AS repartidor
        FROM ruta_reparto r
        LEFT JOIN usuario_empleado u ON u.id_empleado = r.id_repartidor
        WHERE r.estado IN ('planificada', 'en_curso')
          AND r.fecha_planificada >= :hoy
        ORDER BY r.fecha_planificada ASC, r.turno ASC
    ");
    $stmtRutas->execute([':hoy' => $hoy]);
} else {
    $stmtRutas = $conexionbd->prepare("
        SELECT r.id_ruta, r.estado, r.fecha_planificada, r.turno,
               r.observaciones,
               COALESCE(u.nombre || ' ' || u.apellido, 'Sin asignar') AS repartidor
        FROM ruta_reparto r
        LEFT JOIN usuario_empleado u ON u.id_empleado = r.id_repartidor
        WHERE r.id_repartidor = :id
          AND r.estado IN ('planificada', 'en_curso')
          AND r.fecha_planificada >= :hoy
        ORDER BY r.fecha_planificada ASC, r.turno ASC
    ");
    $stmtRutas->execute([':id' => $idEmpleado, ':hoy' => $hoy]);
}

$rutasDB = $stmtRutas->fetchAll();

// Para cada ruta traer sus paradas ordenadas
$stmtParadas = $conexionbd->prepare("
    SELECT
        pr.id_parada,
        pr.orden,
        p.id_pedido,
        p.observaciones_cliente,
        c.nombre,
        c.apellido,
        c.domicilio,
        c.localidad,
        c.telefono
    FROM parada_ruta pr
    JOIN pedido p  ON p.id_pedido  = pr.id_pedido
    JOIN cliente c ON c.id_cliente = p.id_cliente
    WHERE pr.id_ruta = :id_ruta
    ORDER BY pr.orden ASC
");

$rutas = [];
foreach ($rutasDB as $ruta) {
    $stmtParadas->execute([':id_ruta' => $ruta['id_ruta']]);
    $paradasDB = $stmtParadas->fetchAll();

    $paradas = [];
    foreach ($paradasDB as $p) {
        $paradas[] = [
            'id'                  => (string)$p['id_parada'],
            'id_pedido'           => (int)$p['id_pedido'],
            'orden'               => (int)$p['orden'],
            'clientDescription'   => $p['nombre'] . ' ' . $p['apellido'],
            'address'             => $p['domicilio'] . ', ' . $p['localidad'],
            'telefono'            => $p['telefono'] ?? '',
            'observaciones'       => $p['observaciones_cliente'] ?? '',
        ];
    }

    $rutas[] = [
        'id'      => (string)$ruta['id_ruta'],
        'nombre'  => 'Ruta ' . ucfirst($ruta['turno']) . ' — ' . date('d/m/Y', strtotime($ruta['fecha_planificada'])),
        'fecha'   => date('d/m/Y', strtotime($ruta['fecha_planificada'])),
        'turno'   => $ruta['turno'],
        'estado'  => $ruta['estado'],
        'repartidor' => $ruta['repartidor'],
        'observaciones' => $ruta['observaciones'] ?? '',
        'paradas' => $paradas,
    ];
}

apiOk(['rutas' => $rutas]);
