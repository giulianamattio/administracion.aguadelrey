<?php
// ============================================================
//  VISTA/pedidos/gestionarRutaRepartos.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/CONTROLADOR/pedidos/listaRutas.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pagina = 'Rutas de Reparto'; ?>
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
              <li class="breadcrumb-item"><a href="#">Pedidos</a></li>
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
                <a href="/pedidos/nuevaRutaReparto" class="btn btn-success">
                  <i class="fas fa-plus mr-1"></i> Nueva ruta
                </a>
              </div>
              <div class="card-body">

                <?php if (isset($_GET['ok'])): ?>
                  <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_GET['ok'] === 'creada' ? '✅ Ruta creada correctamente.' : '✅ Ruta actualizada.' ?>
                  </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                  <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= htmlspecialchars($_GET['error']) ?>
                  </div>
                <?php endif; ?>

                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Fecha</th>
                      <th>Turno</th>
                      <th>Repartidor</th>
                      <th>Paradas</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($rutas) > 0): ?>
                      <?php foreach ($rutas as $i => $ruta): ?>
                        <tr>
                          <td><?= $i + 1 ?></td>
                          <td><?= date('d/m/Y', strtotime($ruta['fecha_planificada'])) ?></td>
                          <td><?= ucfirst($ruta['turno']) ?></td>
                          <td><?= htmlspecialchars($ruta['repartidor'] ?? '-') ?></td>
                          <td class="text-center"><?= $ruta['total_paradas'] ?></td>
                          <td>
                            <?php
                            $badgeClass = match($ruta['estado']) {
                                'planificada' => 'badge-warning',
                                'en_curso'    => 'badge-primary',
                                'finalizada'  => 'badge-success',
                                'cancelada'   => 'badge-danger',
                                default       => 'badge-secondary'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                              <?= ucfirst($ruta['estado']) ?>
                            </span>
                          </td>
                          <td>
                            <!-- Ver paradas -->
                            <a href="#"
                               class="btn-ver-paradas"
                               data-id="<?= $ruta['id_ruta'] ?>"
                               data-fecha="<?= date('d/m/Y', strtotime($ruta['fecha_planificada'])) ?>"
                               data-turno="<?= ucfirst($ruta['turno']) ?>"
                               title="Ver paradas">
                              <i class="fas fa-info-circle fa-lg" style="color:#17a2b8;"></i>
                            </a>
                            &nbsp;
                            <!-- Modificar -->
                            <a href="/pedidos/modificarRutaReparto/<?= $ruta['id_ruta'] ?>"
                               title="Modificar" style="color:#ffc107;">
                              <i class="fas fa-pen-square fa-lg"></i>
                            </a>
                            &nbsp;
                            <!-- Eliminar -->
                            <a href="#"
                               class="btn-eliminar"
                               data-id="<?= $ruta['id_ruta'] ?>"
                               data-fecha="<?= date('d/m/Y', strtotime($ruta['fecha_planificada'])) ?>"
                               data-turno="<?= ucfirst($ruta['turno']) ?>"
                               title="Eliminar" style="color:#dc3545;">
                              <i class="fas fa-minus-square fa-lg"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted">No hay rutas registradas.</td>
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

  <!-- Modal Ver Paradas -->
  <div class="modal fade" id="modalVerParadas">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="modalParadasTitulo">Paradas de la ruta</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body" id="modalParadasBody">
          <div class="text-center"><div class="spinner-border text-primary"></div></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Eliminar -->
  <div class="modal fade" id="modalEliminar">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Eliminar ruta</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form action="/pedidos/eliminarRuta" method="POST">
          <input type="hidden" name="idRuta" id="eliminar-id">
          <div class="modal-body">
            <p>¿Confirmás la eliminación de la ruta del <strong id="eliminar-fecha"></strong> - Turno <strong id="eliminar-turno"></strong>?</p>
            <p class="text-danger">Los pedidos volverán al estado pendiente.</p>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Eliminar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>

<script>
// Ver paradas — carga dinámica via fetch
document.querySelectorAll('.btn-ver-paradas').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const id    = this.dataset.id;
        const fecha = this.dataset.fecha;
        const turno = this.dataset.turno;

        document.getElementById('modalParadasTitulo').textContent =
            'Paradas del ' + fecha + ' - Turno ' + turno;
        document.getElementById('modalParadasBody').innerHTML =
            '<div class="text-center"><div class="spinner-border text-primary"></div></div>';
        $('#modalVerParadas').modal('show');

        fetch('/pedidos/paradasRuta?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    document.getElementById('modalParadasBody').innerHTML =
                        '<p class="text-muted">No hay paradas registradas.</p>';
                    return;
                }
                let html = '<ol>';
                data.forEach(function(p) {
                    html += '<li><strong>' + p.nombre + ' ' + p.apellido + '</strong> — '
                          + p.domicilio + ' — <em>' + p.observaciones + '</em></li>';
                });
                html += '</ol>';
                document.getElementById('modalParadasBody').innerHTML = html;
            });
    });
});

// Eliminar
document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('eliminar-id').value = this.dataset.id;
        document.getElementById('eliminar-fecha').textContent = this.dataset.fecha;
        document.getElementById('eliminar-turno').textContent = this.dataset.turno;
        $('#modalEliminar').modal('show');
    });
});
</script>
</body>
</html>
