<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Productos más vendidos';
require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');

// Filtro de fechas con default últimos 3 meses
$desde = $_GET['fecha_desde'] ?? date('Y-m-d', strtotime('-3 months'));
$hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');

// Validar que desde no sea mayor que hasta
if ($desde > $hasta) {
    $temp  = $desde;
    $desde = $hasta;
    $hasta = $temp;
}

$sql = " SELECT 
      pr.nombre,
      SUM(pp.cantidad) AS total_vendido
    FROM pedido_producto pp
    INNER JOIN producto pr ON pr.id_producto = pp.id_producto
    INNER JOIN pedido p    ON p.id_pedido    = pp.id_pedido
    WHERE pp.fecha_baja IS NULL
      AND p.fecha_baja  IS NULL
      AND p.id_estado  != 4
      AND DATE(p.fecha_pedido) BETWEEN :desde AND :hasta
    GROUP BY pr.nombre
    ORDER BY total_vendido DESC
  ";

  $stmt = $conexionbd->prepare($sql);
  $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);
  $listaProductos = $stmt->fetchAll();

  // Preparar datos para Chart.js
  $labels    = [];
  $datos     = [];
  $totalUnidades = 0;

  foreach ($listaProductos as $row) {
    $labels[] = $row['nombre'];
    $datos[]  = (int) $row['total_vendido'];
    $totalUnidades += (int) $row['total_vendido'];
  }

  $labelsJson = json_encode($labels);
  $datosJson  = json_encode($datos);
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>
  <link rel="Agua del rey" href="/favicon.ico">
  <style>
    .table td, .table th { border-top: none; }
    .table thead th { border-bottom: none; }
    .chart-container {
      position: relative;
      max-width: 420px;
      margin: 0 auto;
    }
    .color-dot {
      display: inline-block;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 6px;
    }
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


      <!-- Filtros -->
<div class="row mb-3">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <form method="GET" action="" class="form-inline flex-wrap" style="gap: 10px;">

          <div class="form-group">
            <label class="mr-2">Desde:</label>
            <input type="date" name="fecha_desde" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($desde) ?>">
          </div>

          <div class="form-group">
            <label class="mr-2">Hasta:</label>
            <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($hasta) ?>">
          </div>

          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-search"></i> Buscar
          </button>
          <a href="/pedidos/reporteProductosMasVendidos" class="btn btn-secondary btn-sm">
            <i class="fas fa-times"></i> Limpiar
          </a>

        </form>
      </div>
    </div>
  </div>
</div>
        <!-- Encabezado del período -->
        <div class="row mb-3">
          <div class="col-md-12">
            <small class="text-muted">
              <i class="fas fa-calendar-alt"></i>
              Período: <strong><?= date('d/m/Y', strtotime($desde)) ?></strong> al <strong><?= date('d/m/Y', strtotime($hasta)) ?></strong>
              &nbsp;·&nbsp; Total unidades vendidas: <strong><?= $totalUnidades ?></strong>
            </small>
          </div>
        </div>

        <div class="row">

          <!-- Gráfico de torta -->
          <div class="col-md-5">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Distribución</h3>
              </div>
              <div class="card-body">
                <?php if (empty($listaProductos)): ?>
                  <p class="text-muted text-center py-4">No hay datos para el período seleccionado.</p>
                <?php else: ?>
                  <div class="chart-container">
                    <canvas id="graficoPie"></canvas>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Tabla detalle -->
          <div class="col-md-7">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Detalle por producto</h3>
              </div>
              <div class="card-body p-0">
                <?php if (empty($listaProductos)): ?>
                  <p class="text-muted text-center py-4">No hay datos para el período seleccionado.</p>
                <?php else: ?>
                  <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-center">Unidades</th>
                        <th class="text-center">% del total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($listaProductos as $i => $row): 
                        $porcentaje = $totalUnidades > 0 
                          ? round(($row['total_vendido'] / $totalUnidades) * 100, 1) 
                          : 0;
                      ?>
                      <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                          <span class="color-dot" id="dot-<?= $i ?>"></span>
                          <?= htmlspecialchars($row['nombre']) ?>
                        </td>
                        <td class="text-center"><strong><?= $row['total_vendido'] ?></strong></td>
                        <td class="text-center">
                          <div class="progress" style="height: 16px;">
                            <div class="progress-bar" id="bar-<?= $i ?>"
                                 role="progressbar" 
                                 style="width: <?= $porcentaje ?>%">
                              <?= $porcentaje ?>%
                            </div>
                          </div>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                      <tr class="table-secondary font-weight-bold">
                        <td colspan="2" class="text-right">Total:</td>
                        <td class="text-center"><?= $totalUnidades ?></td>
                        <td class="text-center">100%</td>
                      </tr>
                    </tfoot>
                  </table>
                <?php endif; ?>
              </div>
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
<?php if (!empty($listaProductos)): ?>

const colores = [
  '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b',
  '#858796','#5a5c69','#2ecc71','#e67e22','#9b59b6',
  '#1abc9c','#e91e63','#ff5722','#607d8b','#795548'
];

// Asignar colores a los dots de la tabla
<?php foreach($listaProductos as $i => $row): ?>
  document.getElementById('dot-<?= $i ?>').style.backgroundColor = colores[<?= $i ?> % colores.length];
  document.getElementById('bar-<?= $i ?>').style.backgroundColor  = colores[<?= $i ?> % colores.length];
<?php endforeach; ?>

// Gráfico de torta
const ctx = document.getElementById('graficoPie').getContext('2d');
new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: <?= $labelsJson ?>,
    datasets: [{
      data: <?= $datosJson ?>,
      backgroundColor: colores.slice(0, <?= count($listaProductos) ?>),
      borderWidth: 2,
      borderColor: '#fff'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { padding: 15, font: { size: 12 } }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const pct   = ((context.parsed / total) * 100).toFixed(1);
            return ` ${context.label}: ${context.parsed} uds (${pct}%)`;
          }
        }
      }
    }
  }
});
<?php endif; ?>
</script>
</body>
</html>