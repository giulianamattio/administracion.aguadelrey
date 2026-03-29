<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Seguimiento de Clientes';
require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php'); ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>
  <link rel="Agua del rey" href="/favicon.ico">
  <style>
    .table td, .table th { border-top: none; }
    .table thead th { border-bottom: none; }
    .badge-dias { font-size: 0.85em; padding: 4px 8px; }
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

                  <div class="form-group">
                    <label class="mr-2">Sin pedidos hace más de:</label>
                    <select name="dias" class="form-control form-control-sm">
                      <option value="7"  <?= ($_GET['dias'] ?? '') === '7'  ? 'selected' : '' ?>>7 días</option>
                      <option value="15" <?= ($_GET['dias'] ?? '') === '15' ? 'selected' : '' ?>>15 días</option>
                      <option value="30" <?= ($_GET['dias'] ?? '30') === '30' ? 'selected' : '' ?>>30 días</option>
                      <option value="60" <?= ($_GET['dias'] ?? '') === '60' ? 'selected' : '' ?>>60 días</option>
                    </select>
                  </div>

                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Buscar
                  </button>
                  <a href="/pedidos/reporteSeguimientoClientes" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                  </a>

                </form>
              </div>
              <!-- /.card-header -->

              <div class="card-body">
              <?php
              if (isset($_GET['dias'])):

                $dias = (int) $_GET['dias'];

                // LEFT JOIN para traer también clientes sin pedidos
                // CASE para mostrar "Nunca" si no tiene pedidos
                $sql = "
                  SELECT 
                    c.id_cliente,
                    c.nombre,
                    c.apellido,
                    MAX(p.fecha_pedido) AS ultimo_pedido,
                    CASE 
                      WHEN MAX(p.fecha_pedido) IS NULL THEN NULL
                      ELSE CURRENT_DATE - CAST(MAX(p.fecha_pedido) AS DATE)
                    END AS dias_sin_pedir
                  FROM cliente c
                  LEFT JOIN pedido p ON p.id_cliente = c.id_cliente 
                    AND p.fecha_baja IS NULL
                  WHERE c.fecha_baja IS NULL
                  GROUP BY c.id_cliente, c.nombre, c.apellido
                  HAVING 
                    MAX(p.fecha_pedido) IS NULL
                    OR CURRENT_DATE - CAST(MAX(p.fecha_pedido) AS DATE) > :dias
                  ORDER BY dias_sin_pedir DESC NULLS LAST
                ";

                $stmt = $conexionbd->prepare($sql);
                $stmt->execute([':dias' => $dias]);
                $listaClientes = $stmt->fetchAll();
                $totalClientes = count($listaClientes);
              ?>

                <table class="table table-bordered table-hover table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>#</th>
                      <th>Cliente</th>
                      <th class="text-center">Último pedido</th>
                      <th class="text-center">Días sin pedir</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if ($totalClientes === 0): ?>
                    <tr>
                      <td colspan="4" class="text-center text-muted py-3">
                        No hay clientes sin pedidos en los últimos <?= $dias ?> días. 🎉
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach($listaClientes as $i => $row):
                      $diasSinPedir = $row['dias_sin_pedir'];
                      $ultimoPedido = $row['ultimo_pedido'];

                      // Color del badge según urgencia
                      if ($diasSinPedir === null) {
                        $badgeClass = 'badge-dark';
                        $labelDias  = 'Nunca pidió';
                      } elseif ($diasSinPedir >= 60) {
                        $badgeClass = 'badge-danger';
                        $labelDias  = $diasSinPedir . ' días';
                      } elseif ($diasSinPedir >= 30) {
                        $badgeClass = 'badge-warning';
                        $labelDias  = $diasSinPedir . ' días';
                      } else {
                        $badgeClass = 'badge-info';
                        $labelDias  = $diasSinPedir . ' días';
                      }
                    ?>
                    <tr>
                      <td><?= $i + 1 ?></td>
                      <td><?= $row['apellido'] . ', ' . $row['nombre'] ?></td>
                      <td class="text-center">
                        <?= $ultimoPedido ? date('d/m/Y', strtotime($ultimoPedido)) : '<span class="text-muted">—</span>' ?>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-dias <?= $badgeClass ?>">
                          <?= $labelDias ?>
                        </span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  </tbody>

                  <?php if ($totalClientes > 0): ?>
                  <tfoot>
                    <tr class="table-secondary font-weight-bold">
                      <td colspan="3" class="text-right">Total clientes a contactar:</td>
                      <td class="text-center"><?= $totalClientes ?></td>
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
</body>
</html>