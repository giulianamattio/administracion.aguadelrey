<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/maquinas.php
//  Lista máquinas con filtro opcional por estado + paginado
// ============================================================

// Traer todos los estados para el select del filtro
$stmtEstados = $conexionbd->prepare("SELECT id_estado, nombre FROM estado_maquina ORDER BY nombre");
$stmtEstados->execute();
$estados = $stmtEstados->fetchAll();

// Filtro por estado
$filtroEstado = isset($_GET['estado']) && $_GET['estado'] !== '' ? (int)$_GET['estado'] : null;

// Paginado
$porPagina = 10;
$pagActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset    = ($pagActual - 1) * $porPagina;

// WHERE dinámico
$where  = $filtroEstado ? "WHERE m.id_estado = :estado" : "";
$params = $filtroEstado ? [':estado' => $filtroEstado] : [];

// Total de registros
$stmtTotal = $conexionbd->prepare("
    SELECT COUNT(*) 
    FROM maquina_dispensadora m
    INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
    $where
");
$stmtTotal->execute($params);
$totalMaquinas = $stmtTotal->fetchColumn();
$totalPaginas  = ceil($totalMaquinas / $porPagina);

// Query paginada
$stmt = $conexionbd->prepare("
    SELECT m.id_maquina, m.numero_serie, m.numero_precinto, m.marca, e.nombre AS estado
    FROM maquina_dispensadora m
    INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
    $where
    ORDER BY m.created_at DESC
    LIMIT :limite OFFSET :offset
");
$params[':limite'] = $porPagina;
$params[':offset'] = $offset;
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
if ($filtroEstado) $stmt->bindValue(':estado', $filtroEstado, PDO::PARAM_INT);
$stmt->execute();

$listadoMaquinas = $stmt->fetchAll();
?>