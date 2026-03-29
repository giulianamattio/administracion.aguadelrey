<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Reporte de Pedidos';
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>
  <link rel="Agua del rey" href="/favicon.ico">
  <style>
    .table td, .table th { border-top: none; }
    .table thead th { border-bottom: none; }
    .resumen-box {
      background: #f8f9fa;
      border-radius: 6px;
      padding: 12px 20px;
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
              <li class="breadcrumb-item"><a href="#">Pedidos</a></li>
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

                  <!-- Estado -->
                  <div class="form-group">
                    <label class="mr-2">Estado:</label>
                    <select name="estado" class="form-control form-control-sm">
                      <option value="">Todos</option>
                      <option value="1" <?= ($_GET['estado'] ?? '') === '1' ? 'selected' : '' ?>>Pendiente</option>
                      <option value="2" <?= ($_GET['estado'] ?? '') === '2' ? 'selected' : '' ?>>En ruta</option>
                      <option value="3" <?= ($_GET['estado'] ?? '') === '3' ? 'selected' : '' ?>>Entregado</option>
                      <option value="4" <?= ($_GET['estado'] ?? '') === '4' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                  </div>

                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Buscar
                  </button>
                  <a href="/pedidos/reportePedidos" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                  </a>

                </form>
              </div>
              <!-- /.card-header -->

              <div class="card-body">
              <?php
              if (isset($_GET['periodo'])):

                $periodo      = $_GET['periodo'];
                $estadoFiltro = $_GET['estado'] ?? '';
                $params       = [];
                $whereFecha   = '';

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

                $whereEstado = '';
                if ($estadoFiltro !== '') {
                  $whereEstado = "AND p.id_estado = :id_estado";
                  $params[':id_estado'] = $estadoFiltro;
                }

                $sql = "
                  SELECT p.id_pedido, p.fecha_pedido, p.total, p.id_estado,
                         c.nombre, c.apellido,
                         ep.nombre AS estado
                  FROM pedido p
                  INNER JOIN cliente c ON c.id_cliente = p.id_cliente
                  INNER JOIN estado_pedido ep ON ep.id_estado = p.id_estado
                  WHERE p.fecha_baja IS NULL
                  $whereFecha
                  $whereEstado
                  ORDER BY p.fecha_pedido DESC
                ";

                $stmt = $conexionbd->prepare($sql);
                $stmt->execute($params);
                $listaPedidos = $stmt->fetchAll();

                $totalPedidos = count($listaPedidos);
                $sumaTotal    = array_sum(array_column($listaPedidos, 'total'));
              ?>

                <table class="table table-bordered table-hover table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>Fecha</th>
                      <th>Cliente</th>
                      <th>Productos</th>
                      <th>Total</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if ($totalPedidos === 0): ?>
                    <tr>
                      <td colspan="5" class="text-center text-muted py-3">
                        No se encontraron pedidos para el período seleccionado.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach($listaPedidos as $p):
                      $idEstado = $p['id_estado'];
                    ?>
                    <tr>
                      <td><?= date('d/m/Y', strtotime($p['fecha_pedido'])) ?></td>
                      <td><?= $p['nombre'] . ' ' . $p['apellido'] ?></td>
                      <td>
                        <ul style="list-style-type: none; margin: 0; padding: 0;">
                          <?php
                          $stmtProd = $conexionbd->prepare("
                            SELECT pr.nombre, pp.cantidad
                            FROM pedido_producto pp
                            INNER JOIN producto pr ON pr.id_producto = pp.id_producto
                            WHERE pp.id_pedido = :id_pedido AND pp.fecha_baja IS NULL
                          ");
                          $stmtProd->execute([':id_pedido' => $p['id_pedido']]);
                          foreach($stmtProd->fetchAll() as $prod):
                          ?>
                            <li><?= $prod['cantidad'] . ' ' . $prod['nombre'] ?></li>
                          <?php endforeach; ?>
                        </ul>
                      </td>
                      <td>$ <?= number_format($p['total'], 2, ',', '.') ?></td>
                      <td>
                        <?php switch ($idEstado) {
                          case 1: echo '<span class="badge badge-warning">' . $p['estado'] . '</span>'; break;
                          case 2: echo '<span class="badge badge-primary">' . $p['estado'] . '</span>'; break;
                          case 3: echo '<span class="badge badge-success">' . $p['estado'] . '</span>'; break;
                          case 4: echo '<span class="badge badge-danger">'  . $p['estado'] . '</span>'; break;
                        } ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  </tbody>

                  <!-- Resumen al pie de la tabla -->
                  <?php if ($totalPedidos > 0): ?>
                  <tfoot>
                    <tr class="table-secondary font-weight-bold">
                      <td colspan="3" class="text-right">Totales:</td>
                      <td>$ <?= number_format($sumaTotal, 2, ',', '.') ?></td>
                      <td><?= $totalPedidos ?> pedido<?= $totalPedidos !== 1 ? 's' : '' ?></td>
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