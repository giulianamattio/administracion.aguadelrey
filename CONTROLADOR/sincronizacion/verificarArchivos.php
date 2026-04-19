<?php
// CONTROLADOR/sincronizacion/verificarArchivos.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

header('Content-Type: application/json; charset=utf-8');

$dir = $_SERVER['DOCUMENT_ROOT'] . '/imports/';

function infoArchivo(string $ruta, string $nombre): array {
    if (!file_exists($ruta)) {
        return ['existe' => false, 'nombre' => $nombre];
    }
    // Contar filas (sin encabezado)
    $filas = 0;
    $handle = fopen($ruta, 'r');
    $primera = true;
    while (fgetcsv($handle) !== false) {
        if ($primera) { $primera = false; continue; }
        $filas++;
    }
    fclose($handle);

    $bytes = filesize($ruta);
    $peso  = $bytes < 1024
        ? $bytes . ' B'
        : ($bytes < 1048576
            ? round($bytes / 1024, 1) . ' KB'
            : round($bytes / 1048576, 2) . ' MB');

    return [
        'existe' => true,
        'nombre' => $nombre,
        'filas'  => $filas,
        'peso'   => $peso,
    ];
}

// Última sincronización (guardada en un archivo de log simple)
$logFile    = $_SERVER['DOCUMENT_ROOT'] . '/imports/.ultima_sync';
$ultimaSync = file_exists($logFile) ? file_get_contents($logFile) : null;

echo json_encode([
    'productos'   => infoArchivo($dir . 'productos.csv', 'productos.csv'),
    'clientes'    => infoArchivo($dir . 'clientes.csv',  'clientes.csv'),
    'ultima_sync' => $ultimaSync,
]);
?>
