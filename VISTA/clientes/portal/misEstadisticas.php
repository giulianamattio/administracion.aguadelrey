<?php
// ============================================================
//  VISTA/clientes/portal/misEstadisticas.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$idCliente = $_SESSION['cliente_id'];
$desde     = date('Y-m-d', strtotime('-1 year'));
$hasta     = date('Y-m-d');

// 1. Productos más consumidos (torta)
$stmtProductos = $conexionbd->prepare("
    SELECT pr.nombre, SUM(pp.cantidad) AS total
    FROM pedido_producto pp
    INNER JOIN producto pr ON pr.id_producto = pp.id_producto
    INNER JOIN pedido p    ON p.id_pedido    = pp.id_pedido
    WHERE p.id_cliente  = :id_cliente
      AND p.fecha_baja  IS NULL
      AND pp.fecha_baja IS NULL
      AND p.id_estado  != 4
      AND DATE(p.fecha_pedido) BETWEEN :desde AND :hasta
    GROUP BY pr.nombre
    ORDER BY total DESC
");
$stmtProductos->execute([':id_cliente' => $idCliente, ':desde' => $desde, ':hasta' => $hasta]);
$productosConsumo = $stmtProductos->fetchAll();

// 2. Pedidos por mes
$stmtMeses = $conexionbd->prepare("
    SELECT TO_CHAR(fecha_pedido, 'Mon YYYY') AS mes,
           TO_CHAR(fecha_pedido, 'YYYY-MM')  AS mes_orden,
           COUNT(*) AS cantidad
    FROM pedido
    WHERE id_cliente  = :id_cliente
      AND fecha_baja  IS NULL
      AND id_estado  != 4
      AND DATE(fecha_pedido) BETWEEN :desde AND :hasta
    GROUP BY mes, mes_orden
    ORDER BY mes_orden ASC
");
$stmtMeses->execute([':id_cliente' => $idCliente, ':desde' => $desde, ':hasta' => $hasta]);
$pedidosPorMes = $stmtMeses->fetchAll();

// 3. Cálculo de agua diaria (bidones 20L y 10L)
$stmtAgua = $conexionbd->prepare("
    SELECT pr.nombre, SUM(pp.cantidad) AS total
    FROM pedido_producto pp
    INNER JOIN producto pr ON pr.id_producto = pp.id_producto
    INNER JOIN pedido p    ON p.id_pedido    = pp.id_pedido
    WHERE p.id_cliente  = :id_cliente
      AND p.fecha_baja  IS NULL
      AND pp.fecha_baja IS NULL
      AND p.id_estado  != 4
      AND DATE(p.fecha_pedido) BETWEEN :desde AND :hasta
      AND (pr.nombre ILIKE '%20L%' OR pr.nombre ILIKE '%12L%' OR pr.nombre ILIKE '%10L%')
    GROUP BY pr.nombre
");
$stmtAgua->execute([':id_cliente' => $idCliente, ':desde' => $desde, ':hasta' => $hasta]);
$consumoAgua = $stmtAgua->fetchAll();

$totalLitros = 0;
foreach ($consumoAgua as $item) {
    if (stripos($item['nombre'], '20L') !== false) {
        $totalLitros += $item['total'] * 20;
    } elseif (stripos($item['nombre'], '12L') !== false) {
        $totalLitros += $item['total'] * 12;
    } elseif (stripos($item['nombre'], '10L') !== false) {
        $totalLitros += $item['total'] * 10;
    }
}


// Traer fecha del primer pedido del cliente
$stmtPrimerPedido = $conexionbd->prepare("
    SELECT MIN(fecha_pedido) AS primer_pedido
    FROM pedido
    WHERE id_cliente = :id_cliente
      AND fecha_baja IS NULL
      AND id_estado != 4
");
$stmtPrimerPedido->execute([':id_cliente' => $idCliente]);
$primerPedido = $stmtPrimerPedido->fetchColumn();

// Calcular días desde el primer pedido hasta hoy
if ($primerPedido) {
    $diasDesde = max(1, (int) floor((strtotime($hasta) - strtotime($primerPedido)) / 86400));
} else {
    $diasDesde = 365;
}

$litrosPorDia = $totalLitros > 0 ? round($totalLitros / $diasDesde, 1) : 0;

// Preparar datos para Chart.js
$labelsProductos = json_encode(array_column($productosConsumo, 'nombre'));
$datosProductos  = json_encode(array_map(fn($p) => (int)$p['total'], $productosConsumo));
$labelsMeses     = json_encode(array_column($pedidosPorMes, 'mes'));
$datosMeses      = json_encode(array_map(fn($m) => (int)$m['cantidad'], $pedidosPorMes));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Mis Estadísticas</title>
  <style>
    .stat-card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .stat-card .card-header {
      border-radius: 12px 12px 0 0;
      font-weight: 600;
    }
    .agua-numero {
      font-size: 52px;
      font-weight: 700;
      line-height: 1;
    }
    .agua-unidad {
      font-size: 20px;
      font-weight: 400;
      color: #6c757d;
    }
    .sin-datos {
      text-align: center;
      padding: 40px 0;
      color: #adb5bd;
      font-size: 14px;
    }
  </style>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/navbar.php'); ?>

<div class="portal-content">
  <div class="container">

     <div class="card shadow-sm" style="border-radius: 10px; border: none;">
      <div class="card-header bg-rojo text-white" style="border-radius: 10px 10px 0 0;">
        <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Mis estadísticas</h5>
      </div>


      <div> &emsp; <small class="text-muted">
          <i class="fas fa-calendar-alt mr-1"></i>
          Último año: <?= date('d/m/Y', strtotime($desde)) ?> al <?= date('d/m/Y', strtotime($hasta)) ?>
      </small></div>

      <div class="card-body p-4">

    <!-- Encabezado 
    <div class="row mb-4">
      <div class="col-12">
        <h4 class="font-weight-bold">
          <i class="fas fa-chart-bar mr-2"></i>Mis Estadísticas
        </h4>
        <small class="text-muted">
          <i class="fas fa-calendar-alt mr-1"></i>
          Último año: <?= date('d/m/Y', strtotime($desde)) ?> al <?= date('d/m/Y', strtotime($hasta)) ?>
        </small>
      </div>
    </div>-->

        <!-- Fila 1: Torta + Agua diaria -->
        <div class="row mb-4">

        <!-- Productos más consumidos -->
        <div class="col-md-7 mb-4">
            <div class="card stat-card h-100">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-chart-pie mr-2"></i>Productos más consumidos
            </div>
            <div class="card-body">
                <?php if (empty($productosConsumo)): ?>
                <div class="sin-datos">
                    <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                    Todavía no tenés consumo registrado.
                </div>
                <?php else: ?>
                <canvas id="graficoPie" style="max-height: 280px;"></canvas>
                <div class="mt-3">
                    <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <?php
                        $colores = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'];
                        foreach ($productosConsumo as $i => $prod):
                        $color = $colores[$i % count($colores)];
                        ?>
                        <tr>
                        <td style="width:16px;">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?= $color ?>"></span>
                        </td>
                        <td style="font-size:13px;"><?= htmlspecialchars($prod['nombre']) ?></td>
                        <td class="text-right font-weight-bold" style="font-size:13px;"><?= $prod['total'] ?> unidades</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>

        <!-- Agua diaria -->
        <div class="col-md-5 mb-4">
            <div class="card stat-card h-100">
            <div class="card-header bg-info text-white">
                <i class="fas fa-tint mr-2"></i>Tu consumo de agua
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center py-4">
                <?php if ($litrosPorDia > 0): ?>
                <div class="agua-numero text-info"><?= $litrosPorDia ?></div>
                <div class="agua-unidad mb-3">litros por día</div>
                <p class="text-muted mb-2" style="font-size:13px;">
                    Basado en <strong><?= $totalLitros ?> litros</strong> consumidos
                    desde el <strong><?= date('d/m/Y', strtotime($primerPedido)) ?></strong>
                    (<?= $diasDesde ?> días).
                </p>
                <hr class="w-100">
                <?php
                // Desglose por tipo de bidón
                foreach ($consumoAgua as $item):
                    //$litros = stripos($item['nombre'], '20L') !== false ? 20 : 12;
                    $litros = stripos($item['nombre'], '20L') !== false ? 20 
                              : (stripos($item['nombre'], '12 L') !== false ? 12
                               : 10);
                ?>
                <div class="d-flex justify-content-between w-100 px-2" style="font-size:13px;">
                    <span><?= htmlspecialchars($item['nombre']) ?></span>
                    <span class="font-weight-bold"><?= $item['total'] ?> unidades = <?= $item['total'] * $litros ?>L</span>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="sin-datos">
                    <i class="fas fa-tint fa-2x mb-2 d-block" style="color:#dee2e6"></i>
                    No hay consumo de bidones registrado.
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>

        </div>

        <!-- Fila 2: Pedidos por mes -->
        <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-calendar-check mr-2"></i>Pedidos por mes
            </div>
            <div class="card-body">
                <?php if (empty($pedidosPorMes)): ?>
                <div class="sin-datos">
                    <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                    Todavía no tenés pedidos registrados.
                </div>
                <?php else: ?>
                <canvas id="graficoBarras" style="max-height: 250px;"></canvas>
                <?php endif; ?>
            </div>
            </div>
        </div>
        </div>

      </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/footer.php'); ?>
<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
var colores = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'];

<?php if (!empty($productosConsumo)): ?>
// Gráfico de torta
new Chart(document.getElementById('graficoPie'), {
  type: 'doughnut',
  data: {
    labels: <?= $labelsProductos ?>,
    datasets: [{
      data: <?= $datosProductos ?>,
      backgroundColor: colores.slice(0, <?= count($productosConsumo) ?>),
      borderWidth: 2,
      borderColor: '#fff'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function(context) {
            var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
            var pct   = ((context.parsed / total) * 100).toFixed(1);
            return ' ' + context.label + ': ' + context.parsed + ' uds. (' + pct + '%)';
          }
        }
      }
    }
  }
});
<?php endif; ?>

<?php if (!empty($pedidosPorMes)): ?>
// Gráfico de barras por mes
new Chart(document.getElementById('graficoBarras'), {
  type: 'bar',
  data: {
    labels: <?= $labelsMeses ?>,
    datasets: [{
      label: 'Pedidos',
      data: <?= $datosMeses ?>,
      backgroundColor: '#1cc88a',
      borderRadius: 6,
      borderSkipped: false
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function(context) {
            return ' ' + context.parsed.y + ' pedido' + (context.parsed.y !== 1 ? 's' : '');
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 },
        grid: { color: 'rgba(0,0,0,0.05)' }
      },
      x: {
        grid: { display: false }
      }
    }
  }
});
<?php endif; ?>
</script>
</body>
</html>