<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Estado de Máquinas Dispensadoras';
require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');

$sql = " SELECT
        e.descripcion AS estado,
        e.nombre AS estado_key,
        COUNT(m.id_maquina) AS total
    FROM maquina_dispensadora m
    INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
    GROUP BY e.descripcion, m.id_estado, e.nombre
    ORDER BY total DESC";

$stmt = $conexionbd->prepare($sql);
$stmt->execute();
$listaMaquinas = $stmt->fetchAll();

$totalMaquinas = array_sum(array_column($listaMaquinas, 'total'));

$labels     = [];
$datos      = [];
foreach ($listaMaquinas as $row) {
    $labels[] = $row['estado'];
    $datos[]  = (int) $row['total'];
}

$labelsJson = json_encode($labels);
$datosJson  = json_encode($datos);

// Colores por estado
$coloresEstado = [
    'disponible'    => '#1cc88a',
    'en_cliente'    => '#4e73df',
    'en_reparacion' => '#f6c23e',
    'baja'          => '#e74a3b',
];
?>
  <title>Agua del Rey | <?= $pagina ?></title>
  <link rel="icon" href="/favicon.ico">
  <style>
    .table td, .table th { border-top: none; }
    .table thead th { border-bottom: none; }
    .chart-container { position: relative; max-width: 420px; margin: 0 auto; }
    .color-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 6px; }
    .stat-box { border-radius: 10px; padding: 20px; text-align: center; color: #fff; }
    .stat-box .numero { font-size: 42px; font-weight: 700; line-height: 1; }
    .stat-box .label  { font-size: 14px; margin-top: 6px; opacity: 0.9; }
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
            <h1><?= $pagina ?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Reportes</a></li>
              <li class="breadcrumb-item active"><?= $pagina ?></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">

        <!-- Encabezado total -->
        <div class="row mb-3">
          <div class="col-md-12">
            <small class="text-muted">
              <i class="fas fa-database mr-1"></i>
              Total de máquinas registradas: <strong><?= $totalMaquinas ?></strong>
            </small>
          </div>
        </div>

        <!-- Cards resumen por estado -->
        <div class="row mb-4">
          <?php foreach($listaMaquinas as $row):
            $key   = $row['estado_key'];
            $color = $coloresEstado[$key] ?? '#858796';
            $icono = match($key) {
              'disponible'    => 'fas fa-check-circle',
              'en_cliente'    => 'fas fa-user',
              'en_reparacion' => 'fas fa-tools',
              'baja'          => 'fas fa-times-circle',
              default         => 'fas fa-circle'
            };
            $porcentaje = $totalMaquinas > 0
              ? round(($row['total'] / $totalMaquinas) * 100, 1)
              : 0;
          ?>
          <div class="col-sm-6 col-md-3 mb-3">
            <div class="stat-box" style="background: <?= $color ?>;">
              <i class="<?= $icono ?> fa-2x mb-2"></i>
              <div class="numero"><?= $row['total'] ?></div>
              <div class="label"><?= htmlspecialchars($row['estado']) ?></div>
              <div style="font-size:12px; opacity:0.8; margin-top:4px;"><?= $porcentaje ?>% del total</div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="row">

          <!-- Gráfico de torta -->
          <div class="col-md-5">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Distribución</h3>
              </div>
              <div class="card-body">
                <?php if (empty($listaMaquinas)): ?>
                  <p class="text-muted text-center py-4">No hay máquinas registradas.</p>
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
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Detalle por estado</h3>
              </div>
              <div class="card-body p-0">
                <?php if (empty($listaMaquinas)): ?>
                  <p class="text-muted text-center py-4">No hay máquinas registradas.</p>
                <?php else: ?>
                  <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Estado</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">% del total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($listaMaquinas as $i => $row):
                        $key        = $row['estado_key'];
                        $color      = $coloresEstado[$key] ?? '#858796';
                        $porcentaje = $totalMaquinas > 0
                          ? round(($row['total'] / $totalMaquinas) * 100, 1)
                          : 0;
                      ?>
                      <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                          <span class="color-dot" style="background:<?= $color ?>"></span>
                          <?= htmlspecialchars($row['estado']) ?>
                        </td>
                        <td class="text-center"><strong><?= $row['total'] ?></strong></td>
                        <td class="text-center">
                          <div class="progress" style="height: 16px;">
                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: <?= $porcentaje ?>%; background: <?= $color ?>;">
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
                        <td class="text-center"><?= $totalMaquinas ?></td>
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
<?php if (!empty($listaMaquinas)): ?>

const coloresGrafico = <?= json_encode(array_map(
    fn($r) => $coloresEstado[$r['estado_key']] ?? '#858796',
    $listaMaquinas
)) ?>;

const ctx = document.getElementById('graficoPie').getContext('2d');
new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: <?= $labelsJson ?>,
    datasets: [{
      data: <?= $datosJson ?>,
      backgroundColor: coloresGrafico,
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
            return ` ${context.label}: ${context.parsed} máquinas (${pct}%)`;
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