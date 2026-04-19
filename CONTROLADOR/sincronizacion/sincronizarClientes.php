<?php
// CONTROLADOR/sincronizacion/sincronizarClientes.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

header('Content-Type: application/json; charset=utf-8');

$archivo = $_SERVER['DOCUMENT_ROOT'] . '/imports/clientes.csv';

if (!file_exists($archivo)) {
    echo json_encode(['error' => 'No se encontró el archivo clientes.csv en /imports/']);
    exit;
}

$log          = [];
$nuevos       = 0;
$actualizados = 0;
$errores      = 0;

function logEntry(array &$log, string $msg, string $tipo = 'info'): void {
    $log[] = ['msg' => $msg, 'tipo' => $tipo];
}

try {
    // Statements reutilizables — clave: DNI
    $stmtBuscar = $conexionbd->prepare('
        SELECT id_cliente, nombre, apellido, dni, telefono, email, domicilio, localidad, provincia
        FROM Cliente
        WHERE dni = :dni
    ');

    $stmtInsertar = $conexionbd->prepare('
        INSERT INTO Cliente (nombre, apellido, dni, telefono, email, domicilio, localidad, provincia)
        VALUES (:nombre, :apellido, :dni, :telefono, :email, :domicilio, :localidad, :provincia)
    ');

    $stmtActualizar = $conexionbd->prepare('
        UPDATE Cliente
        SET nombre    = :nombre,
            apellido  = :apellido,
            telefono  = :telefono,
            email     = :email,
            domicilio = :domicilio,
            localidad = :localidad,
            provincia = :provincia
        WHERE dni = :dni
    ');

    // Leer CSV
    $handle  = fopen($archivo, 'r');
    $headers = fgetcsv($handle);
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
        if (empty($data['dni'])) {
            logEntry($log, "Fila $fila: DNI vacío, se omite.", 'error');
            $errores++;
            continue;
        }
        if (empty($data['nombre']) || empty($data['apellido'])) {
            logEntry($log, "Fila $fila: nombre o apellido vacío (DNI: {$data['dni']}), se omite.", 'error');
            $errores++;
            continue;
        }

        $dni      = (string)$data['dni'];
        $nombre   = (string)$data['nombre'];
        $apellido = (string)$data['apellido'];
        $telefono = $data['telefono']  ?? null;
        $email    = $data['email']     ?? null;
        $domicilio= $data['domicilio'] ?? null;
        $localidad= $data['localidad'] ?? null;
        $provincia= $data['provincia'] ?? null;

        // Buscar por DNI
        $stmtBuscar->execute([':dni' => $dni]);
        $existente = $stmtBuscar->fetch();

        if (!$existente) {
            // ── INSERT ──
            $stmtInsertar->execute([
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':dni'       => $dni,
                ':telefono'  => $telefono,
                ':email'     => $email,
                ':domicilio' => $domicilio,
                ':localidad' => $localidad,
                ':provincia' => $provincia,
            ]);
            logEntry($log, "Fila $fila: ✅ Cliente $nombre $apellido (DNI $dni) insertado.", 'ok');
            $nuevos++;

        } else {
            // ── Detectar cambios ──
            $campos  = ['nombre','apellido','telefono','email','domicilio','localidad','provincia'];
            $valores = compact('nombre','apellido','telefono','email','domicilio','localidad','provincia');
            $cambios = [];

            foreach ($campos as $campo) {
                $viejo = $existente[$campo] ?? null;
                $nuevo = $valores[$campo]   ?? null;
                if ($viejo !== $nuevo) $cambios[] = $campo;
            }

            if (!empty($cambios)) {
                $stmtActualizar->execute([
                    ':dni'       => $dni,
                    ':nombre'    => $nombre,
                    ':apellido'  => $apellido,
                    ':telefono'  => $telefono,
                    ':email'     => $email,
                    ':domicilio' => $domicilio,
                    ':localidad' => $localidad,
                    ':provincia' => $provincia,
                ]);
                $detalle = implode(', ', $cambios);
                logEntry($log, "Fila $fila: 🔄 Cliente DNI $dni actualizado [$detalle].", 'info');
                $actualizados++;
            } else {
                logEntry($log, "Fila $fila: — Cliente DNI $dni sin cambios.", 'info');
            }
        }
    }

    fclose($handle);
    $conexionbd->commit();

    // Borrar archivo si no hubo errores
    if ($errores === 0) {
        unlink($archivo);
        logEntry($log, '🗑️  Archivo clientes.csv eliminado del servidor.', 'ok');
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
        'errores'      => $errores,
        'log'          => $log,
    ]);

} catch (Exception $e) {
    if (isset($conexionbd) && $conexionbd->inTransaction()) {
        $conexionbd->rollBack();
    }
    echo json_encode([
        'error' => $e->getMessage(),
        'log'   => $log,
    ]);
}
?>
