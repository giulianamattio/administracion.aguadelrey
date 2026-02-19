<?php require_once($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/maquinasDispensadoras/modificarMaquina_controlador.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pagina = 'Modificar Máquina'; ?>
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
              <!-- El action apunta al controlador que procesa el UPDATE -->
              <form action="/maquinasDispensadoras/guardarModificacionMaquina" method="POST">
                <!-- Campo oculto con el id para el UPDATE -->
                <input type="hidden" name="idMaquina" value="<?= $maquina['id_maquina'] ?>">

                <div class="card-body">
                  <div class="row">

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="descripcion">Descripción / Marca</label>
                        <input type="text" id="descripcion" name="descripcion"
                               class="form-control form-control-sm"
                               value="<?= htmlspecialchars($maquina['marca'] ?? '') ?>">
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="serie">Serie</label>
                        <input type="text" id="serie" name="serie"
                               class="form-control form-control-sm"
                               value="<?= htmlspecialchars($maquina['numero_serie']) ?>">
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="precinto">Precinto</label>
                        <input type="text" id="precinto" name="precinto"
                               class="form-control form-control-sm"
                               value="<?= htmlspecialchars($maquina['numero_precinto'] ?? '') ?>">
                      </div>
                    </div>

                  </div>

                  <div class="row">
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="tipo">Estado</label>
                        <select id="tipo" name="tipo" class="form-control form-control-sm">
                          <?php foreach ($estados as $est): ?>
                            <option value="<?= $est['id_estado'] ?>"
                              <?= ($est['id_estado'] == $maquina['id_estado']) ? 'selected' : '' ?>>
                              <?= htmlspecialchars($est['nombre']) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="card-header"></div><br/>
                <div class="row align-items-center h-100 justify-content-center">
                  <div class="col-auto">
                    <a href="/maquinasDispensadoras/listado" class="btn btn-default">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar cambios</button>
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
</body>
</html>
