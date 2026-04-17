<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php $pagina = 'Nuevo pedido'; ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>

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
            <div class="card card-primary">

              <?php if (isset($_GET['error']) && $_GET['error'] === 'pedido_duplicado'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>⚠️ Error:</strong> Ya existe un pedido pendiente para este cliente en la fecha seleccionada.
                  <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
              <?php endif; ?>

              <?php if (isset($_GET['exito']) && $_GET['exito'] === '1'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>✅ Éxito:</strong> El pedido fue registrado correctamente.
                  <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
              <?php endif; ?>

              <form onSubmit="return validarNuevoPedido(this)" method="post" action="/CONTROLADOR/pedidos/nuevoPedido.php">
                <div class="card-body">

                  <div class="row">
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="fecha" name="fecha" class="form-control form-control-sm">
                        <div id="error-fecha" class="text-danger small error-msg"></div>
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="cliente">Cliente <span class="text-danger">*</span></label>
                        <select id="cliente" name="cliente" class="form-control form-control-sm select2" style="width: 100%;">
                          <option value="0">Seleccione el cliente</option>
                          <?php
                          $stmt = $conexionbd->prepare("SELECT id_cliente, nombre, apellido FROM cliente WHERE fecha_baja IS NULL");
                          $stmt->execute();
                          foreach($stmt->fetchAll() as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>"><?= $c['nombre'] ?>, <?= $c['apellido'] ?></option>
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
                          <input type="text" id="total" name="total" class="form-control form-control-sm" placeholder="">
                          <div id="error-total" class="text-danger small error-msg"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Turno deseado -->
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Turno deseado por el cliente <span class="text-danger">*</span></label>
                        <div id="turnos-container">
                          <?php
                          $stmtTurnos = $conexionbd->prepare("SELECT id_turno, nombre FROM turno ORDER BY id_turno");
                          $stmtTurnos->execute();
                          $listaTurnos = $stmtTurnos->fetchAll();
                          foreach($listaTurnos as $turno): ?>
                              <div class="icheck-primary d-inline mr-3">
                                  <input type="radio" id="turno<?= $turno['id_turno'] ?>" name="id_turno_deseado" value="<?= $turno['id_turno'] ?>">
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
                        <textarea id="observaciones" name="observaciones" class="form-control form-control-sm"></textarea>
                      </div>
                    </div>
                  </div>

                  <label for="productos">Productos <span class="text-danger">*</span></label>

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
                        <tr>
                          <td>
                            <select class="form-control form-control-sm select-producto" id="producto1" name="producto1">
                              <option value="0">Seleccione un producto</option>
                              <?php
                              $stmt = $conexionbd->prepare("SELECT id_producto, nombre, precio_unitario FROM producto WHERE fecha_baja IS NULL");
                              $stmt->execute();
                              foreach($stmt->fetchAll() as $prod): ?>
                                <option value="<?= $prod['id_producto'] ?>" data-precio="<?= $prod['precio_unitario'] ?>"><?= $prod['nombre'] ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback d-block text-danger small error-producto1"></div>
                          </td>
                          <td>
                            <input type="number" class="form-control form-control-sm input-cantidad" id="cantidad1" name="cantidad1" />
                            <div class="invalid-feedback d-block text-danger small error-cantidad1"></div>
                          </td>
                          <td>
                            <i class="fas fa-minus-square fa-lg button_eliminar_producto" style="color: #dc3545;"></i>
                          </td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="3">
                            <input type="hidden" name="cantidadProductoActual" id="cantidadProductoActual" value="1">
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
                      <button type="button" class="btn btn-default" onclick="javascript:volverHome()">Cancelar</button>
                      <button type="submit" class="btn btn-success">Guardar</button>
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