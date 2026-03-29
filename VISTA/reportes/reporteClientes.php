<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Reporte por Cliente';
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>
  <link rel="Agua del rey" href="/favicon.ico">
  <style>
    .table td, .table th { border-top: none; }
    .table thead th { border-bottom: none; }
  </style>
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
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><?=$pagina?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Reportes</a></li>
              <li class="breadcrumb-item active"><?=$pagina?></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">

              <!-- Filtros -->
              <div class="card-header">
                <form method="GET" action="" class="form-inline flex-wrap" style="gap: 10px;">

                <!-- Cliente -->
                <div class="form-group">
                <label class="mr-2">Cliente:</label>
                <select name="cliente" class="form-control form-control-sm" style="min-width: 200px;">
                    <option value="">Todos</option>
                    <?php
                    $stmtClientes = $conexionbd->prepare("SELECT id_cliente, nombre, apellido FROM cliente WHERE fecha_baja IS NULL ORDER BY apellido, nombre");
                    $stmtClientes->execute();
                    foreach($stmtClientes->fetchAll() as $c):
                    ?>
                    <option value="<?= $c['id_cliente'] ?>" <?= ($_GET['cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>>
                        <?= $c['apellido'] ?>, <?= $c['nombre'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                </div>

                  <div class="form-group">
                    <label class="mr-2">Período:</label>
                    <select name="periodo" id="periodo" class="form-control form-control-sm" onchange="toggleFechas()">
                      <option value="diario"  <?= ($_GET['periodo'] ?? '') === 'diario'  ? 'selected' : '' ?>>Diario</option>
                      <option value="semanal" <?= ($_GET['periodo'] ?? '') === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                      <option value="mensual" <?= ($_GET['periodo'] ?? '') === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                      <option value="rango"   <?= ($_GET['periodo'] ?? '') === 'rango'   ? 'selected' : '' ?>>Rango personalizado</option>
                    </select>
                  </div>

                  <!-- Diario -->
                  <div class="form-group" id="filtro-diario" style="display:none;">
                    <label class="mr-2">Fecha:</label>
                    <input type="date" name="fecha_dia" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['fecha_dia'] ?? date('Y-m-d')) ?>">
                  </div>

                  <!-- Semanal -->
                  <div class="form-group" id="filtro-semanal" style="display:none;">
                    <label class="mr-2">Semana del:</label>
                    <input type="date" name="fecha_semana" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['fecha_semana'] ?? date('Y-m-d')) ?>">
                  </div>

                  <!-- Mensual -->
                  <div class="form-group" id="filtro-mensual" style="display:none;">
                    <label class="mr-2">Mes:</label>
                    <input type="month" name="fecha_mes" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['fecha_mes'] ?? date('Y-m')) ?>">
                  </div>

                  <!-- Rango -->
                  <div class="form-group" id="filtro-rango" style="display:none;">
                    <label class="mr-2">Desde:</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
                    <label class="mx-2">Hasta:</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
                  </div>

                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Buscar
                  </button>
                  <a href="/pedidos/reporteClientes" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                  </a>

                </form>
              </div>
              <!-- /.card-header -->

              <div class="card-body">
              <?php
              if (isset($_GET['periodo'])):

                $periodo    = $_GET['periodo'];
                $params     = [];
                $whereFecha = '';

                switch ($periodo) {
                  case 'diario':
                    $dia        = $_GET['fecha_dia'] ?? date('Y-m-d');
                    $whereFecha = "AND DATE(p.fecha_pedido) = :fecha1";
                    $params[':fecha1'] = $dia;
                    break;

                  case 'semanal':
                    $diaBase    = $_GET['fecha_semana'] ?? date('Y-m-d');
                    $lunes      = date('Y-m-d', strtotime('monday this week', strtotime($diaBase)));
                    $domingo    = date('Y-m-d', strtotime('sunday this week', strtotime($diaBase)));
                    $whereFecha = "AND DATE(p.fecha_pedido) BETWEEN :fecha1 AND :fecha2";
                    $params[':fecha1'] = $lunes;
                    $params[':fecha2'] = $domingo;
                    break;

                  case 'mensual':
                    $mes        = $_GET['fecha_mes'] ?? date('Y-m');
                    $desde      = $mes . '-01';
                    $hasta      = date('Y-m-t', strtotime($desde));
                    $whereFecha = "AND DATE(p.fecha_pedido) BETWEEN :fecha1 AND :fecha2";
                    $params[':fecha1'] = $desde;
                    $params[':fecha2'] = $hasta;
                    break;

                  case 'rango':
                    $desde = $_GET['fecha_desde'] ?? '';
                    $hasta = $_GET['fecha_hasta'] ?? '';
                    if ($desde && $hasta) {
                      $whereFecha = "AND DATE(p.fecha_pedido) BETWEEN :fecha1 AND :fecha2";
                      $params[':fecha1'] = $desde;
                      $params[':fecha2'] = $hasta;
                    }
                    break;
                }




                $whereCliente = '';



                if (!empty($_GET['cliente']) && is_numeric($_GET['cliente'])) {
                    $whereCliente = "AND c.id_cliente = :id_cliente";
                    $params[':id_cliente'] = $_GET['cliente'];
                }

                $sql = "
                    SELECT 
                        c.id_cliente,
                        c.nombre,
                        c.apellido,
                        COUNT(p.id_pedido)  AS cantidad_pedidos,
                        SUM(p.total)        AS total_gastado,
                        AVG(p.total)        AS promedio_pedido
                    FROM pedido p
                    INNER JOIN cliente c ON c.id_cliente = p.id_cliente
                    WHERE p.fecha_baja IS NULL
                    $whereFecha
                    $whereCliente
                    GROUP BY c.id_cliente, c.nombre, c.apellido
                    ORDER BY total_gastado DESC
                    ";

                $stmt = $conexionbd->prepare($sql);
                $stmt->execute($params);
                $listaClientes = $stmt->fetchAll();

                $totalClientes = count($listaClientes);
                $sumaTotalGeneral = array_sum(array_column($listaClientes, 'total_gastado'));
              ?>

                <table class="table table-bordered table-hover table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>#</th>
                      <th>Cliente</th>
                      <th class="text-center">Cantidad de pedidos</th>
                      <th class="text-right">Total gastado</th>
                      <th class="text-right">Promedio por pedido</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if ($totalClientes === 0): ?>
                    <tr>
                      <td colspan="5" class="text-center text-muted py-3">
                        No se encontraron pedidos para el período seleccionado.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach($listaClientes as $i => $row): ?>
                    <tr>
                      <td><?= $i + 1 ?></td>
                      <td><?= $row['nombre'] . ' ' . $row['apellido'] ?></td>
                      <td class="text-center"><?= $row['cantidad_pedidos'] ?></td>
                      <td class="text-right">$ <?= number_format($row['total_gastado'], 2, ',', '.') ?></td>
                      <td class="text-right">$ <?= number_format($row['promedio_pedido'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  </tbody>

                  <?php if ($totalClientes > 0): ?>
                  <tfoot>
                    <tr class="table-secondary font-weight-bold">
                      <td colspan="2" class="text-right">Totales:</td>
                      <td class="text-center"><?= array_sum(array_column($listaClientes, 'cantidad_pedidos')) ?> pedidos</td>
                      <td class="text-right">$ <?= number_format($sumaTotalGeneral, 2, ',', '.') ?></td>
                      <td class="text-right text-muted">—</td>
                    </tr>
                  </tfoot>
                  <?php endif; ?>

                </table>

              <?php else: ?>
                <p class="text-muted text-center mt-3">
                  Seleccioná un período y presioná Buscar para ver los resultados.
                </p>
              <?php endif; ?>
              </div>
              <!-- /.card-body -->

            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>
<script>
function toggleFechas() {
  var periodo = document.getElementById('periodo').value;
  document.getElementById('filtro-diario').style.display  = periodo === 'diario'  ? 'flex' : 'none';
  document.getElementById('filtro-semanal').style.display = periodo === 'semanal' ? 'flex' : 'none';
  document.getElementById('filtro-mensual').style.display = periodo === 'mensual' ? 'flex' : 'none';
  document.getElementById('filtro-rango').style.display   = periodo === 'rango'   ? 'flex' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleFechas);
</script>
</body>
</html>