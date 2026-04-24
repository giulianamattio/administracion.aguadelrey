<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Recorridos &amp; Combustible';
require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');

?>
  <title>Agua del Rey | <?= $pagina ?></title>
  <link rel="icon" href="/favicon.ico">
  <style>
    body { background: #f4f6f9; font-family: 'Source Sans Pro', sans-serif; }

    /* ── Colores por turno ── */
    .badge-manana  { background: #1cc88a; color:#fff; }
    .badge-tarde   { background: #f6c23e; color:#fff; }

    /* ── KPI cards ── */
    .kpi-card { border-left: 4px solid; border-radius: 6px; }
    .kpi-card.green  { border-color: #1cc88a; }
    .kpi-card.blue   { border-color: #4e73df; }
    .kpi-card.orange { border-color: #f6c23e; }
    .kpi-card.red    { border-color: #e74a3b; }
    .kpi-icon { font-size: 2rem; opacity: .25; }
    .kpi-label { font-size: 11px; font-weight: 700; text-transform: uppercase;
                 letter-spacing: .5px; color: #858796; }
    .kpi-value { font-size: 28px; font-weight: 700; color: #5a5c69; line-height: 1.1; }
    .kpi-sub   { font-size: 12px; color: #858796; margin-top: 2px; }

    /* ── Tabla ── */
    .table td, .table th { border-top: none; vertical-align: middle; }
    .table thead th { border-bottom: none; font-size: 12px; text-transform: uppercase;
                      letter-spacing: .4px; color: #858796; }
    .progress { height: 16px; }

    /* ── Ranking bar ── */
    .rank-bar { height: 8px; border-radius: 4px; }

    /* ── Color dots ── */
    .color-dot { display:inline-block; width:10px; height:10px;
                 border-radius:50%; margin-right:6px; }

    /* ── Chart container ── */
    .chart-wrap { position: relative; height: 260px; }

    /* ── Sort cursor ── */
    thead th[data-sort] { cursor: pointer; user-select: none; }
    thead th[data-sort]:hover { background: #f8f9fc; }
    .sort-icon { opacity: .35; font-size: 10px; margin-left: 3px; }
    thead th.sorted .sort-icon { opacity: 1; color: #4e73df; }

    /* ── Paginación ── */
    .page-link { font-size: 12px; }

    /* ── Búsqueda ── */
    .search-bar { max-width: 260px; }

    /* ── Stat summary row ── */
    .stat-box { border-radius: 8px; padding: 18px 20px; color: #fff; }
    .stat-box .numero { font-size: 36px; font-weight: 700; line-height: 1; }
    .stat-box .label  { font-size: 13px; margin-top: 4px; opacity: .9; }
    .stat-box .pct    { font-size: 11px; opacity: .75; margin-top: 2px; }
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

        <!-- ── FILTRO DE FECHAS ── -->
        <div class="card card-outline card-primary mb-4">
          <div class="card-body py-3">
            <div class="row align-items-center">
              <div class="col-auto">
                <small class="text-muted text-uppercase font-weight-bold"
                       style="letter-spacing:.5px; font-size:11px;">
                  <i class="fas fa-filter mr-1"></i> Período
                </small>
              </div>
              <div class="col-auto">
                <input type="date" class="form-control form-control-sm" id="fechaDesde">
              </div>
              <div class="col-auto text-muted">→</div>
              <div class="col-auto">
                <input type="date" class="form-control form-control-sm" id="fechaHasta">
              </div>
              <div class="col-auto">
                <button class="btn btn-primary btn-sm" onclick="aplicarFiltro()">
                  <i class="fas fa-search mr-1"></i> Aplicar
                </button>
              </div>
              <div class="col-auto ml-auto">
                <small class="text-muted" id="totalInfo">— rutas en el período</small>
              </div>
            </div>
          </div>
        </div>


        <!-- después del card de filtro de fechas -->
        <div class="text-right mb-3">
        <a href="#" data-toggle="modal" data-target="#modalHistoricoNafta">
            <i class="fas fa-history mr-1"></i>
            <small>Ver historial de precios de nafta</small>
        </a>
        </div>

        <!-- ── KPI CARDS ── -->
        <div class="row mb-4">
          <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card kpi-card green mb-0">
              <div class="card-body py-3">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="kpi-label">Gasto total</div>
                    <div class="kpi-value" id="kpiGasto">$0</div>
                    <div class="kpi-sub" id="kpiGastoSub">— rutas</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-dollar-sign kpi-icon text-success"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card kpi-card blue mb-0">
              <div class="card-body py-3">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="kpi-label">Km recorridos</div>
                    <div class="kpi-value" id="kpiKm">0</div>
                    <div class="kpi-sub" id="kpiKmSub">— litros usados</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-road kpi-icon text-primary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card kpi-card orange mb-0">
              <div class="card-body py-3">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="kpi-label">Costo por pedido</div>
                    <div class="kpi-value" id="kpiCPP">$0</div>
                    <div class="kpi-sub">promedio período</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-box kpi-icon text-warning"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card kpi-card red mb-0">
              <div class="card-body py-3">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="kpi-label">Precio nafta prom.</div>
                    <div class="kpi-value" id="kpiPrecio">$0</div>
                    <div class="kpi-sub">$/litro promedio</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-gas-pump kpi-icon text-danger"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── GRÁFICOS ── -->
        <div class="row mb-4">
          <div class="col-md-5">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i> Gasto mensual ($)
                </h3>
                <div class="card-tools">
                  <button class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart-wrap">
                  <canvas id="chartMensual"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-line mr-1"></i> Evolución precio nafta ($/litro)
                </h3>
                <div class="card-tools">
                  <button class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart-wrap">
                  <canvas id="chartPrecio"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── RANKING ── -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-trophy mr-1"></i> Ranking de repartidores — por km recorridos
                </h3>
                <div class="card-tools">
                  <button class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="row" id="rankingGrid"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── TABLA DETALLE ── -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-list mr-1"></i> Detalle por ruta
            </h3>
            <div class="card-tools d-flex align-items-center gap-2">
              <div class="input-group input-group-sm search-bar mr-2">
                <input type="text" class="form-control" id="searchInput"
                       placeholder="Buscar repartidor, fecha…"
                       oninput="filtrarTabla()">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
              </div>
              <button class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                  <tr>
                    <th data-sort="id_ruta">#Ruta <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="fecha">Fecha <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="repartidor">Repartidor <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="turno">Turno <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="km" class="text-center">Km <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="litros" class="text-center">Litros <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="precio" class="text-center">$/litro <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="costo" class="text-center">Costo comb. <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="pedidos" class="text-center">Pedidos <span class="sort-icon fas fa-sort"></span></th>
                    <th data-sort="cpp" class="text-center">$/pedido <span class="sort-icon fas fa-sort"></span></th>
                  </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
                <tfoot id="tablaFoot"></tfoot>
              </table>
            </div>
          </div>
          <div class="card-footer clearfix py-2">
            <small class="text-muted float-left mt-1" id="tablaInfo">0 registros</small>
            <ul class="pagination pagination-sm float-right mb-0" id="pagBtns"></ul>
          </div>
        </div>

      

      </div> <!-- Container-fluid-->
    </section>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); 
?>
<script src="/VISTA/script/reporteRecorridos.js"></script>


<!-- Modal Histórico Nafta -->
<div class="modal fade" id="modalHistoricoNafta">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-gas-pump mr-1"></i> Historial de precios de nafta
        </h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-sm table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>Desde</th>
              <th>Hasta</th>
              <th class="text-right">$/litro</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $stmtNafta = $conexionbd->query("
                SELECT fecha_desde, fecha_hasta, precio_por_litro
                FROM precio_nafta
                ORDER BY fecha_desde DESC
            ");
            foreach ($stmtNafta->fetchAll() as $n):
            ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($n['fecha_desde'])) ?></td>
              <td><?= $n['fecha_hasta'] ? date('d/m/Y', strtotime($n['fecha_hasta'])) : '<span class="badge badge-success">Vigente</span>' ?></td>
              <td class="text-right font-weight-bold">$<?= number_format($n['precio_por_litro'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>