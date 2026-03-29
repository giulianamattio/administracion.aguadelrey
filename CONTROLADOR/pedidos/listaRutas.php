<?php
// ============================================================
//  CONTROLADOR/pedidos/listaRutas.php
//  Trae rutas de reparto con filtros y paginado
// ============================================================

$porPagina = 10;
$pagActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset    = ($pagActual - 1) * $porPagina;

// Filtros
$filtroDesde  = $_GET['fecha_desde'] ?? '';
$filtroHasta  = $_GET['fecha_hasta'] ?? '';
$filtroTurno  = $_GET['turno']       ?? '';
$filtroEstado = $_GET['estado']      ?? '';

$where  = "WHERE 1=1";
$params = [];

if ($filtroDesde) {
    $where .= " AND DATE(r.fecha_planificada) >= :fecha_desde";
    $params[':fecha_desde'] = $filtroDesde;
}
if ($filtroHasta) {
    $where .= " AND DATE(r.fecha_planificada) <= :fecha_hasta";
    $params[':fecha_hasta'] = $filtroHasta;
}
if ($filtroTurno) {
    $where .= " AND r.turno = :turno";
    $params[':turno'] = $filtroTurno;
}
if ($filtroEstado) {
    $where .= " AND r.estado = :estado";
    $params[':estado'] = $filtroEstado;
}

// Total con filtros
$stmtTotal = $conexionbd->prepare("
    SELECT COUNT(DISTINCT r.id_ruta)
    FROM ruta_reparto r
    LEFT JOIN usuario_empleado u ON u.id_empleado = r.id_repartidor
    $where
");
$stmtTotal->execute($params);
$totalRutas  = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRutas / $porPagina);

// Query paginada con filtros
$stmt = $conexionbd->prepare("
    SELECT
        r.id_ruta,
        r.fecha_planificada,
        r.turno,
        r.estado,
        r.observaciones,
        CONCAT(u.nombre, ' ', u.apellido) AS repartidor,
        COUNT(p.id_parada)   AS total_paradas,
        SUM(ped.bidones_vacios) AS total_bidones_vacios
    FROM ruta_reparto r
    LEFT JOIN usuario_empleado u ON u.id_empleado = r.id_repartidor
    LEFT JOIN parada_ruta p      ON p.id_ruta     = r.id_ruta
    LEFT JOIN pedido ped         ON p.id_pedido   = ped.id_pedido
    $where
    GROUP BY r.id_ruta, r.fecha_planificada, r.turno, r.estado,
             r.observaciones, u.nombre, u.apellido
    ORDER BY r.fecha_planificada DESC, r.turno ASC
    LIMIT :limite OFFSET :offset
");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
$stmt->execute();
$rutas = $stmt->fetchAll();

?>