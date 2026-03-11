<?php
// ============================================================
//  VISTA/pedidos/nuevaRutaReparto.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/CONTROLADOR/pedidos/datosNuevaRuta.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pagina = 'Nueva ruta de reparto'; ?>
  <title>Agua del Rey | <?= $pagina ?></title>
  <link rel="icon" href="/favicon.ico">
  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php'); ?>
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
          <div class="col-sm-6"><h1><?= $pagina ?></h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/pedidos/gestionarRutaRepartos">Pedidos</a></li>
              <li class="breadcrumb-item active"><?= $pagina ?></li>
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
              <div class="card-body">

                <?php if (isset($_GET['error'])): ?>
                  <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <?= htmlspecialchars($_GET['error']) ?>
                  </div>
                <?php endif; ?>

                <form action="/pedidos/guardarNuevaRuta" method="POST" id="formNuevaRuta">

                  <div class="row">
                    <div class="col-sm-3">
                      <div class="form-group">
                        <label>Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control form-control-sm"
                               min="<?= date('Y-m-d') ?>"
                               value="<?= date('Y-m-d') ?>" required>
                      </div>
                    </div>
                    <div class="col-sm-3">
                      <div class="form-group">
                        <label>Turno <span class="text-danger">*</span></label>
                        <select name="turno" class="form-control form-control-sm" required>
                          <option value="mañana">Mañana</option>
                          <option value="tarde">Tarde</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label>Repartidor</label>
                        <select name="id_repartidor" class="form-control form-control-sm">
                          <option value="">-- Sin asignar --</option>
                          <?php foreach ($repartidores as $rep): ?>
                            <option value="<?= $rep['id_empleado'] ?>">
                              <?= htmlspecialchars($rep['nombre'] . ' ' . $rep['apellido']) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Observaciones</label>
                    <input type="text" name="observaciones" class="form-control form-control-sm"
                           placeholder="Notas generales de la ruta...">
                  </div>

                  <hr>
                  <label>Seleccioná los pedidos a incluir</label>

                  <?php if (count($pedidosPendientes) === 0): ?>
                    <div class="alert alert-info">
                      <i class="fas fa-info-circle mr-1"></i>
                      No hay pedidos pendientes sin ruta asignada.
                    </div>
                  <?php else: ?>
                    <div class="row" id="listaPedidos">
                      <?php foreach ($pedidosPendientes as $p): ?>
                        <div class="col-sm-6">
                          <div class="form-group clearfix">
                            <input type="checkbox"
                                   name="pedidos[]"
                                   value="<?= $p['id_pedido'] ?>"
                                   class="chk-pedido"
                                   id="p<?= $p['id_pedido'] ?>">
                            <label for="p<?= $p['id_pedido'] ?>" style="font-weight:normal; margin-left:6px;">
                              <strong>#<?= $p['id_pedido'] ?></strong>
                              — <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                              — <?= htmlspecialchars($p['domicilio'] ?? 'Sin domicilio') ?>
                              <?php if ($p['observaciones_cliente']): ?>
                                <br><small class="text-muted ml-4"><?= htmlspecialchars($p['observaciones_cliente']) ?></small>
                              <?php endif; ?>
                            </label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <div class="row mb-3">
                      <div class="col-sm-6">
                        <span class="text-muted">Pedidos seleccionados: </span>
                        <strong id="contadorSeleccionados">0</strong>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-sm-8">
                        <button type="button" id="btnCalcular" class="btn btn-primary" disabled>
                          <i class="fas fa-route mr-1"></i> Calcular Ruta Óptima
                        </button>
                      </div>
                    </div>

                    <!-- Tabla resultado del cálculo -->
                    <div id="resultadoRuta" style="display:none;">
                      <hr>
                      <h6><i class="fas fa-sort-numeric-down mr-1 text-success"></i>
                        Orden sugerido (ordenado por numeración de calle):
                      </h6>
                      <table class="table table-bordered table-sm" style="max-width:600px;">
                        <thead class="thead-light">
                          <tr>
                            <th>Posición</th>
                            <th>Cliente</th>
                            <th>Domicilio</th>
                          </tr>
                        </thead>
                        <tbody id="tbodyRuta"></tbody>
                      </table>
                      <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        El orden se calcula automáticamente por número de calle.
                        Podés modificarlo manualmente desde la opción "Editar ruta" luego de guardar.
                      </small>
                    </div>

                  <?php endif; ?>

                  <div class="card-header mt-3"></div>
                  <div class="row align-items-center mt-3">
                    <div class="col-auto">
                      <a href="/pedidos/gestionarRutaRepartos" class="btn btn-default">Cancelar</a>
                      <button type="submit" class="btn btn-success ml-2"
                              <?= count($pedidosPendientes) === 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-save mr-1"></i> Guardar
                      </button>
                    </div>
                  </div>

                </form>
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
// Datos de pedidos para el cálculo en JS
const pedidosData = <?= json_encode(array_map(function($p) {
    return [
        'id'        => $p['id_pedido'],
        'nombre'    => $p['nombre'] . ' ' . $p['apellido'],
        'domicilio' => $p['domicilio'] ?? 'Sin domicilio',
    ];
}, $pedidosPendientes)) ?>;

// Contador de seleccionados
document.querySelectorAll('.chk-pedido').forEach(function(chk) {
    chk.addEventListener('change', actualizarContador);
});

function actualizarContador() {
    const seleccionados = document.querySelectorAll('.chk-pedido:checked').length;
    document.getElementById('contadorSeleccionados').textContent = seleccionados;
    document.getElementById('btnCalcular').disabled = seleccionados === 0;
    // Ocultar resultado anterior si cambia selección
    document.getElementById('resultadoRuta').style.display = 'none';
}

// Calcular ruta óptima en el cliente (JS)
document.getElementById('btnCalcular').addEventListener('click', function() {
    const idsSeleccionados = Array.from(
        document.querySelectorAll('.chk-pedido:checked')
    ).map(c => parseInt(c.value));

    const seleccionados = pedidosData.filter(p => idsSeleccionados.includes(p.id));

    // Algoritmo: extraer número de calle y ordenar ascendente
    function extraerNumero(dom) {
        const match = dom.match(/\d+/);
        return match ? parseInt(match[0]) : 9999;
    }

    seleccionados.sort((a, b) => extraerNumero(a.domicilio) - extraerNumero(b.domicilio));

    // Mostrar tabla resultado
    const tbody = document.getElementById('tbodyRuta');
    tbody.innerHTML = '';
    seleccionados.forEach(function(p, i) {
        tbody.innerHTML += `<tr>
            <td>${i + 1}</td>
            <td>${p.nombre}</td>
            <td>${p.domicilio}</td>
        </tr>`;
    });

    document.getElementById('resultadoRuta').style.display = 'block';
});
</script>
</body>
</html>
