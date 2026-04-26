<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $pagina = 'Sincronización';
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>
  <title>Agua del Rey | <?= $pagina ?></title>
  <link rel="icon" href="/favicon.ico">
  <style>
    .sync-card        { border-left: 4px solid #dee2e6; border-radius: 6px; transition: border-color .2s; }
    .sync-card.ok     { border-color: #28a745; }
    .sync-card.error  { border-color: #dc3545; }
    .sync-card.waiting{ border-color: #6c757d; }
    .sync-card.running{ border-color: #007bff; }
    .stat-badge       { font-size: 13px; padding: 3px 9px; border-radius: 20px; }
    .log-box          { background:#f8f9fa; border:1px solid #dee2e6; border-radius:4px;
                        font-size:12px; font-family:monospace; max-height:220px;
                        overflow-y:auto; padding:10px 12px; }
    .log-box .ok      { color:#155724; }
    .log-box .err     { color:#721c24; }
    .log-box .info    { color:#004085; }
    .log-box .warn    { color:#856404; }
    .spinner-sm       { width:14px; height:14px; border-width:2px; }
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
          <div class="col-sm-6"><h1><?= $pagina ?></h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Datos</a></li>
              <li class="breadcrumb-item active"><?= $pagina ?></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">

        <!-- Alerta global -->
        <div id="alertaGlobal" style="display:none;" class="alert alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <span id="alertaGlobalMsg"></span>
        </div>

        <div class="row">

          <!-- ── CARD PRODUCTOS ───────────────────────────────────────────── -->
          <div class="col-md-6 mb-4">
            <div class="card sync-card waiting" id="cardProductos">
              <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                  <i class="fas fa-box mr-2 text-primary"></i> Productos
                </h5>
                <span id="estadoProductos" class="badge badge-secondary stat-badge">En espera</span>
              </div>
              <div class="card-body">

                <!-- Estado del archivo -->
                <div id="archivoProductos" class="mb-3">
                  <small class="text-muted">Verificando archivo…</small>
                </div>

                <!-- Estadísticas -->
                <div id="statsProductos" style="display:none;" class="mb-3">
                  <span class="badge badge-success stat-badge mr-1">
                    <i class="fas fa-plus mr-1"></i>
                    Nuevos: <strong id="pNuevos">0</strong>
                  </span>
                  <span class="badge badge-info stat-badge mr-1">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Actualizados: <strong id="pActualizados">0</strong>
                  </span>
                  <span class="badge badge-warning stat-badge mr-1">
                    <i class="fas fa-calendar-times mr-1"></i>
                    Dados de baja: <strong id="pBajas">0</strong>
                  </span>
                  <span class="badge badge-danger stat-badge">
                    <i class="fas fa-times mr-1"></i>
                    Errores: <strong id="pErrores">0</strong>
                  </span>
                </div>

                <!-- Log -->
                <div id="logProductos" class="log-box" style="display:none;"></div>

              </div>
            </div>
          </div>

          <!-- ── CARD CLIENTES ────────────────────────────────────────────── -->
          <div class="col-md-6 mb-4">
            <div class="card sync-card waiting" id="cardClientes">
              <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                  <i class="fas fa-users mr-2 text-success"></i> Clientes
                </h5>
                <span id="estadoClientes" class="badge badge-secondary stat-badge">En espera</span>
              </div>
              <div class="card-body">

                <div id="archivoClientes" class="mb-3">
                  <small class="text-muted">Verificando archivo…</small>
                </div>

                <div id="statsClientes" style="display:none;" class="mb-3">
                  <span class="badge badge-success stat-badge mr-1">
                    <i class="fas fa-plus mr-1"></i>
                    Nuevos: <strong id="cNuevos">0</strong>
                  </span>
                  <span class="badge badge-info stat-badge mr-1">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Actualizados: <strong id="cActualizados">0</strong>
                  </span>
                  <span class="badge badge-danger stat-badge">
                    <i class="fas fa-times mr-1"></i>
                    Errores: <strong id="cErrores">0</strong>
                  </span>
                </div>

                <div id="logClientes" class="log-box" style="display:none;"></div>

              </div>
            </div>
          </div>

        </div>

        <!-- ── BOTÓN SINCRONIZAR ─────────────────────────────────────────── -->
        <div class="row">
          <div class="col-12 text-center">
            <button type="button" class="btn btn-primary btn-lg px-5" id="btnSincronizar">
              <i class="fas fa-sync-alt mr-2"></i> SINCRONIZAR DATOS
            </button>
            <p class="text-muted mt-2" style="font-size:12px;" id="ultimaSincLabel"></p>
          </div>
        </div>

      </div>
    </section>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>

<script>
// ── Helpers ──────────────────────────────────────────────────────────────────
function setEstado(tipo, estado) {
  const badges = { ok:'badge-success', error:'badge-danger', running:'badge-primary', waiting:'badge-secondary', skip:'badge-warning' };
  const labels = { ok:'Completado', error:'Error', running:'Sincronizando…', waiting:'En espera', skip:'Sin archivo' };
  const card   = document.getElementById('card' + tipo);
  card.className = 'card sync-card ' + (estado === 'running' ? 'running' : estado === 'ok' ? 'ok' : estado === 'error' ? 'error' : 'waiting');
  const badge  = document.getElementById('estado' + tipo);
  badge.className = 'badge stat-badge ' + (badges[estado] || 'badge-secondary');
  badge.textContent = labels[estado] || estado;
}

function addLog(tipo, msg, clase) {
  const box = document.getElementById('log' + tipo);
  box.style.display = 'block';
  const line = document.createElement('div');
  line.className = clase || 'info';
  line.textContent = msg;
  box.appendChild(line);
  box.scrollTop = box.scrollHeight;
}

function mostrarStats(tipo, data) {
  document.getElementById('stats' + tipo).style.display = 'block';
  if (tipo === 'Productos') {
    document.getElementById('pNuevos').textContent      = data.nuevos      ?? 0;
    document.getElementById('pActualizados').textContent = data.actualizados ?? 0;
    document.getElementById('pBajas').textContent       = data.bajas        ?? 0;
    document.getElementById('pErrores').textContent     = data.errores      ?? 0;
  } else {
    document.getElementById('cNuevos').textContent      = data.nuevos      ?? 0;
    document.getElementById('cActualizados').textContent = data.actualizados ?? 0;
    document.getElementById('cErrores').textContent     = data.errores      ?? 0;
  }
}

function mostrarAlerta(tipo, msg) {
  const el  = document.getElementById('alertaGlobal');
  const txt = document.getElementById('alertaGlobalMsg');
  el.className = 'alert alert-dismissible alert-' + tipo;
  txt.innerHTML = msg;
  el.style.display = 'block';
  el.scrollIntoView({ behavior:'smooth' });
}

// ── Verificar archivos al cargar ──────────────────────────────────────────────
async function verificarArchivos() {
  try {
    const r    = await fetch('/CONTROLADOR/sincronizacion/verificarArchivos.php');
    const data = await r.json();

    ['Productos','Clientes'].forEach(tipo => {
      const key = tipo.toLowerCase();
      const el  = document.getElementById('archivo' + tipo);
      if (data[key] && data[key].existe) {
        el.innerHTML = `<i class="fas fa-file-csv text-success mr-1"></i>
          <strong>${data[key].nombre}</strong>
          <span class="text-muted ml-2">${data[key].filas} filas · ${data[key].peso}</span>`;
      } else {
        el.innerHTML = `<i class="fas fa-times-circle text-danger mr-1"></i>
          <span class="text-muted">Archivo no encontrado en <code>/imports/</code></span>`;
        setEstado(tipo, 'skip');
      }
    });

    if (data.ultima_sync) {
      document.getElementById('ultimaSincLabel').textContent =
        'Última sincronización: ' + data.ultima_sync;
    }
  } catch(e) {
    console.error('Error al verificar archivos', e);
  }
}

// ── Sincronizar un tipo ───────────────────────────────────────────────────────
async function sincronizarTipo(tipo) {
  setEstado(tipo, 'running');
  document.getElementById('log'   + tipo).innerHTML = '';
  document.getElementById('stats' + tipo).style.display = 'none';
  addLog(tipo, '⏳ Iniciando sincronización…', 'info');

  try {
    const r    = await fetch('/CONTROLADOR/sincronizacion/sincronizar' + tipo + '.php', { method:'POST' });
    const data = await r.json();

    if (data.error) {
      setEstado(tipo, 'error');
      addLog(tipo, '❌ ' + data.error, 'err');
      return false;
    }

    // Mostrar log línea por línea
    (data.log || []).forEach(entry => {
      addLog(tipo, entry.msg, entry.tipo === 'error' ? 'err' : entry.tipo === 'warn' ? 'warn' : entry.tipo === 'ok' ? 'ok' : 'info');
    });

    mostrarStats(tipo, data);
    setEstado(tipo, data.errores > 0 ? 'error' : 'ok');
    return data.errores === 0;

  } catch(e) {
    setEstado(tipo, 'error');
    addLog(tipo, '❌ Error de conexión: ' + e.message, 'err');
    return false;
  }
}

// ── Botón principal ───────────────────────────────────────────────────────────
document.getElementById('btnSincronizar').addEventListener('click', async function() {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-sm mr-2"></span> Sincronizando…';
  document.getElementById('alertaGlobal').style.display = 'none';

  const okP = await sincronizarTipo('Productos');
  const okC = await sincronizarTipo('Clientes');

  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> SINCRONIZAR DATOS';

  if (okP && okC) {
    mostrarAlerta('success', '<strong>¡Éxito!</strong> Productos y clientes sincronizados correctamente.');
  } else if (!okP && !okC) {
    mostrarAlerta('danger', '<strong>Error</strong> en ambas sincronizaciones. Revisá los logs.');
  } else {
    mostrarAlerta('warning', '<strong>Atención:</strong> Una sincronización tuvo errores. Revisá los logs.');
  }

  // Actualizar label de última sync
  document.getElementById('ultimaSincLabel').textContent =
    'Última sincronización: ' + new Date().toLocaleString('es-AR');

  verificarArchivos();
});

// ── Init ──────────────────────────────────────────────────────────────────────
verificarArchivos();
</script>
</body>
</html>
