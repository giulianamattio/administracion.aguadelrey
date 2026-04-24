<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Listado'; ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/encabezado.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/menu.php');


  $porPagina = 15;
                  $pagActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
                  $offset    = ($pagActual - 1) * $porPagina;

                  // Filtros
                  $filtroDesde   = $_GET['fecha_desde'] ?? '';
                  $filtroHasta   = $_GET['fecha_hasta'] ?? '';
                  $filtroCliente = isset($_GET['cliente']) && $_GET['cliente'] !== '' ? (int)$_GET['cliente'] : null;
                  $filtroEstado  = isset($_GET['estado'])  && $_GET['estado']  !== '' ? (int)$_GET['estado']  : null;
                  $filtroOrden = $_GET['orden'] ?? 'DESC';
                  $filtroOrden = in_array($filtroOrden, ['ASC', 'DESC']) ? $filtroOrden : 'DESC';

                  $where  = "WHERE p.fecha_baja IS NULL";
                  $params = [];

                  if ($filtroDesde) {
                      $where .= " AND DATE(p.fecha_pedido) >= :fecha_desde";
                      $params[':fecha_desde'] = $filtroDesde;
                  }
                  if ($filtroHasta) {
                      $where .= " AND DATE(p.fecha_pedido) <= :fecha_hasta";
                      $params[':fecha_hasta'] = $filtroHasta;
                  }
                  if ($filtroCliente) {
                      $where .= " AND p.id_cliente = :id_cliente";
                      $params[':id_cliente'] = $filtroCliente;
                  }
                  if ($filtroEstado) {
                      $where .= " AND p.id_estado = :id_estado";
                      $params[':id_estado'] = $filtroEstado;
                  }

                  // Total con filtros
                  $stmtTotal = $conexionbd->prepare("SELECT COUNT(*) FROM pedido p $where");
                  $stmtTotal->execute($params);
                  $totalPedidos = $stmtTotal->fetchColumn();
                  $totalPaginas = ceil($totalPedidos / $porPagina);

                  // Lista de clientes para el select
                  $stmtClientes = $conexionbd->prepare("SELECT id_cliente, nombre, apellido FROM cliente WHERE fecha_baja IS NULL ORDER BY apellido, nombre");
                  $stmtClientes->execute();
                  $listaClientes = $stmtClientes->fetchAll();

                  // Query paginada con filtros
                  $stmt = $conexionbd->prepare("
                      SELECT p.id_pedido, p.id_estado, p.fecha_pedido, c.nombre, c.apellido,
                            ep.nombre AS estado, p.total, oip.nombre AS origen,
                            p.observaciones_internas, p.observaciones_cliente,
                            t.nombre AS turno, p.bidones_vacios
                      FROM pedido p
                      INNER JOIN cliente c ON c.id_cliente = p.id_cliente
                      INNER JOIN estado_pedido ep ON ep.id_estado = p.id_estado
                      LEFT JOIN origen_ingreso_pedido oip ON oip.id_origen_ingreso = p.id_origen_pedido
                      LEFT JOIN turno t ON t.id_turno = p.id_turno_deseado
                      $where
                      ORDER BY p.fecha_pedido $filtroOrden
                      LIMIT :limite OFFSET :offset
                  ");
                  foreach ($params as $key => $value) {
                      $stmt->bindValue($key, $value);
                  }
                  $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
                  $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
                  $stmt->execute();
                  $listaPedidos = $stmt->fetchAll();

  ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
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
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">

              <div class="card-header">
                <div class="row align-items-center mb-2">
                  <div class="col-auto">
                    <a href="/pedidos/nuevoPedido" class="btn btn-success btn-sm">
                      <i class="fas fa-plus mr-1"></i> Nuevo Pedido
                    </a>
                  </div>
                </div>

                <form method="GET" action="" class="form-inline flex-wrap" style="gap: 8px;" id="formFiltros">

                  <div class="form-group">
                    <label class="mr-1 small">Desde:</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm"
                          value="<?= htmlspecialchars($filtroDesde) ?>">
                  </div>

                  <div class="form-group">
                    <label class="mr-1 small">Hasta:</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                          value="<?= htmlspecialchars($filtroHasta) ?>">
                  </div>

                  <div class="form-group">
                    <label class="mr-1 small">Cliente:</label>
                    <select name="cliente" class="form-control form-control-sm" style="min-width:180px;">
                      <option value="">Todos</option>
                      <?php foreach($listaClientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>"
                          <?= $filtroCliente == $c['id_cliente'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="mr-1 small">Estado:</label>
                    <select name="estado" class="form-control form-control-sm">
                      <option value="">Todos</option>
                      <option value="1" <?= $filtroEstado == 1 ? 'selected' : '' ?>>Pendiente</option>
                      <option value="2" <?= $filtroEstado == 2 ? 'selected' : '' ?>>En ruta</option>
                      <option value="3" <?= $filtroEstado == 3 ? 'selected' : '' ?>>Entregado</option>
                      <option value="4" <?= $filtroEstado == 4 ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                  </div>

                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Buscar
                  </button>
                  <a href="/pedidos/listado" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                  </a>

                </form>
              </div>

              <div class="card-body">

              <?php if (isset($_GET['exito']) && $_GET['exito'] === 'eliminado'): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                      <strong>✅ Éxito:</strong> El pedido fue eliminado correctamente.
                      <button type="button" class="close" data-dismiss="alert">
                          <span>&times;</span>
                      </button>
                  </div>
              <?php endif; ?>


              <?php if (isset($_GET['exito']) && $_GET['exito'] === 'finalizado'): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                      <strong>✅ Éxito:</strong> El pedido fue marcado como entregado.
                      <button type="button" class="close" data-dismiss="alert">
                          <span>&times;</span>
                      </button>
                  </div>
              <?php endif; ?>

              <?php if (isset($_GET['exito']) && $_GET['exito'] === 'modificado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>✅ Éxito:</strong> El pedido fue modificado correctamente.
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 10px;">#</th>
                      <th>
                        Fecha
                        <a href="#" id="btnOrden" data-orden="<?= $filtroOrden ?>" title="Ordenar por fecha">
                          <?php if ($filtroOrden === 'DESC'): ?>
                            <i class="fas fa-sort-amount-down" style="color:#6c757d;"></i>
                          <?php else: ?>
                            <i class="fas fa-sort-amount-up" style="color:#6c757d;"></i>
                          <?php endif; ?>
                        </a>
                      </th>
                      <th>Cliente</th>
                      <th>Productos</th>
                      <th>Origen</th>
                      <th>Monto</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="tablaPedidos">

                  <?php foreach($listaPedidos as $rsPedidos):
                          $idPedido   = $rsPedidos["id_pedido"];
                          $fecha      = $rsPedidos["fecha_pedido"];
                          $cliente    = $rsPedidos["nombre"]." ".$rsPedidos["apellido"];
                          $estado     = $rsPedidos["estado"];
                          $idEstado   = $rsPedidos["id_estado"];
                          $total      = $rsPedidos["total"];
                          $origen     = $rsPedidos["origen"];
                          $bidonesVacios = $rsPedidos["bidones_vacios"];
                          $obsInterna = $rsPedidos["observaciones_internas"] ?? '';
                          $obsCliente = $rsPedidos["observaciones_cliente"] ?? '';
                          $turno      = $rsPedidos["turno"] ?? '';
                      ?>
                      <tr>
                        <td style="width: 10px"><?=$idPedido?></td>
                        <td><?=date('d/m/Y', strtotime($fecha))?></td>
                        <td><?=$cliente?></td>
                        <td>
                          <ul style="list-style-type: none;">
                            <?php
                            $stmtProductosPedidos = $conexionbd->prepare("
                                SELECT p.nombre AS producto, pp.cantidad
                                FROM pedido_producto pp 
                                INNER JOIN producto p ON p.id_producto = pp.id_producto
                                WHERE pp.fecha_baja IS NULL AND pp.id_pedido = :id_pedido
                            ");
                            $stmtProductosPedidos->execute([':id_pedido' => $idPedido]);
                            foreach($stmtProductosPedidos->fetchAll() as $rsProductosPedidos):
                            ?>
                              <li><?=$rsProductosPedidos["cantidad"]." ".$rsProductosPedidos["producto"]?></li>
                            <?php endforeach; ?>
                          </ul>
                        </td>
                        <td><?= $origen ?></td>
                        <td><b>Total: </b> $<?= $total ?></td>
                        <td>
                          <?php switch ($idEstado) {
                            case 1: ?><span class="badge badge-warning"><?= $estado?></span><?php break;
                            case 2: ?><span class="badge badge-primary"><?= $estado?></span><?php break;
                            case 3: ?><span class="badge badge-success"><?= $estado?></span><?php break;
                            case 4: ?><span class="badge badge-danger"><?= $estado?></span><?php break;
                            case 5: ?><span class="badge badge-dark"><?= $estado?></span><?php break;
                            default: ?><span></span><?php
                          }?>
                        </td>
                        <td>
                          <!-- Botón info -->
                          <button type="button" class="btn btn-link p-0 btn-info-pedido"
                                  data-toggle="collapse"
                                  data-target="#detalle-<?= $idPedido ?>"
                                  title="Ver detalle">
                            <i class="fas fa-info-circle fa-lg" style="color: #17a2b8;"></i>
                          </button>
                          &nbsp;
                          <?php if($idEstado == 1): ?>
                            <button type="button" class="btn btn-link p-0"
                                    data-toggle="modal"
                                    data-target="#modalEliminar"
                                    data-id="<?= $idPedido ?>">
                              <i class="fas fa-minus-square fa-lg" style="color: #dc3545;"></i>
                            </button>
                            &nbsp;
                          <?php endif; ?>
                          <?php if($idEstado == 1 || $idEstado == 2 || $idEstado == 5): ?>
                            <button type="button" class="btn btn-link p-0"
                                    data-toggle="modal"
                                    data-target="#modalFinalizar"
                                    data-id="<?= $idPedido ?>">
                              <i class="fas fa-check-square fa-lg" style="color: #28a745;"></i>
                            </button>
                            &nbsp;
                          <?php endif; ?>
                          <?php if($idEstado == 1): ?>
                            <a href="/pedidos/modificarPedido/<?=$idPedido?>">
                              <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>

                      <!-- Fila de detalle colapsable -->
                      <tr>
                        <td colspan="8" class="p-0 border-0">
                          <div class="collapse" id="detalle-<?= $idPedido ?>">
                            <div class="px-4 py-3" style="background:#f8f9fa; border-bottom: 1px solid #dee2e6;">
                              <div class="row">
                                <div class="col-sm-2">
                                  <p class="mb-1">
                                    <strong><i class="fas fa-clock mr-1 text-muted"></i>Turno deseado:</strong>
                                  </p>
                                  <p class="text-muted mb-0">
                                    <?= $turno ? htmlspecialchars($turno) : '<span class="text-muted">No especificado</span>' ?>
                                  </p>
                                </div>
                                <div class="col-sm-4">
                                  <p class="mb-1">
                                    <strong><i class="fas fa-comment mr-1 text-muted"></i>Observaciones del cliente:</strong>
                                  </p>
                                  <p class="text-muted mb-0">
                                    <?= $obsCliente ? htmlspecialchars($obsCliente) : '<span class="text-muted">—</span>' ?>
                                  </p>
                                </div>
                                <div class="col-sm-4">
                                  <p class="mb-1">
                                    <strong><i class="fas fa-lock mr-1 text-muted"></i>Observaciones internas:</strong>
                                  </p>
                                  <p class="text-muted mb-0">
                                    <?= $obsInterna ? htmlspecialchars($obsInterna) : '<span class="text-muted">—</span>' ?>
                                  </p>
                                </div>
                                <div class="col-sm-2">
                                  <p class="mb-1">
                                    <strong><i class="fas fa-wine-bottle mr-1 text-muted"></i>Bidones vacios:</strong>
                                  </p>
                                  <p class="text-muted mb-0">
                                    <?= $bidonesVacios ? htmlspecialchars($bidonesVacios) : '<span class="text-muted">—</span>' ?>
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>

                      <?php endforeach; ?> 
                    
                  </tbody>
                </table>

              </div>
              <!-- /.card-body -->

              <div class="card-footer clearfix">
                <small id="infoPaginacion"  class="text-muted">
                  Mostrando <?= min($offset + 1, $totalPedidos) ?>–<?= min($offset + $porPagina, $totalPedidos) ?> de <?= $totalPedidos ?> pedidos
                </small>
                <ul class="pagination pagination-sm m-0 float-right" id="paginacion">
                  <li class="page-item <?= $pagActual <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" data-pagina="<?= $pagActual - 1 ?>">&laquo;</a>
                  </li>
                  <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <li class="page-item <?= $p === $pagActual ? 'active' : '' ?>">
                      <a class="page-link" href="#" data-pagina="<?= $p ?>"><?= $p ?></a>
                    </li>
                  <?php endfor; ?>
                  <li class="page-item <?= $pagActual >= $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" data-pagina="<?= $pagActual + 1 ?>">&raquo;</a>
                  </li>
                </ul>
              </div>

            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
        
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>


  <!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">⚠️ Confirmar cancelación</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ¿Estás seguro que querés Cancelar el pedido <strong>#<span id="modalIdPedido"></span></strong>? Esta acción no se puede deshacer.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">Sí, cancelar</a>
      </div>
    </div>
  </div>
</div>




<!-- Modal Confirmar Finalización -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">✅ Confirmar finalización</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ¿Confirmás que el pedido <strong>#<span id="modalIdFinalizar"></span></strong> fue entregado?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarFinalizar" class="btn btn-success">Sí, finalizar</a>
      </div>
    </div>
  </div>
</div>



  <?php 
    require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php');
  ?>
    

</div>
<!-- ./wrapper -->

<?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php');
?>


<script>
  $('#modalEliminar').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var idPedido = button.data('id');
    $('#modalIdPedido').text(idPedido);
    $('#btnConfirmarEliminar').attr('href', '/pedidos/eliminarPedido/' + idPedido);
  });



  $('#modalFinalizar').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var idPedido = button.data('id');
    $('#modalIdFinalizar').text(idPedido);
    $('#btnConfirmarFinalizar').attr('href', '/pedidos/finalizarPedido/' + idPedido);
});



// Paginado AJAX
$(document).on('click', '#paginacion .page-link', function(e) {
    e.preventDefault();

    var $item = $(this).closest('.page-item');
    if ($item.hasClass('disabled') || $item.hasClass('active')) return;

    var pagina = $(this).data('pagina');

    // Mantener filtros activos al paginar
    var ordenActual = $('#btnOrden').data('orden') || 'DESC';
    var params = $('#formFiltros').serialize() + '&pagina=' + pagina + '&orden=' + ordenActual;

    $.get(window.location.pathname, params, function(response) {
        var $nuevo = $(response);
        $('#tablaPedidos').html($nuevo.find('#tablaPedidos').html());
        $('#paginacion').replaceWith($nuevo.find('#paginacion'));
        $('#infoPaginacion').replaceWith($nuevo.find('#infoPaginacion'));
    });
});



// Rotar ícono info al abrir/cerrar detalle
$(document).on('click', '.btn-info-pedido', function() {
    var icon = $(this).find('i');
    var target = $(this).data('target');
    $(target).on('show.bs.collapse', function() {
        icon.css('color', '#0c6fa8');
    });
    $(target).on('hide.bs.collapse', function() {
        icon.css('color', '#17a2b8');
    });
});



// Ordenar por fecha
$(document).on('click', '#btnOrden', function(e) {
    e.preventDefault();
    var ordenActual = $(this).data('orden');
    var nuevoOrden  = ordenActual === 'DESC' ? 'ASC' : 'DESC';

    var params = $('#formFiltros').serialize() + '&orden=' + nuevoOrden + '&pagina=1';

    $.get(window.location.pathname, params, function(response) {
        var $nuevo = $(response);
        $('#tablaPedidos').html($nuevo.find('#tablaPedidos').html());
        $('#paginacion').replaceWith($nuevo.find('#paginacion'));
        $('#infoPaginacion').replaceWith($nuevo.find('#infoPaginacion'));
        // Actualizar el botón con el nuevo estado
        $('#btnOrden').data('orden', nuevoOrden);
        $('#btnOrden i').attr('class',
            nuevoOrden === 'DESC'
                ? 'fas fa-sort-amount-down'
                : 'fas fa-sort-amount-up'
        );
    });
});
</script>

</body>
</html>