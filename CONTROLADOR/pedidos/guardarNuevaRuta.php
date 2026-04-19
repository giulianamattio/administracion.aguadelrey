<?php
// ============================================================
//  CONTROLADOR/pedidos/guardarNuevaRuta.php
//  Crea la ruta y sus paradas ordenadas por número de calle
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

$fecha       = trim($_POST['fecha']         ?? '');
$turno       = trim($_POST['turno']         ?? '');
$repartidor  = (int)($_POST['id_repartidor']?? 0);
$pedidosIds  = $_POST['pedidos']            ?? [];
$obs         = trim($_POST['observaciones'] ?? '');
$kmRecorridos = isset($_POST['km_recorridos']) ? (float)$_POST['km_recorridos'] : 0;


// Validaciones
if (empty($fecha) || empty($turno) || empty($pedidosIds)) {
    header('Location: /pedidos/nuevaRutaReparto?error=Completá+fecha,+turno+y+seleccioná+al+menos+un+pedido.');
    exit;
}

// Verificar que no exista ya una ruta para esa fecha y turno
$stmtCheck = $conexionbd->prepare("
    SELECT id_ruta FROM ruta_reparto
    WHERE fecha_planificada = :fecha AND turno = :turno
    AND estado != 4
");
$stmtCheck->execute([':fecha' => $fecha, ':turno' => $turno]);
if ($stmtCheck->fetch()) {
    header('Location: /pedidos/nuevaRutaReparto?error=Ya+existe+una+ruta+para+esa+fecha+y+turno.');
    exit;
}

// Traer domicilios de los pedidos seleccionados para ordenar
$inPlaceholders = implode(',', array_fill(0, count($pedidosIds), '?'));
$stmtDoms = $conexionbd->prepare("
    SELECT p.id_pedido, c.nombre, c.apellido, c.domicilio
    FROM pedido p
    JOIN cliente c ON c.id_cliente = p.id_cliente
    WHERE p.id_pedido IN ($inPlaceholders)
");
$stmtDoms->execute(array_map('intval', $pedidosIds));
$pedidosData = $stmtDoms->fetchAll();

// ── Algoritmo de ordenamiento por número de calle ────────────
// Extrae el número de la dirección y ordena de menor a mayor
// Ej: "Mitre 524" → 524, "Bv. Buenos Aires 1200" → 1200
// Esto simula una ruta lineal óptima para MVP académico
function extraerNumero(string $domicilio): int {
    preg_match('/\d+/', $domicilio, $matches);
    return isset($matches[0]) ? (int)$matches[0] : 9999;
}

usort($pedidosData, function($a, $b) {
    return extraerNumero($a['domicilio']) - extraerNumero($b['domicilio']);
});
// ─────────────────────────────────────────────────────────────

// Insertar la ruta
$stmtRuta = $conexionbd->prepare("
    INSERT INTO ruta_reparto
        (id_repartidor, estado, fecha_planificada, turno, observaciones, created_at, updated_at, km_recorridos)
    VALUES
        (:rep, 1, :fecha, :turno, :obs, NOW(), NOW(), :kmRecorridos)
    RETURNING id_ruta
");
$stmtRuta->execute([
    ':rep'   => $repartidor ?: null,
    ':fecha' => $fecha,
    ':turno' => $turno,
    ':obs'   => $obs ?: null,
    ':kmRecorridos'   => $kmRecorridos ?: null,
]);
$idRuta = $stmtRuta->fetchColumn();

// Insertar cada parada con su orden calculado
$stmtParada = $conexionbd->prepare("
    INSERT INTO parada_ruta (id_ruta, id_pedido, orden)
    VALUES (:id_ruta, :id_pedido, :orden)
");
foreach ($pedidosData as $posicion => $pedido) {
    $stmtParada->execute([
        ':id_ruta'  => $idRuta,
        ':id_pedido'=> $pedido['id_pedido'],
        ':orden'    => $posicion + 1,
    ]);
}

// Marcar pedidos como en_ruta (estado 2)
$stmtEstado = $conexionbd->prepare("
    UPDATE pedido SET id_estado = 2, updated_at = NOW()
    WHERE id_pedido = :id
");
foreach ($pedidosIds as $idPedido) {
    $stmtEstado->execute([':id' => (int)$idPedido]);
}

header('Location: /pedidos/gestionarRutaRepartos?ok=creada');
exit;
