<?php
// ============================================================
//  CONTROLADOR/clientes/geocodificarTodos.php  (Opción B)
//  La geocodificación la hace el NAVEGADOR llamando a Nominatim.
//  El servidor solo sirve la página y luego recibe los resultados.
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

// Traer clientes activos sin coordenadas
$stmt = $conexionbd->prepare("
    SELECT id_cliente, nombre, apellido, domicilio, localidad, provincia
    FROM cliente
    WHERE estado = 'activo'
      AND domicilio IS NOT NULL
      AND (latitud IS NULL OR longitud IS NULL)
    ORDER BY id_cliente
");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agua del Rey | Geocodificación de clientes</title>
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
          <div class="col-sm-6"><h1>Geocodificación de clientes</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/clientes/listaDeEspera">Clientes</a></li>
              <li class="breadcrumb-item active">Geocodificación</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">

            <?php if (empty($clientes)): ?>
              <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i>
                <strong>¡Todos los clientes activos ya tienen coordenadas!</strong>
                No hay nada por procesar.
              </div>
              <a href="/clientes/listaDeEspera" class="btn btn-default">Volver</a>

            <?php else: ?>
              <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>
                Se van a geocodificar <strong><?= count($clientes) ?></strong> cliente(s).
                La búsqueda de coordenadas se realiza desde tu navegador.
                <strong>No cerrés esta pestaña</strong> hasta que termine.
              </div>

              <!-- Barra de progreso -->
              <div class="progress mb-3" style="height:22px;">
                <div id="barraProgreso"
                     class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     style="width:0%">0 / <?= count($clientes) ?></div>
              </div>

              <!-- Tabla de resultados -->
              <table class="table table-sm table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>Cliente</th>
                    <th>Domicilio</th>
                    <th>Resultado</th>
                  </tr>
                </thead>
                <tbody id="tbodyResultados">
                  <?php foreach ($clientes as $c): ?>
                    <tr id="fila-<?= $c['id_cliente'] ?>">
                      <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></td>
                      <td><?= htmlspecialchars($c['domicilio']) ?></td>
                      <td>
                        <span class="badge badge-secondary">
                          <i class="fas fa-clock mr-1"></i>Pendiente
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>

              <div id="mensajeFinal" class="mt-3" style="display:none;"></div>
              <a href="/clientes/listaDeEspera" class="btn btn-default mt-2">Volver</a>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </section>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>

<?php if (!empty($clientes)): ?>
<script>
const clientes = <?= json_encode(array_values($clientes)) ?>;
const total    = clientes.length;
let procesados = 0;
let exitosos   = 0;
let fallidos   = 0;

function actualizarFila(idCliente, exito, texto) {
    const celda = document.querySelector(`#fila-${idCliente} td:last-child`);
    if (!celda) return;
    celda.innerHTML = exito
        ? `<span class="badge badge-success"><i class="fas fa-check mr-1"></i>${texto}</span>`
        : `<span class="badge badge-danger"><i class="fas fa-times mr-1"></i>${texto}</span>`;
}

function actualizarBarra() {
    const pct  = Math.round((procesados / total) * 100);
    const barra = document.getElementById('barraProgreso');
    barra.style.width = pct + '%';
    barra.textContent = `${procesados} / ${total}`;
    if (procesados === total) {
        barra.classList.remove('progress-bar-animated');
        barra.classList.replace('bg-primary', fallidos === 0 ? 'bg-success' : 'bg-warning');
    }
}

async function geocodificar(domicilio, localidad, provincia) {
    const query = encodeURIComponent(`${domicilio}, ${localidad}, ${provincia}, Argentina`);
    const url   = `https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=1&countrycodes=ar`;
    try {
        const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data && data.length > 0) {
            return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
        }
        return null;
    } catch (e) {
        return null;
    }
}

async function guardarEnBD(idCliente, lat, lng) {
    const fd = new FormData();
    fd.append('id_cliente', idCliente);
    fd.append('lat', lat);
    fd.append('lng', lng);
    try {
        const res  = await fetch('/clientes/guardarCoordenadas', { method: 'POST', body: fd });
        const data = await res.json();
        return data.ok === true;
    } catch (e) {
        return false;
    }
}

async function procesarTodos() {
    for (const c of clientes) {
        const celda = document.querySelector(`#fila-${c.id_cliente} td:last-child`);
        if (celda) celda.innerHTML = `<span class="badge badge-warning"><i class="fas fa-spinner fa-spin mr-1"></i>Buscando...</span>`;

        const coords = await geocodificar(
            c.domicilio,
            c.localidad || 'San Francisco',
            c.provincia || 'Córdoba'
        );

        if (coords) {
            const guardado = await guardarEnBD(c.id_cliente, coords.lat, coords.lng);
            if (guardado) {
                actualizarFila(c.id_cliente, true, `${coords.lat.toFixed(5)}, ${coords.lng.toFixed(5)}`);
                exitosos++;
            } else {
                actualizarFila(c.id_cliente, false, 'Error al guardar en BD');
                fallidos++;
            }
        } else {
            actualizarFila(c.id_cliente, false, 'No encontrado');
            fallidos++;
        }

        procesados++;
        actualizarBarra();

        // Respetar límite Nominatim: 1 req/segundo
        if (procesados < total) await new Promise(r => setTimeout(r, 1100));
    }

    const div = document.getElementById('mensajeFinal');
    div.style.display = 'block';
    div.innerHTML = fallidos === 0
        ? `<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>
           <strong>¡Proceso completado!</strong> ${exitosos} cliente(s) geocodificados correctamente.</div>`
        : `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>
           Proceso completado: <strong>${exitosos} exitosos</strong>, <strong>${fallidos} fallidos</strong>.
           Los fallidos podés cargarlos manualmente desde la edición del cliente.</div>`;
}

window.addEventListener('load', procesarTodos);
</script>
<?php endif; ?>
</body>
</html>