<?php
// ============================================================
//  VISTA/pedidos/nuevaRutaReparto.php  (versión con geocodificación)
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

                <?php if ($sinCoordenadas > 0): ?>
                  <div class="alert alert-warning">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    <strong><?= $sinCoordenadas ?> pedido(s)</strong> no tienen coordenadas geocodificadas.
                    El cálculo óptimo usará solo los que sí las tienen.
                    <a href="/clientes/geocodificarTodos" class="btn btn-sm btn-warning ml-2" target="_blank">
                      Geocodificar ahora
                    </a>
                  </div>
                <?php endif; ?>

                <div class="alert alert-light border">
                  <i class="fas fa-warehouse mr-1 text-primary"></i>
                  <strong>Punto de partida:</strong> <?= htmlspecialchars($galpon['dir']) ?>
                  <span class="text-muted ml-2">(<?= $galpon['lat'] ?>, <?= $galpon['lng'] ?>)</span>
                </div>

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
                                   id="p<?= $p['id_pedido'] ?>"
                                   data-lat="<?= $p['latitud'] ?? '' ?>"
                                   data-lng="<?= $p['longitud'] ?? '' ?>"
                                   data-nombre="<?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>"
                                   data-domicilio="<?= htmlspecialchars($p['domicilio'] ?? 'Sin domicilio') ?>"
                                   data-id="<?= $p['id_pedido'] ?>">
                            <label for="p<?= $p['id_pedido'] ?>" style="font-weight:normal; margin-left:6px;">
                              <strong>#<?= $p['id_pedido'] ?></strong>
                              — <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                              — <?= htmlspecialchars($p['domicilio'] ?? 'Sin domicilio') ?>
                              <?php if ($p['latitud']): ?>
                                <i class="fas fa-map-marker-alt text-success ml-1" title="Coordenadas disponibles"></i>
                              <?php else: ?>
                                <i class="fas fa-map-marker-alt text-danger ml-1" title="Sin coordenadas"></i>
                              <?php endif; ?>
                              <?php if ($p['observaciones_cliente']): ?>
                                <br><small class="text-muted ml-4"><?= htmlspecialchars($p['observaciones_cliente']) ?></small>
                              <?php endif; ?>
                              <?php if ($p['turno']): ?>
                                <br><small class="text-muted ml-4">Turno deseado: <?= htmlspecialchars($p['turno']) ?></small>
                              <?php endif; ?>
                            </label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <div class="row mb-3">
                      <div class="col-sm-8">
                        <span class="text-muted">Pedidos seleccionados: </span>
                        <strong id="contadorSeleccionados">0</strong>
                        <span class="text-muted ml-3">Con coordenadas: </span>
                        <strong id="contadorConCoords" class="text-success">0</strong>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-sm-8">
                        <button type="button" id="btnCalcular" class="btn btn-primary" disabled>
                          <i class="fas fa-route mr-1"></i> Calcular Ruta Óptima
                        </button>
                        <small class="text-muted ml-2">
                          Algoritmo: vecino más cercano desde el galpón
                        </small>
                      </div>
                    </div>

                    <!-- Tabla resultado -->
                    <div id="resultadoRuta" style="display:none;">
                      <hr>
                      <h6><i class="fas fa-route mr-1 text-success"></i>
                        Orden óptimo sugerido:
                        <span id="distanciaTotal" class="badge badge-info ml-2"></span>
                      </h6>
                      <table class="table table-bordered table-sm" style="max-width:700px;">
                        <thead class="thead-light">
                          <tr>
                            <th>Pos.</th>
                            <th>Cliente</th>
                            <th>Domicilio</th>
                            <th>Dist. desde anterior</th>
                          </tr>
                        </thead>
                        <tbody id="tbodyRuta"></tbody>
                      </table>
                      <div id="alertaSinCoords" class="alert alert-warning d-none">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Algunos pedidos no tienen coordenadas y se agregaron al final sin optimizar.
                      </div>
                      <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Podés modificar el orden manualmente desde "Editar ruta" luego de guardar.
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
// Coordenadas del galpón (punto de partida)
const GALPON = { lat: <?= $galpon['lat'] ?>, lng: <?= $galpon['lng'] ?> };

// Fórmula Haversine en JS
function distanciaKm(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2)
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
            * Math.sin(dLng/2) * Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// Algoritmo Nearest Neighbor en JS
function rutaOptima(origen, destinos) {
    let pendientes = [...destinos];
    let ordenados  = [];
    let actual     = origen;

    while (pendientes.length > 0) {
        let minDist  = Infinity;
        let minIndex = 0;

        pendientes.forEach((d, i) => {
            const dist = distanciaKm(actual.lat, actual.lng, d.lat, d.lng);
            if (dist < minDist) { minDist = dist; minIndex = i; }
        });

        const sig = { ...pendientes[minIndex], distancia: minDist };
        ordenados.push(sig);
        actual = sig;
        pendientes.splice(minIndex, 1);
    }
    return ordenados;
}

// Actualizar contadores
function actualizarContadores() {
    const checks = document.querySelectorAll('.chk-pedido:checked');
    const conCoords = Array.from(checks).filter(c => c.dataset.lat && c.dataset.lng).length;
    document.getElementById('contadorSeleccionados').textContent = checks.length;
    document.getElementById('contadorConCoords').textContent     = conCoords;
    document.getElementById('btnCalcular').disabled = checks.length === 0;
    document.getElementById('resultadoRuta').style.display = 'none';
}

document.querySelectorAll('.chk-pedido').forEach(c => c.addEventListener('change', actualizarContadores));

// Calcular ruta óptima
document.getElementById('btnCalcular').addEventListener('click', function() {
    const checks = Array.from(document.querySelectorAll('.chk-pedido:checked'));

    const conCoords = checks
        .filter(c => c.dataset.lat && c.dataset.lng)
        .map(c => ({
            id:        c.dataset.id,
            nombre:    c.dataset.nombre,
            domicilio: c.dataset.domicilio,
            lat:       parseFloat(c.dataset.lat),
            lng:       parseFloat(c.dataset.lng),
        }));

    const sinCoords = checks
        .filter(c => !c.dataset.lat || !c.dataset.lng)
        .map(c => ({
            id:        c.dataset.id,
            nombre:    c.dataset.nombre,
            domicilio: c.dataset.domicilio,
            lat:       null,
            lng:       null,
            distancia: null,
        }));

    // Aplicar algoritmo solo a los que tienen coordenadas
    const optimizados = conCoords.length > 0
        ? rutaOptima(GALPON, conCoords)
        : [];

    // Los sin coordenadas van al final
    const resultado = [...optimizados, ...sinCoords];

    // Calcular distancia total
    let totalKm = optimizados.reduce((sum, p) => sum + (p.distancia || 0), 0);
    document.getElementById('distanciaTotal').textContent =
        'Distancia estimada: ' + totalKm.toFixed(1) + ' km';

    // Mostrar tabla
    const tbody = document.getElementById('tbodyRuta');
    tbody.innerHTML = '';
    resultado.forEach(function(p, i) {
        const dist = p.distancia !== null
            ? p.distancia.toFixed(2) + ' km'
            : '<span class="text-muted">—</span>';
        tbody.innerHTML += `<tr>
            <td>${i + 1}</td>
            <td>${p.nombre}</td>
            <td>${p.domicilio}</td>
            <td>${dist}</td>
        </tr>`;
    });

    document.getElementById('alertaSinCoords').classList.toggle('d-none', sinCoords.length === 0);
    document.getElementById('resultadoRuta').style.display = 'block';
});
</script>
</body>
</html>

