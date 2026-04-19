<?php
// CONTROLADOR/sincronizacion/sincronizarProductos.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

header('Content-Type: application/json; charset=utf-8');

$archivo = $_SERVER['DOCUMENT_ROOT'] . '/imports/productos.csv';

if (!file_exists($archivo)) {
    echo json_encode(['error' => 'No se encontró el archivo productos.csv en /imports/']);
    exit;
}

$log          = [];
$nuevos       = 0;
$actualizados = 0;
$bajas        = 0;
$errores      = 0;

function logEntry(array &$log, string $msg, string $tipo = 'info'): void {
    $log[] = ['msg' => $msg, 'tipo' => $tipo];
}

try {
    // Statements reutilizables
    $stmtBuscar = $conexionbd->prepare('
        SELECT id_producto, nombre, precio_unitario, fecha_baja
        FROM Producto
        WHERE id_producto = :id
    ');

    $stmtInsertar = $conexionbd->prepare('
        INSERT INTO Producto (id_producto, nombre, precio_unitario, fecha_baja)
        VALUES (:id, :nombre, :precio, :fecha_baja)
    ');

    $stmtActualizar = $conexionbd->prepare('
        UPDATE Producto
        SET nombre     = :nombre,
            precio_unitario     = :precio,
            fecha_baja = :fecha_baja
        WHERE id_producto = :id
    ');

    // Leer CSV
    $handle  = fopen($archivo, 'r');
    $headers = fgetcsv($handle); // primera fila = encabezados

    // Normalizar encabezados (trim + lowercase)
    $headers = array_map(fn($h) => strtolower(trim(mb_convert_encoding($h, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252'))), $headers);

    $fila = 1;
    $conexionbd->beginTransaction();

    while (($row = fgetcsv($handle)) !== false) {
        $fila++;

        if (count($row) !== count($headers)) {
            logEntry($log, "Fila $fila: columnas incorrectas, se omite.", 'warn');
            $errores++;
            continue;
        }

        $data = array_combine(
                $headers,
                array_map(fn($v) => mb_convert_encoding(trim($v), 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252'), $row)
            );

        // Validaciones básicas
        if (empty($data['id_producto']) || !is_numeric($data['id_producto'])) {
            logEntry($log, "Fila $fila: id_producto inválido ({$data['id_producto']}), se omite.", 'error');
            $errores++;
            continue;
        }
        if (empty($data['nombre'])) {
            logEntry($log, "Fila $fila: nombre vacío, se omite.", 'error');
            $errores++;
            continue;
        }
        if (!is_numeric($data['precio'])) {
            logEntry($log, "Fila $fila: precio inválido ({$data['precio']}), se omite.", 'error');
            $errores++;
            continue;
        }

        $id        = (int)   $data['id_producto'];
        $nombre    = (string)$data['nombre'];
        $precio    = (float) $data['precio'];
        $fechaBaja = !empty($data['fecha_baja']) && $data['fecha_baja'] !== 'NULL'
            ? $data['fecha_baja']
            : null;

        // Buscar en la base
        $stmtBuscar->execute([':id' => $id]);
        $existente = $stmtBuscar->fetch();

        if (!$existente) {
            // ── INSERT ──
            $stmtInsertar->execute([
                ':id'         => $id,
                ':nombre'     => $nombre,
                ':precio'     => $precio,
                ':fecha_baja' => $fechaBaja,
            ]);
            logEntry($log, "Fila $fila: ✅ Producto #$id '$nombre' insertado.", 'ok');
            $nuevos++;

        } else {
            // ── Detectar cambios ──
            $cambios = [];
            if (trim($existente['nombre']) !== $nombre)                        $cambios[] = 'nombre';
            if ((float)$existente['precio'] !== $precio)                       $cambios[] = 'precio';
            if (($existente['fecha_baja'] ?? null) !== $fechaBaja)             $cambios[] = 'fecha_baja';

            if (!empty($cambios)) {
                $stmtActualizar->execute([
                    ':id'         => $id,
                    ':nombre'     => $nombre,
                    ':precio'     => $precio,
                    ':fecha_baja' => $fechaBaja,
                ]);
                $detalle = implode(', ', $cambios);
                if (in_array('fecha_baja', $cambios) && $fechaBaja !== null) {
                    logEntry($log, "Fila $fila: 📅 Producto #$id dado de baja ($fechaBaja).", 'warn');
                    $bajas++;
                } else {
                    logEntry($log, "Fila $fila: 🔄 Producto #$id actualizado [$detalle].", 'info');
                    $actualizados++;
                }
            } else {
                logEntry($log, "Fila $fila: — Producto #$id sin cambios.", 'info');
            }
        }
    }

    fclose($handle);
    $conexionbd->commit();

    // Borrar archivo si todo fue bien
    if ($errores === 0) {
        unlink($archivo);
        logEntry($log, '🗑️  Archivo productos.csv eliminado del servidor.', 'ok');
    } else {
        logEntry($log, "⚠️  El archivo NO fue eliminado porque hubo $errores error(es).", 'warn');
    }

    // Guardar timestamp última sync
    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/imports/.ultima_sync',
        date('d/m/Y H:i:s')
    );

    echo json_encode([
        'nuevos'       => $nuevos,
        'actualizados' => $actualizados,
        'bajas'        => $bajas,
        'errores'      => $errores,
        'log'          => $log,
    ]);

} catch (Exception $e) {
    if (isset($conexionbd) && $conexionbd->inTransaction()) {
        $conexionbd->rollBack();
    }
    echo json_encode([
        'error'  => $e->getMessage(),
        'log'    => $log,
    ]);
}
?>
