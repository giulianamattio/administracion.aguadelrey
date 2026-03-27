<?php
// ============================================================
//  VISTA/clientes/portal/misPedidos.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$idCliente = $_SESSION['cliente_id'];

// Traer pedidos del cliente logueado
$porPagina = 10;
$pagActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset    = ($pagActual - 1) * $porPagina;

// Total de pedidos
$stmtTotal = $conexionbd->prepare("
    SELECT COUNT(*) FROM pedido
    WHERE id_cliente = :id_cliente AND fecha_baja IS NULL
");
$stmtTotal->execute([':id_cliente' => $idCliente]);
$totalPedidos = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalPedidos / $porPagina);

// Pedidos paginados
$stmt = $conexionbd->prepare("
    SELECT p.id_pedido, p.fecha_pedido, p.total, p.observaciones_internas,
           ep.nombre AS estado, ep.id_estado
    FROM pedido p
    INNER JOIN estado_pedido ep ON ep.id_estado = p.id_estado
    WHERE p.id_cliente = :id_cliente
      AND p.fecha_baja IS NULL
    ORDER BY p.fecha_pedido DESC
    LIMIT :limite OFFSET :offset
");
$stmt->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);
$stmt->bindValue(':limite',     $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset',     $offset,    PDO::PARAM_INT);
$stmt->execute();
$listaPedidos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Mis Pedidos</title>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/navbar.php'); ?>

<div class="portal-content">
  <div class="container">

    <!--<div class="row mb-3">
      <div class="col-12">
        <h4 class="font-weight-bold">
          <i class="fas fa-box mr-2"></i>Mis Pedidos
        </h4>
      </div>
    </div>-->

    <div class="card shadow-sm" style="border-radius: 10px; border: none;">
      <div class="card-header bg-primary text-white" style="border-radius: 10px 10px 0 0;">
        <h5 class="mb-0"><i class="fas fa-box mr-2"></i>Mis Pedidos</h5>
      </div>


    <div class="card-body p-4" id="listaPedidos">

    <?php if (empty($listaPedidos)): ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i> Todavía no tenés pedidos registrados.
      </div>
    <?php else: ?>

    <!-- Alert de éxito -->
    <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle mr-1"></i> Tu pedido fue enviado correctamente.
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php endif; ?>

      <?php if (isset($_GET['cancelado'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="fas fa-check-circle mr-1"></i> Tu pedido fue cancelado correctamente.
          <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
      <?php endif; ?>


      <?php if (isset($_GET['modificado'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="fas fa-check-circle mr-1"></i> Tu pedido fue modificado correctamente.
          <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
      <?php endif; ?>

      <?php foreach($listaPedidos as $pedido):
        $idEstado = $pedido['id_estado'];

        // Badge por estado
        $badges = [
          1 => 'warning',
          2 => 'primary',
          3 => 'success',
          4 => 'danger',
        ];
        $badgeColor = $badges[$idEstado] ?? 'secondary';

        // Traer productos del pedido
        $stmtProd = $conexionbd->prepare("
            SELECT pr.nombre, pp.cantidad
            FROM pedido_producto pp
            INNER JOIN producto pr ON pr.id_producto = pp.id_producto
            WHERE pp.id_pedido = :id_pedido
              AND pp.fecha_baja IS NULL
        ");
        $stmtProd->execute([':id_pedido' => $pedido['id_pedido']]);
        $productos = $stmtProd->fetchAll();
      ?>

      <div class="card mb-3 shadow-sm" style="border-radius: 10px; border: none;">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="border-radius: 10px 10px 0 0; background: #f8f9fa;">
          <div>
            <span class="font-weight-bold text-muted" style="font-size: 13px;">
              Pedido #<?= $pedido['id_pedido'] ?>
            </span>
            &nbsp;·&nbsp;
            <span style="font-size: 13px; color: #6c757d;">
              <?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?>
              
              <?php if (!empty($pedido['turno'])): ?>
                &emsp; por la <?= htmlspecialchars($pedido['turno']) ?>
              <?php endif; ?>
            </span>
          </div>
          <span class="badge badge-<?= $badgeColor ?>" style="font-size: 13px; padding: 6px 12px;">
            <?= htmlspecialchars($pedido['estado']) ?>
          </span>
        </div>

        <div class="card-body py-3 px-4">
          <div class="row">

            <!-- Productos -->
            <div class="col-sm-8">
              <p class="mb-1 font-weight-bold" style="font-size: 13px; color: #6c757d;">PRODUCTOS</p>
              <ul class="list-unstyled mb-0">
                <?php foreach($productos as $prod): ?>
                  <li style="font-size: 14px;">
                    <i class="fas fa-tint mr-1" style="color: #adb5bd;"></i>
                    <?= $prod['cantidad'] ?> × <?= htmlspecialchars($prod['nombre']) ?>
                  </li>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                  <li class="text-muted" style="font-size:13px;">Sin productos registrados.</li>
                <?php endif; ?>

              </ul>
            </div>

            <!-- Total -->
            <!--<div class="col-sm-4 text-right">
              <p class="mb-1 font-weight-bold" style="font-size: 13px; color: #6c757d;">TOTAL</p>
              <p class="mb-0" style="font-size: 22px; font-weight: 600; color: #343a40;">
                $ <?= number_format($pedido['total'], 2, ',', '.') ?>
              </p>
            </div>-->

          </div>

          <!-- Observaciones -->
          <?php if (!empty($pedido['observaciones_cliente'])): ?>
          <div class="mt-2 pt-2" style="border-top: 1px solid #f0f0f0;">
            <p class="mb-0 text-muted" style="font-size: 13px;">
              <i class="fas fa-comment-alt mr-1"></i>
              <?= htmlspecialchars($pedido['observaciones_cliente']) ?>
            </p>
          </div>
          <?php endif; ?>

          <?php if ($idEstado == 1 && strtotime($pedido['fecha_pedido']) >= strtotime(date('Y-m-d'))): ?>
            <div class="mt-2 pt-2 d-flex justify-content-end gap-2" style="border-top: 1px solid #f0f0f0;">
              <a href="/clientes/modificarPedidoPortal/<?= $pedido['id_pedido'] ?>" 
                class="btn btn-outline-warning btn-sm">
                <i class="fas fa-pen mr-1"></i> Modificar pedido
              </a>
              <button type="button" class="btn btn-outline-danger btn-sm btn-cancelar ml-2"
                      data-id="<?= $pedido['id_pedido'] ?>">
                <i class="fas fa-times-circle mr-1"></i> Cancelar pedido
              </button>
            </div>
            <?php endif; ?>

        </div>
      </div>

      <?php endforeach; ?>
    <?php endif; ?>


    <!-- Paginado -->
      <?php if ($totalPaginas > 1): ?>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <small id="infoPedidos" class="text-muted">
          Mostrando <?= min($offset + 1, $totalPedidos) ?>–<?= min($offset + $porPagina, $totalPedidos) ?> de <?= $totalPedidos ?> pedidos
        </small>
        <ul class="pagination pagination-sm mb-0" id="paginacion">
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
      <?php endif; ?>


      </div>
    </div>
  </div>
</div>


<!-- Modal cancelar pedido -->
<div class="modal fade" id="modalCancelar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">⚠️ Cancelar pedido</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ¿Confirmás que querés cancelar el pedido <strong>#<span id="modalIdPedido"></span></strong>?
        Esta acción no se puede deshacer.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Volver</button>
        <a href="#" id="btnConfirmarCancelar" class="btn btn-danger">Sí, cancelar</a>
      </div>
    </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/footer.php'); ?>
<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
$('.btn-cancelar').on('click', function() {
  var id = $(this).data('id');
  $('#modalIdPedido').text(id);
  $('#btnConfirmarCancelar').attr('href', '/clientes/cancelarPedido/' + id);
  $('#modalCancelar').modal('show');
});



$(document).on('click', '#paginacion .page-link', function(e) {
    e.preventDefault();

    var $item = $(this).closest('.page-item');
    if ($item.hasClass('disabled') || $item.hasClass('active')) return;

    var pagina = $(this).data('pagina');

    $.get(window.location.pathname, { pagina: pagina }, function(response) {
        var $nuevo = $(response);
        $('#listaPedidos').html($nuevo.find('#listaPedidos').html());
        $('#paginacion').replaceWith($nuevo.find('#paginacion'));
        $('#infoPedidos').replaceWith($nuevo.find('#infoPedidos'));
    });
});
</script>

</body>
</html>