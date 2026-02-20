<?php require_once($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/maquinasDispensadoras/nuevoArreglo.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pagina = 'Arreglo de Máquina'; ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>
  <link rel="Agua del rey" href="/favicon.ico">
  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php'); ?>
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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
          <div class="col-sm-6"><h1><?=$pagina?></h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/maquinasDispensadoras/listado">Máquinas Dispensadoras</a></li>
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

              <form action="/maquinasDispensadoras/guardarNuevoArreglo" method="POST">
                <div class="card-body">

                  <div class="row">
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha"
                               class="form-control form-control-sm"
                               value="<?= date('Y-m-d') ?>">
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="idMaquina">Máquina Dispensadora</label>
                        <select id="idMaquina" name="idMaquina"
                                class="form-control form-control-sm select2"
                                style="width: 100%;">
                          <option value="">-- Seleccioná una máquina --</option>
                          <?php foreach ($maquinasDisponibles as $maq): ?>
                            <option value="<?= $maq['id_maquina'] ?>">
                              <?= htmlspecialchars($maq['numero_serie']) ?>
                              <?= $maq['numero_precinto'] ? ' - ' . htmlspecialchars($maq['numero_precinto']) : '' ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-sm-7">
                      <div class="form-group">
                        <label for="diagnostico">Diagnóstico</label>
                        <select id="diagnostico" name="diagnostico"
                                class="form-control form-control-sm select2"
                                style="width: 100%;">
                          <option value="">-- Seleccioná un diagnóstico --</option>
                          <option value="Rotura de válvula">Rotura de válvula</option>
                          <option value="Rotura de canilla">Rotura de canilla</option>
                          <option value="Rotura de bomba">Rotura de bomba</option>
                          <option value="Falla eléctrica">Falla eléctrica</option>
                          <option value="Pérdida de agua">Pérdida de agua</option>
                          <option value="Otro">Otro</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-sm-5">
                      <div class="form-group">
                        <label for="otroDiagnostico">Otro diagnóstico</label>
                        <input type="text" id="otroDiagnostico" name="otroDiagnostico"
                               class="form-control form-control-sm"
                               placeholder="Describí el problema si no está en la lista">
                      </div>
                    </div>
                  </div>

                </div>

                <div class="card-header"></div><br/>
                <div class="row align-items-center h-100 justify-content-center">
                  <div class="col-auto">
                    <a href="/maquinasDispensadoras/listado" class="btn btn-default">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
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
<script>
  $(function () {
    $('.select2').select2();
  });
</script>
</body>
</html>
