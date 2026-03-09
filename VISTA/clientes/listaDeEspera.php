<?php require_once($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/clientes/listaEspera.php'); ?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pagina = 'Lista de Espera'; ?>
  <title>Agua del Rey | <?= $pagina ?></title>
  <link rel="Agua del rey" href="/favicon.ico">
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
              <li class="breadcrumb-item"><a href="#">Clientes</a></li>
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
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Clientes pendientes de aprobación</h3>
              </div>

              <div class="card-body">

                <?php if (isset($_GET['ok'])): ?>
                  <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_GET['ok'] === 'aprobado' ? '✅ Cliente aprobado correctamente.' : '❌ Cliente rechazado.' ?>
                  </div>
                <?php endif; ?>

                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nombre y Apellido</th>
                      <th>DNI / CUIT</th>
                      <th>Teléfono</th>
                      <th>Domicilio</th>
                      <th>Email</th>
                      <th>Fecha solicitud</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($clientesPendientes) > 0): ?>
                      <?php foreach ($clientesPendientes as $i => $c): ?>
                        <tr>
                          <td><?= $i + 1 ?></td>
                          <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>
                            <?php if ($c['razon_social']): ?>
                              <br><small class="text-muted"><?= htmlspecialchars($c['razon_social']) ?></small>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($c['dni_cuit']) ?></td>
                          <td><?= htmlspecialchars($c['telefono'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($c['domicilio'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($c['email']) ?></td>
                          <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                          <td>
                            <!-- Botón aprobar -->
                            <a href="#"
                               class="btn-aprobar"
                               data-id="<?= $c['id_espera'] ?>"
                               data-nombre="<?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>"
                               data-email="<?= htmlspecialchars($c['email']) ?>"
                               title="Aprobar">
                              <i class="fas fa-check-square fa-lg" style="color: #28a745;"></i>
                            </a>
                            &nbsp;
                            <!-- Botón ver datos -->
                            <a href="#"
                               class="btn-ver"
                               data-id="<?= $c['id_espera'] ?>"
                               data-nombre="<?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>"
                               data-dni="<?= htmlspecialchars($c['dni_cuit']) ?>"
                               data-email="<?= htmlspecialchars($c['email']) ?>"
                               data-tel="<?= htmlspecialchars($c['telefono'] ?? '-') ?>"
                               data-dom="<?= htmlspecialchars($c['domicilio'] ?? '-') ?>"
                               data-loc="<?= htmlspecialchars($c['localidad'] ?? '-') ?>"
                               data-prov="<?= htmlspecialchars($c['provincia'] ?? '-') ?>"
                               title="Ver datos">
                              <i class="fas fa-info-circle fa-lg" style="color: #17a2b8;"></i>
                            </a>
                            &nbsp;
                            <!-- Botón rechazar -->
                            <a href="#"
                               class="btn-rechazar"
                               data-id="<?= $c['id_espera'] ?>"
                               data-nombre="<?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>"
                               title="Rechazar">
                              <i class="fas fa-minus-square fa-lg" style="color: #dc3545;"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="8" class="text-center text-muted">No hay clientes pendientes de aprobación.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Modal Aprobar -->
  <div class="modal fade" id="modalAprobar">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Aprobar cliente</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form action="/clientes/aprobarCliente" method="POST">
          <input type="hidden" name="idEspera" id="aprobar-id">
          <div class="modal-body">
            <p>¿Confirmás la aprobación de <strong id="aprobar-nombre"></strong>?</p>
            <p class="text-muted">Se creará su cuenta y se le notificará a <span id="aprobar-email"></span>.</p>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Aprobar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Ver Datos -->
  <div class="modal fade" id="modalVerDatos">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Datos del cliente</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <table class="table table-sm">
            <tr><th>Nombre</th><td id="ver-nombre"></td></tr>
            <tr><th>DNI/CUIT</th><td id="ver-dni"></td></tr>
            <tr><th>Email</th><td id="ver-email"></td></tr>
            <tr><th>Teléfono</th><td id="ver-tel"></td></tr>
            <tr><th>Domicilio</th><td id="ver-dom"></td></tr>
            <tr><th>Localidad</th><td id="ver-loc"></td></tr>
            <tr><th>Provincia</th><td id="ver-prov"></td></tr>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Rechazar -->
  <div class="modal fade" id="modalRechazar">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Rechazar cliente</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form action="/clientes/rechazarCliente" method="POST">
          <input type="hidden" name="idEspera" id="rechazar-id">
          <div class="modal-body">
            <p>¿Confirmás el rechazo de <strong id="rechazar-nombre"></strong>?</p>
            <p class="text-danger">Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Rechazar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>

<script>
// Aprobar
document.querySelectorAll('.btn-aprobar').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('aprobar-id').value    = this.dataset.id;
        document.getElementById('aprobar-nombre').textContent = this.dataset.nombre;
        document.getElementById('aprobar-email').textContent  = this.dataset.email;
        $('#modalAprobar').modal('show');
    });
});

// Ver datos
document.querySelectorAll('.btn-ver').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('ver-nombre').textContent = this.dataset.nombre;
        document.getElementById('ver-dni').textContent    = this.dataset.dni;
        document.getElementById('ver-email').textContent  = this.dataset.email;
        document.getElementById('ver-tel').textContent    = this.dataset.tel;
        document.getElementById('ver-dom').textContent    = this.dataset.dom;
        document.getElementById('ver-loc').textContent    = this.dataset.loc;
        document.getElementById('ver-prov').textContent   = this.dataset.prov;
        $('#modalVerDatos').modal('show');
    });
});

// Rechazar
document.querySelectorAll('.btn-rechazar').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('rechazar-id').value    = this.dataset.id;
        document.getElementById('rechazar-nombre').textContent = this.dataset.nombre;
        $('#modalRechazar').modal('show');
    });
});
</script>
</body>
</html>
