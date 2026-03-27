<?php
// ============================================================
//  VISTA/clientes/portal/modificarPedidoPortal.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$idCliente = $_SESSION['cliente_id'];
$idPedido  = $_GET['id'] ?? null;

if (!$idPedido || !is_numeric($idPedido)) {
    header('Location: /clientes/misPedidos');
    exit;
}

// Verificar que el pedido pertenece al cliente, está pendiente y es de hoy o futuro
$stmtVerifica = $conexionbd->prepare("
    SELECT id_pedido, fecha_pedido, observaciones_internas, id_turno_deseado
    FROM pedido
    WHERE id_pedido  = :id_pedido
      AND id_cliente = :id_cliente
      AND id_estado  = 1
      AND fecha_baja IS NULL
      AND DATE(fecha_pedido) >= CURRENT_DATE
");
$stmtVerifica->execute([':id_pedido' => $idPedido, ':id_cliente' => $idCliente]);
$pedido = $stmtVerifica->fetch();

if (!$pedido) {
    header('Location: /clientes/misPedidos');
    exit;
}

// Traer productos del pedido
$stmtProdPedido = $conexionbd->prepare("
    SELECT pp.id_producto, pp.cantidad
    FROM pedido_producto pp
    WHERE pp.id_pedido  = :id_pedido
      AND pp.fecha_baja IS NULL
");
$stmtProdPedido->execute([':id_pedido' => $idPedido]);
$productosPedido = $stmtProdPedido->fetchAll();

// Traer todos los productos disponibles (sin "Visita de cobro")
$stmtProductos = $conexionbd->prepare("
    SELECT id_producto, nombre 
    FROM producto 
    WHERE fecha_baja IS NULL 
      AND id_producto != 4
    ORDER BY nombre
");
$stmtProductos->execute();
$listaProductos = $stmtProductos->fetchAll();

$cantidadInicial = count($productosPedido) ?: 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Modificar Pedido</title>
  <style>
    .producto-row td { vertical-align: middle; }
    .btn-eliminar-prod { background: none; border: none; cursor: pointer; padding: 0; }
  </style>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/navbar.php'); ?>

<div class="portal-content">
  <div class="container">

    <div class="row mb-3">
      <div class="col-12">
        <h4 class="font-weight-bold">
          <i class="fas fa-pen mr-2"></i>Modificar Pedido <small class="text-muted">#<?= $idPedido ?></small>
        </h4>
      </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm" style="border-radius: 10px; border: none;">
      <div class="card-header bg-warning text-white" style="border-radius: 10px 10px 0 0;">
        <h5 class="mb-0"><i class="fas fa-shopping-cart mr-2"></i>Editá tu pedido</h5>
      </div>
      <div class="card-body p-4">

        <form action="/clientes/guardarModificacionPedidoPortal" method="POST" onsubmit="return validarFormulario()">
          <input type="hidden" name="id_pedido" value="<?= $idPedido ?>">

          <!-- Turno -->
          <div class="form-group">
            <label class="font-weight-bold">Turno deseado <span class="text-danger">*</span></label>
            <div class="mt-1">
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="turno_manana" name="turno" value="1" class="custom-control-input"
                       <?= $pedido['id_turno_deseado'] == 1 ? 'checked' : '' ?> required>
                <label class="custom-control-label" for="turno_manana">
                  <i class="fas fa-sun mr-1" style="color:#f6c23e"></i> Por la mañana
                </label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="turno_tarde" name="turno" value="2" class="custom-control-input"
                       <?= $pedido['id_turno_deseado'] == 2 ? 'checked' : '' ?>>
                <label class="custom-control-label" for="turno_tarde">
                  <i class="fas fa-cloud-sun mr-1" style="color:#fd7e14"></i> Por la tarde
                </label>
              </div>
            </div>
            <div class="text-danger small mt-1" id="error-turno"></div>
          </div>

          <!-- Productos -->
          <div class="form-group">
            <label class="font-weight-bold">Productos <span class="text-danger">*</span></label>
            <table class="table table-sm" id="tabla-productos">
              <thead class="thead-light">
                <tr>
                  <th>Producto</th>
                  <th style="width:130px;">Cantidad</th>
                  <th style="width:40px;"></th>
                </tr>
              </thead>
              <tbody id="tbody-productos">
                <?php foreach($productosPedido as $idx => $pp):
                  $num = $idx + 1;
                ?>
                <tr class="producto-row">
                  <td>
                    <select name="producto<?= $num ?>" id="producto<?= $num ?>" class="form-control form-control-sm">
                      <option value="0">Seleccione un producto</option>
                      <?php foreach($listaProductos as $prod): ?>
                        <option value="<?= $prod['id_producto'] ?>"
                          <?= $prod['id_producto'] == $pp['id_producto'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($prod['nombre']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="text-danger small error-producto<?= $num ?>"></div>
                  </td>
                  <td>
                    <input type="number" name="cantidad<?= $num ?>" id="cantidad<?= $num ?>"
                           class="form-control form-control-sm" min="1"
                           value="<?= $pp['cantidad'] ?>">
                    <div class="text-danger small error-cantidad<?= $num ?>"></div>
                  </td>
                  <td>
                    <?php if ($num > 1): ?>
                    <button type="button" class="btn-eliminar-prod" onclick="eliminarFila(this)">
                      <i class="fas fa-minus-square fa-lg" style="color:#dc3545;"></i>
                    </button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($productosPedido)): ?>
                <tr class="producto-row">
                  <td>
                    <select name="producto1" id="producto1" class="form-control form-control-sm">
                      <option value="0">Seleccione un producto</option>
                      <?php foreach($listaProductos as $prod): ?>
                        <option value="<?= $prod['id_producto'] ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <div class="text-danger small error-producto1"></div>
                  </td>
                  <td>
                    <input type="number" name="cantidad1" id="cantidad1"
                           class="form-control form-control-sm" min="1">
                    <div class="text-danger small error-cantidad1"></div>
                  </td>
                  <td></td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>

            <input type="hidden" name="cantidadProductoActual" id="cantidadProductoActual" value="<?= $cantidadInicial ?>">

            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFila()">
              <i class="fas fa-plus mr-1"></i> Agregar producto
            </button>
          </div>

          <!-- Observaciones -->
          <div class="form-group">
            <label class="font-weight-bold">Observaciones <small class="text-muted">(opcional)</small></label>
            <textarea name="observaciones" class="form-control" rows="3"
                      placeholder="Indicá cualquier aclaración sobre tu pedido..."><?= htmlspecialchars($pedido['observaciones_internas'] ?? '') ?></textarea>
          </div>

          <!-- Botones -->
          <div class="row mt-4">
            <div class="col-sm-6">
              <a href="/clientes/misPedidos" class="btn btn-default btn-block">Cancelar</a>
            </div>
            <div class="col-sm-6">
              <button type="submit" class="btn btn-warning btn-block text-white">
                <i class="fas fa-save mr-1"></i> Guardar cambios
              </button>
            </div>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/footer.php'); ?>
<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
var productosDisponibles = <?= json_encode(array_map(fn($p) => [
    'id'     => $p['id_producto'],
    'nombre' => $p['nombre']
], $listaProductos)) ?>;

function agregarFila() {
  var total = parseInt(document.getElementById('cantidadProductoActual').value) + 1;
  document.getElementById('cantidadProductoActual').value = total;

  var opciones = '<option value="0">Seleccione un producto</option>';
  productosDisponibles.forEach(function(p) {
    opciones += '<option value="' + p.id + '">' + p.nombre + '</option>';
  });

  var fila = '<tr class="producto-row">' +
    '<td>' +
      '<select name="producto' + total + '" id="producto' + total + '" class="form-control form-control-sm">' +
        opciones +
      '</select>' +
      '<div class="text-danger small error-producto' + total + '"></div>' +
    '</td>' +
    '<td>' +
      '<input type="number" name="cantidad' + total + '" id="cantidad' + total + '" ' +
             'class="form-control form-control-sm" min="1" placeholder="0">' +
      '<div class="text-danger small error-cantidad' + total + '"></div>' +
    '</td>' +
    '<td>' +
      '<button type="button" class="btn-eliminar-prod" onclick="eliminarFila(this)">' +
        '<i class="fas fa-minus-square fa-lg" style="color:#dc3545;"></i>' +
      '</button>' +
    '</td>' +
  '</tr>';

  document.getElementById('tbody-productos').insertAdjacentHTML('beforeend', fila);
}

function eliminarFila(btn) {
  var filas = document.querySelectorAll('#tbody-productos .producto-row');
  if (filas.length <= 1) return;
  btn.closest('tr').remove();
  document.getElementById('cantidadProductoActual').value =
    document.querySelectorAll('#tbody-productos .producto-row').length;
}

function validarFormulario() {
  var valido = true;
  var productosSeleccionados = [];

  document.querySelectorAll('.text-danger.small').forEach(function(el) { el.textContent = ''; });

  // Validar turno
  var turno = document.querySelector('input[name="turno"]:checked');
  if (!turno) {
    document.getElementById('error-turno').textContent = 'Seleccioná un turno.';
    valido = false;
  }

  // Validar productos
  var total = parseInt(document.getElementById('cantidadProductoActual').value);
  for (var i = 1; i <= total; i++) {
    var prod = document.getElementById('producto' + i);
    var cant = document.getElementById('cantidad' + i);
    if (!prod || !cant) continue;

    if (!prod.value || prod.value == '0') {
      document.querySelector('.error-producto' + i).textContent = 'Seleccioná un producto.';
      valido = false;
    } else if (productosSeleccionados.includes(prod.value)) {
      document.querySelector('.error-producto' + i).textContent = 'Este producto ya fue agregado.';
      valido = false;
    } else {
      productosSeleccionados.push(prod.value);
    }

    if (!cant.value || parseInt(cant.value) < 1) {
      document.querySelector('.error-cantidad' + i).textContent = 'Ingresá una cantidad válida.';
      valido = false;
    }
  }

  return valido;
}
</script>
</body>
</html>