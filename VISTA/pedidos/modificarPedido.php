<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Modificar pedido';
require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');

  $idPedido = $_GET['id'] ?? null;
  if (!$idPedido || !is_numeric($idPedido)) {
      header('Location: /pedidos/listado?error=id_invalido');
      exit;
  }

  // Incluir id_turno_deseado en la consulta del pedido
  $stmtPedido = $conexionbd->prepare("
      SELECT p.id_pedido, p.id_cliente, p.fecha_pedido, p.total, p.observaciones_internas, p.id_turno_deseado
      FROM pedido p
      WHERE p.id_pedido = :id_pedido AND p.fecha_baja IS NULL
  ");
  $stmtPedido->execute([':id_pedido' => $idPedido]);
  $pedido = $stmtPedido->fetch();

  if (!$pedido) {
      header('Location: /pedidos/listado?error=pedido_no_encontrado');
      exit;
  }

  $stmtProductosPedido = $conexionbd->prepare("
      SELECT pp.id_producto, pp.cantidad
      FROM pedido_producto pp
      WHERE pp.id_pedido = :id_pedido AND pp.fecha_baja IS NULL
  ");
  $stmtProductosPedido->execute([':id_pedido' => $idPedido]);
  $productosPedido = $stmtProductosPedido->fetchAll();

  $stmtTodosProductos = $conexionbd->prepare("SELECT id_producto, nombre, precio_unitario FROM producto WHERE fecha_baja IS NULL");
  $stmtTodosProductos->execute();
  $listaProductos = $stmtTodosProductos->fetchAll();

  $stmtClientes = $conexionbd->prepare("SELECT id_cliente, nombre, apellido FROM cliente WHERE fecha_baja IS NULL");
  $stmtClientes->execute();
  $listaClientes = $stmtClientes->fetchAll();

  // Cargar turnos
  $stmtTurnos = $conexionbd->prepare("SELECT id_turno, nombre FROM turno ORDER BY id_turno");
  $stmtTurnos->execute();
  $listaTurnos = $stmtTurnos->fetchAll();
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>
  <link rel="Agua del rey" href="/favicon.ico">
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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
            <h1><?=$pagina?> <small class="text-muted">#<?=$idPedido?></small></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/pedidos/listado">Pedidos</a></li>
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
            <div class="card card-warning">

              <?php if (isset($_GET['error']) && $_GET['error'] === 'pedido_duplicado'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>⚠️ Error:</strong> Ya existe un pedido pendiente para este cliente en la fecha seleccionada.
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
              <?php endif; ?>

              <form onSubmit="return validarNuevoPedido(this)" method="post" action="/CONTROLADOR/pedidos/modificarPedido.php">
                <input type="hidden" name="id_pedido" value="<?= $idPedido ?>">

                <div class="card-body">
                  <div class="row">

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="fecha" name="fecha" class="form-control form-control-sm"
                               value="<?= date('Y-m-d', strtotime($pedido['fecha_pedido'])) ?>">
                        <div id="error-fecha" class="text-danger small error-msg"></div>
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="cliente">Cliente <span class="text-danger">*</span></label>
                        <select id="cliente" name="cliente" class="form-control form-control-sm select2" style="width: 100%;">
                          <option value="0">Seleccione el cliente</option>
                          <?php foreach($listaClientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>" <?= $c['id_cliente'] == $pedido['id_cliente'] ? 'selected' : '' ?>>
                              <?= $c['nombre'] ?>, <?= $c['apellido'] ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <div id="error-cliente" class="text-danger small error-msg"></div>
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="total">Monto total del pedido <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text form-control-sm">$</span>
                          </div>
                          <input type="text" id="total" name="total" class="form-control form-control-sm"
                                 value="<?= $pedido['total'] ?>">
                          <div id="error-total" class="text-danger small error-msg"></div>
                        </div>
                      </div>
                    </div>

                  </div>

                  <!-- Turno deseado -->
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Turno deseado por el cliente<span class="text-danger">*</span></label>
                        <div id="turnos-container">
                          <?php foreach($listaTurnos as $turno): ?>
                            <div class="icheck-primary d-inline mr-3">
                              <input type="radio"
                                     id="turno<?= $turno['id_turno'] ?>"
                                     name="id_turno_deseado"
                                     value="<?= $turno['id_turno'] ?>"
                                     <?= $turno['id_turno'] == $pedido['id_turno_deseado'] ? 'checked' : '' ?>>
                              <label for="turno<?= $turno['id_turno'] ?>">
                                <?php if($turno['id_turno'] == 1 || $turno['id_turno'] == 2): ?>
                                          Por la <?= $turno['nombre'] ?>
                                      <?php else: ?>
                                          <?= $turno['nombre'] ?>
                                      <?php endif; ?>
                              </label>
                            </div>
                          <?php endforeach; ?>
                        </div>
                        <div id="error-turno" class="text-danger small error-msg"></div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label for="observaciones">Observaciones internas</label>
                        <textarea id="observaciones" name="observaciones" class="form-control form-control-sm"><?= htmlspecialchars($pedido['observaciones_internas'] ?? '') ?></textarea>
                      </div>
                    </div>
                  </div>

                  <label>Productos <span class="text-danger">*</span></label>

                  <div class="col-md-8 card-body p-0">
                    <table id="lista_productos" class="table table-sm" style="border: none;">
                      <thead>
                        <tr>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>

                        <?php 
                        $cantidadInicial = count($productosPedido);
                        if ($cantidadInicial === 0) $cantidadInicial = 1;

                        if (!empty($productosPedido)):
                          foreach($productosPedido as $idx => $pp):
                            $num = $idx + 1;
                        ?>
                        <tr>
                          <td>
                            <select class="form-control form-control-sm select-producto" id="producto<?=$num?>" name="producto<?=$num?>">
                              <option value="0">Seleccione un producto</option>
                              <?php foreach($listaProductos as $prod): ?>
                                <option value="<?= $prod['id_producto'] ?>"
                                        data-precio="<?= $prod['precio_unitario'] ?>"
                                        <?= $prod['id_producto'] == $pp['id_producto'] ? 'selected' : '' ?>>
                                  <?= $prod['nombre'] ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback d-block text-danger small error-producto<?=$num?>"></div>
                          </td>
                          <td>
                            <input type="number" class="form-control form-control-sm input-cantidad" id="cantidad<?=$num?>" name="cantidad<?=$num?>" value="<?= $pp['cantidad'] ?>">
                            <div class="invalid-feedback d-block text-danger small error-cantidad<?=$num?>"></div>
                          </td>
                          <td>
                            <i class="fas fa-minus-square fa-lg button_eliminar_producto" style="color: #dc3545;"></i>
                          </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php else: ?>
                        <tr>
                          <td>
                            <select class="form-control form-control-sm select-producto" id="producto1" name="producto1">
                              <option value="0">Seleccione un producto</option>
                              <?php foreach($listaProductos as $prod): ?>
                                <option value="<?= $prod['id_producto'] ?>" data-precio="<?= $prod['precio_unitario'] ?>">
                                  <?= $prod['nombre'] ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </td>
                          <td>
                            <input type="number" class="form-control form-control-sm input-cantidad" id="cantidad1" name="cantidad1">
                          </td>
                          <td>
                            <i class="fas fa-minus-square fa-lg button_eliminar_producto" style="color: #dc3545;"></i>
                          </td>
                        </tr>
                        <?php endif; ?>

                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="3">
                            <input type="hidden" name="cantidadProductoActual" id="cantidadProductoActual" value="<?= $cantidadInicial ?>">
                            <div class="row align-items-center h-100 justify-content-center" style="margin-top: 5px;">
                              <i class="nav-icon fas fa-plus-square fa-lg button_agregar_producto" style="color: #28a745;"></i>
                            </div>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <div class="card-header"></div><br/>

                  <div class="row align-items-center h-100 justify-content-center">
                    <div class="col-auto">
                      <a href="/pedidos/listado" class="btn btn-default">Cancelar</a>
                      <button type="submit" class="btn btn-warning">Guardar cambios</button>
                    </div>
                  </div>

                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script src="/VISTA/script/productos.js"></script>
<script>
  $(function () {
    $('.select2').select2();
    $('.select2bs4').select2({ theme: 'bootstrap4' });
  });
</script>
</body>
</html>