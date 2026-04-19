<?php
// ============================================================
//  VISTA/pedidos/modificarRutaReparto.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

$idRuta = (int)($_GET['idRutaReparto'] ?? 0);
if (!$idRuta) {
    header('Location: /pedidos/gestionarRutaRepartos');
    exit;
}

// Traer datos de la ruta
$stmtRuta = $conexionbd->prepare("
    SELECT r.id_ruta, r.id_repartidor, r.estado, r.fecha_planificada,
           r.turno, r.observaciones
    FROM ruta_reparto r
    WHERE r.id_ruta = :id
");
$stmtRuta->execute([':id' => $idRuta]);
$ruta = $stmtRuta->fetch();

if (!$ruta) {
    header('Location: /pedidos/gestionarRutaRepartos');
    exit;
}

// Paradas actuales de la ruta con datos del cliente
$stmtParadas = $conexionbd->prepare("
    SELECT pr.id_parada, pr.orden, pr.id_pedido,
           c.nombre, c.apellido, c.domicilio,
           c.latitud, c.longitud
    FROM parada_ruta pr
    JOIN pedido p ON p.id_pedido = pr.id_pedido
    JOIN cliente c ON c.id_cliente = p.id_cliente
    WHERE pr.id_ruta = :id
    ORDER BY pr.orden ASC
");
$stmtParadas->execute([':id' => $idRuta]);
$paradas = $stmtParadas->fetchAll();

// Pedidos pendientes no incluidos en esta ruta (para poder agregar)
$stmtDisponibles = $conexionbd->prepare("
    SELECT p.id_pedido, c.nombre, c.apellido, c.domicilio,
           c.latitud, c.longitud, p.observaciones_cliente, t.nombre AS turno
    FROM pedido p
    JOIN cliente c ON c.id_cliente = p.id_cliente
    LEFT JOIN turno t ON t.id_turno = p.id_turno_deseado
    WHERE p.id_estado = 1
      AND p.id_pedido NOT IN (
          SELECT pr2.id_pedido FROM parada_ruta pr2
          JOIN ruta_reparto r2 ON r2.id_ruta = pr2.id_ruta
          WHERE r2.estado IN (1, 3)
      )
    ORDER BY c.apellido ASC
");
$stmtDisponibles->execute();
$disponibles = $stmtDisponibles->fetchAll();

// Repartidores activos
$stmtRep = $conexionbd->prepare("
    SELECT id_empleado, nombre, apellido
    FROM usuario_empleado
    WHERE activo = TRUE
    ORDER BY apellido ASC
");
$stmtRep->execute();
$repartidores = $stmtRep->fetchAll();

// Coordenadas del galpón
$stmtG = $conexionbd->prepare("
    SELECT clave, valor FROM configuracion_sistema
    WHERE clave IN ('galpon_latitud','galpon_longitud','galpon_direccion')
");
$stmtG->execute();
$cfg = [];
foreach ($stmtG->fetchAll() as $row) $cfg[$row['clave']] = $row['valor'];
$galpon = [
    'lat' => (float)($cfg['galpon_latitud']  ?? -31.4267),
    'lng' => (float)($cfg['galpon_longitud'] ?? -62.0834),
    'dir' => $cfg['galpon_direccion'] ?? 'Galpón Agua del Rey',
];

$pagina = 'Modificar ruta de reparto';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
          <div class="col-sm-6"><h1><?= $pagina ?> #<?= $idRuta ?></h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/pedidos/gestionarRutaRepartos">Rutas</a></li>
              <li class="breadcrumb-item active"><?= $pagina ?></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="card card-primary">
          <div class="card-body">

            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="alert alert-light border">
              <i class="fas fa-warehouse mr-1 text-primary"></i>
              <strong>Punto de partida:</strong> <?= htmlspecialchars($galpon['dir']) ?>
            </div>

            <form action="/pedidos/guardarModificacionRuta" method="POST">
              <input type="hidden" name="id_ruta" value="<?= $idRuta ?>">

              <div class="row">
                <div class="col-sm-3">
                  <div class="form-group">
                    <label>Fecha <span class="text-danger">*</span></label>
                    <input type="date" name="fecha" class="form-control form-control-sm"
                           value="<?= date('Y-m-d', strtotime($ruta['fecha_planificada'])) ?>" required>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="form-group">
                    <label>Turno <span class="text-danger">*</span></label>
                    <select name="turno" class="form-control form-control-sm" required>
                      <option value="mañana"  <?= $ruta['turno'] === 'mañana'  ? 'selected' : '' ?>>Mañana</option>
                      <option value="tarde"   <?= $ruta['turno'] === 'tarde'   ? 'selected' : '' ?>>Tarde</option>
                    </select>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>Repartidor</label>
                    <select name="id_repartidor" class="form-control form-control-sm">
                      <option value="">-- Sin asignar --</option>
                      <?php foreach ($repartidores as $rep): ?>
                        <option value="<?= $rep['id_empleado'] ?>"
                          <?= $ruta['id_repartidor'] == $rep['id_empleado'] ? 'selected' : '' ?>>
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
                       value="<?= htmlspecialchars($ruta['observaciones'] ?? '') ?>">
              </div>

              <hr>

              <!-- Paradas actuales con drag & drop para reordenar -->
              <label>
                Paradas de la ruta <small class="text-muted">(arrastrá para reordenar)</small>
                <span id="distanciaTotal" class="badge badge-info ml-2" style="display:none;"></span>
              </label>
              <ul id="listaParadas" class="list-group mb-3" style="max-width:700px;">
                <?php foreach ($paradas as $p): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center"
                      data-id="<?= $p['id_pedido'] ?>"
                      data-lat="<?= $p['latitud'] ?? '' ?>"
                      data-lng="<?= $p['longitud'] ?? '' ?>">
                    <span>
                      <i class="fas fa-grip-vertical text-muted mr-2" style="cursor:grab;"></i>
                      <strong>#<?= $p['id_pedido'] ?></strong>
                      — <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                      — <?= htmlspecialchars($p['domicilio'] ?? '') ?>
                      <?php if ($p['latitud']): ?>
                        <i class="fas fa-map-marker-alt text-success ml-1" title="Coordenadas OK"></i>
                      <?php else: ?>
                        <i class="fas fa-map-marker-alt text-danger ml-1" title="Sin coordenadas"></i>
                      <?php endif; ?>
                    </span>
                    <button type="button" class="btn btn-xs btn-danger btn-eliminar-parada"
                            data-id="<?= $p['id_pedido'] ?>">
                      <i class="fas fa-times"></i>
                    </button>
                  </li>
                <?php endforeach; ?>
              </ul>

              <!-- Recalcular orden -->
              <button type="button" id="btnRecalcular" class="btn btn-sm btn-primary mb-3">
                <i class="fas fa-route mr-1"></i> Recalcular orden óptimo
              </button>

              <!-- Agregar pedidos disponibles -->
              <?php if (!empty($disponibles)): ?>
              <div class="card card-outline card-secondary mb-3" style="max-width:700px;">
                <div class="card-header"><h6 class="mb-0">Agregar pedidos pendientes</h6></div>
                <div class="card-body p-2" id="contenedorPendientes">
                  <?php foreach ($disponibles as $d): ?>
                    <div class="form-check">
                      <input type="checkbox" class="form-check-input chk-agregar"
                             id="ag<?= $d['id_pedido'] ?>"
                             data-id="<?= $d['id_pedido'] ?>"
                             data-lat="<?= $d['latitud'] ?? '' ?>"
                             data-lng="<?= $d['longitud'] ?? '' ?>"
                             data-nombre="<?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?>"
                             data-domicilio="<?= htmlspecialchars($d['domicilio'] ?? '') ?>">
                      <label class="form-check-label" for="ag<?= $d['id_pedido'] ?>">
                        <strong>#<?= $d['id_pedido'] ?></strong>
                        — <?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?>
                        — <?= htmlspecialchars($d['domicilio'] ?? 'Sin domicilio') ?>
                        <?php if ($d['observaciones_cliente']): ?>
                          <small class="text-muted"> · <?= htmlspecialchars($d['observaciones_cliente']) ?></small>
                        <?php endif; ?>
                        <?php if ($d['turno']): ?>
                          <small class="text-muted"> · Turno deseado: <?= htmlspecialchars($d['turno']) ?></small>
                        <?php endif; ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                  <button type="button" id="btnAgregar" class="btn btn-sm btn-secondary mt-2">
                    <i class="fas fa-plus mr-1"></i> Agregar seleccionados
                  </button>
                </div>
              </div>
              <?php endif; ?>

              <!-- Campo hidden con el orden final de pedidos -->
              <input type="hidden" name="pedidos_orden" id="pedidosOrden">
              <input type="hidden" name="km_recorridos" id="kmRecorridos">

              <div class="card-header"></div>
              <div class="row align-items-center mt-3">
                <div class="col-auto">
                  <a href="/pedidos/gestionarRutaRepartos" class="btn btn-default">Cancelar</a>
                  <button type="submit" class="btn btn-success ml-2" id="btnGuardar">
                    <i class="fas fa-save mr-1"></i> Guardar cambios
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>

<!-- SortableJS para drag & drop -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
const GALPON = { lat: <?= $galpon['lat'] ?>, lng: <?= $galpon['lng'] ?> };

const lista = document.getElementById('listaParadas');

// ── Haversine ────────────────────────────────────────────────────────────────
function distKm(lat1, lng1, lat2, lng2) {
    const R = 6371, dLat=(lat2-lat1)*Math.PI/180, dLng=(lng2-lng1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// ── Distancia total (galpón → parada1 → parada2 → … ) ───────────────────────
function calcularDistanciaTotal() {
    const items = Array.from(lista.querySelectorAll('li'));
    const badge = document.getElementById('distanciaTotal');
    const conCoords = items.filter(li => li.dataset.lat && li.dataset.lng);

    if (conCoords.length === 0) {
        badge.style.display = 'none';
        document.getElementById('kmRecorridos').value = 0; // ← agregar
        return;
    }

    let totalKm = 0;
    let actual  = GALPON;
    conCoords.forEach(li => {
        const lat = parseFloat(li.dataset.lat);
        const lng = parseFloat(li.dataset.lng);
        totalKm  += distKm(actual.lat, actual.lng, lat, lng);
        actual    = { lat, lng };
    });

    badge.style.display = '';
    badge.textContent   = 'Distancia estimada: ' + totalKm.toFixed(1) + ' km';
    document.getElementById('kmRecorridos').value = totalKm.toFixed(2); // ← agregar
}

// ── Actualizar campo hidden + distancia ─────────────────────────────────────
function actualizarOrden() {
    const ids = Array.from(lista.querySelectorAll('li')).map(li => li.dataset.id);
    document.getElementById('pedidosOrden').value = ids.join(',');
    calcularDistanciaTotal();
}

// ── Eliminar parada ──────────────────────────────────────────────────────────
lista.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-eliminar-parada');
    if (!btn) return;
    if (lista.querySelectorAll('li').length <= 1) {
        alert('La ruta debe tener al menos una parada.');
        return;
    }
    const li        = btn.closest('li');
    const idPed     = li.dataset.id;
    const nombre    = li.dataset.nombre    || '';
    const domicilio = li.dataset.domicilio || '';
    const lat       = li.dataset.lat       || '';
    const lng       = li.dataset.lng       || '';

    const contenedor = document.getElementById('contenedorPendientes');
    if (contenedor && !contenedor.querySelector(`[data-id="${idPed}"]`)) {
        const div = document.createElement('div');
        div.className  = 'form-check';
        div.dataset.id = idPed;
        div.innerHTML  = `
            <input type="checkbox" class="form-check-input chk-agregar"
                   id="ag${idPed}" data-id="${idPed}"
                   data-lat="${lat}" data-lng="${lng}"
                   data-nombre="${nombre}" data-domicilio="${domicilio}">
            <label class="form-check-label" for="ag${idPed}">
              <strong>#${idPed}</strong> — ${nombre} — ${domicilio}
            </label>`;
        const btnAgregar = document.getElementById('btnAgregar');
        contenedor.insertBefore(div, btnAgregar);
    }
    li.remove();
    actualizarOrden();
});

// ── Agregar pedidos seleccionados ────────────────────────────────────────────
document.getElementById('btnAgregar')?.addEventListener('click', function() {
    const checks = document.querySelectorAll('.chk-agregar:checked');
    if (checks.length === 0) { alert('Seleccioná al menos un pedido.'); return; }

    checks.forEach(c => {
        if (lista.querySelector(`li[data-id="${c.dataset.id}"]`)) {
            c.closest('.form-check')?.remove();
            return;
        }
        const tieneCoords = c.dataset.lat && c.dataset.lng;
        const li = document.createElement('li');
        li.className         = 'list-group-item d-flex justify-content-between align-items-center';
        li.dataset.id        = c.dataset.id;
        li.dataset.lat       = c.dataset.lat;
        li.dataset.lng       = c.dataset.lng;
        li.dataset.nombre    = c.dataset.nombre;
        li.dataset.domicilio = c.dataset.domicilio;
        li.innerHTML = `
            <span>
              <i class="fas fa-grip-vertical text-muted mr-2" style="cursor:grab;"></i>
              <strong>#${c.dataset.id}</strong>
              — ${c.dataset.nombre}
              — ${c.dataset.domicilio}
              <i class="fas fa-map-marker-alt ${tieneCoords ? 'text-success' : 'text-danger'} ml-1"></i>
            </span>
            <button type="button" class="btn btn-xs btn-danger btn-eliminar-parada" data-id="${c.dataset.id}">
              <i class="fas fa-times"></i>
            </button>`;
        lista.appendChild(li);
        c.closest('.form-check')?.remove();
    });
    actualizarOrden();
});

// ── Nearest Neighbor ─────────────────────────────────────────────────────────
function nearestNeighbor(origen, destinos) {
    let pend=[...destinos], ord=[], act=origen;
    while(pend.length){
        let minD=Infinity, minI=0;
        pend.forEach((d,i)=>{ const d2=distKm(act.lat,act.lng,d.lat,d.lng); if(d2<minD){minD=d2;minI=i;} });
        ord.push(pend[minI]); act=pend[minI]; pend.splice(minI,1);
    }
    return ord;
}

// ── Recalcular orden óptimo ──────────────────────────────────────────────────
document.getElementById('btnRecalcular').addEventListener('click', function() {
    const items     = Array.from(lista.querySelectorAll('li'));
    const conCoords = items.filter(li => li.dataset.lat && li.dataset.lng)
        .map(li => ({ el: li, lat: parseFloat(li.dataset.lat), lng: parseFloat(li.dataset.lng) }));
    const sinCoords = items.filter(li => !li.dataset.lat || !li.dataset.lng);

    if (conCoords.length === 0) { alert('Ninguna parada tiene coordenadas para optimizar.'); return; }

    const ordenado = nearestNeighbor(GALPON, conCoords);
    lista.innerHTML = '';
    [...ordenado.map(d => d.el), ...sinCoords].forEach(el => lista.appendChild(el));
    actualizarOrden();
});

// ── SortableJS (drag & drop) ─────────────────────────────────────────────────
Sortable.create(lista, { animation: 150, handle: '.fa-grip-vertical', onEnd: actualizarOrden });

// ── Init ─────────────────────────────────────────────────────────────────────
actualizarOrden();
</script>

</body>
</html>

