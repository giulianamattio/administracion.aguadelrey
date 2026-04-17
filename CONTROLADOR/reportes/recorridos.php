<?php
// CONTROLADOR/reportes/recorridos.ph
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

header('Content-Type: application/json; charset=utf-8');

// ── Parámetros ──────────────────────────────────────────────────────────────
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

if (!$desde || !$hasta) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros desde/hasta']);
    exit;
}

// Validación básica de formato de fecha (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de fecha inválido. Use YYYY-MM-DD']);
    exit;
}

try {
    /*
     * Columnas reales de v_recorridos_costo:
     *   id_ruta, fecha_planificada, id_repartidor, turno, estado,
     *   km_recorridos, precio_por_litro, litros_usados,
     *   costo_combustible, total_pedidos, costo_por_pedido
     *
     * Se hace JOIN con la tabla de repartidores para traer el nombre.
     * Si no existe esa tabla/columna, reemplazá id_repartidor::text por
     * el nombre real que uses en tu esquema.
     */
    $sql = "
        SELECT
            v.id_ruta,
            v.fecha_planificada::date                        AS fecha,

            -- Nombre del repartidor: ajustá el JOIN si tu tabla tiene otro nombre
            COALESCE(u.nombre || ' ' || u.apellido,
                     v.id_repartidor::text)                  AS repartidor,

            v.turno,
            v.km_recorridos                                  AS km,
            v.litros_usados                                  AS litros,
            v.precio_por_litro                               AS precio,
            v.costo_combustible                              AS costo,
            v.total_pedidos                                  AS pedidos,
            v.costo_por_pedido                               AS cpp

        FROM v_recorridos_costo v

        -- JOIN opcional para resolver el nombre del repartidor
        LEFT JOIN usuario_empleado u ON u.id_empleado = v.id_repartidor

        WHERE v.fecha_planificada::date BETWEEN :desde AND :hasta

        ORDER BY v.fecha_planificada DESC, v.id_ruta DESC
    ";

    $stmt = $conexionbd->prepare($sql);
    $stmt->execute([
        ':desde' => $desde,
        ':hasta' => $hasta,
    ]);

    $rows = $stmt->fetchAll();

    // Forzar tipos numéricos para que el JS reciba números, no strings
    foreach ($rows as &$r) {
        $r['km']      = (float) $r['km'];
        $r['litros']  = (float) $r['litros'];
        $r['precio']  = (float) $r['precio'];
        $r['costo']   = (float) $r['costo'];
        $r['pedidos'] = (int)   $r['pedidos'];
        $r['cpp']     = (float) $r['cpp'];
    }
    unset($r);

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>