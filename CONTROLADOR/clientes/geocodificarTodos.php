<?php
// ============================================================
//  CONTROLADOR/clientes/geocodificarTodos.php
//  Geocodifica en batch todos los clientes sin lat/lng.
//  Acceder UNA SOLA VEZ desde el navegador como admin.
//  URL: /clientes/geocodificarTodos
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/geocodificacion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Geocodificación batch</title>
  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/encabezado.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/menu.php');
  ?>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <h1>Geocodificación de clientes</h1>
      </div>
    </section>
    <section class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
<?php
// Traer clientes sin coordenadas
$stmt = $conexionbd->prepare("
    SELECT id_cliente, nombre, apellido, domicilio, localidad, provincia
    FROM cliente
    WHERE estado = 'activo'
      AND domicilio IS NOT NULL
      AND (latitud IS NULL OR longitud IS NULL)
    ORDER BY id_cliente
");
$stmt->execute();
$sinCoords = $stmt->fetchAll();

if (empty($sinCoords)) {
    echo '<div class="alert alert-success">✅ Todos los clientes activos ya tienen coordenadas.</div>';
} else {
    echo '<p>Geocodificando <strong>' . count($sinCoords) . '</strong> clientes...</p>';
    echo '<table class="table table-sm table-bordered">';
    echo '<thead><tr><th>Cliente</th><th>Domicilio</th><th>Resultado</th></tr></thead><tbody>';

    foreach ($sinCoords as $c) {
        $coords = Geocodificacion::geocodificar(
            $c['domicilio'],
            $c['localidad'] ?: 'San Francisco',
            $c['provincia'] ?: 'Córdoba'
        );

        if ($coords) {
            $stmtUp = $conexionbd->prepare("
                UPDATE cliente SET latitud = :lat, longitud = :lng, updated_at = NOW()
                WHERE id_cliente = :id
            ");
            $stmtUp->execute([
                ':lat' => $coords['lat'],
                ':lng' => $coords['lng'],
                ':id'  => $c['id_cliente'],
            ]);
            $resultado = '✅ ' . $coords['lat'] . ', ' . $coords['lng'];
        } else {
            $resultado = '❌ No encontrado';
        }

        echo '<tr>';
        echo '<td>' . htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) . '</td>';
        echo '<td>' . htmlspecialchars($c['domicilio']) . '</td>';
        echo '<td>' . $resultado . '</td>';
        echo '</tr>';

        // Respetar límite de Nominatim: 1 req/segundo
        sleep(1);
    }

    echo '</tbody></table>';
    echo '<div class="alert alert-success mt-3">✅ Proceso completado.</div>';
}
?>
            <a href="/clientes/listaDeEspera" class="btn btn-default mt-2">Volver</a>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>
</body>
</html>
