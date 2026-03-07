<?php require_once($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/maquinasDispensadoras/finalizarArreglo.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pagina = 'Finalizar Arreglo'; ?>
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
              <li class="breadcrumb-item"><a href="/maquinasDispensadoras/reportes">Arreglos</a></li>
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
            <div class="card card-success">
              <form action="/maquinasDispensadoras/guardarFinalizacionArreglo" method="POST">
                <input type="hidden" name="idArreglo" value="<?= $arreglo['id_arreglo'] ?>">
                <input type="hidden" name="idMaquina" value="<?= $arreglo['id_maquina'] ?>">

                <div class="card-body">

                  <!-- Info de solo lectura -->
                  <div class="row mb-3">
                    <div class="col-sm-4">
                      <label>Máquina (Serie)</label>
                      <p class="form-control-static font-weight-bold">
                        <?= htmlspecialchars($arreglo['numero_serie']) ?>
                        <?= $arreglo['numero_precinto'] ? ' - ' . htmlspecialchars($arreglo['numero_precinto']) : '' ?>
                      </p>
                    </div>
                    <div class="col-sm-4">
                      <label>Fecha ingreso</label>
                      <p class="form-control-static"><?= htmlspecialchars($arreglo['fecha_ingreso']) ?></p>
                    </div>
                    <div class="col-sm-4">
                      <label>Diagnóstico</label>
                      <p class="form-control-static"><?= htmlspecialchars($arreglo['descripcion']) ?></p>
                    </div>
                  </div>

                  <hr>

                  <!-- Campos editables para cerrar el arreglo -->
                  <div class="row">
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="fecha_egreso">Fecha de egreso</label>
                        <input type="date" id="fecha_egreso" name="fecha_egreso"
                               class="form-control form-control-sm"
                               value="<?= date('Y-m-d') ?>" required>
                      </div>
                    </div>
                    <div class="col-sm-8">
                      <div class="form-group">
                        <label for="observaciones">Observaciones finales</label>
                        <input type="text" id="observaciones" name="observaciones"
                               class="form-control form-control-sm"
                               value="<?= htmlspecialchars($arreglo['observaciones'] ?? '') ?>"
                               placeholder="Descripción de la solución aplicada">
                      </div>
                    </div>
                  </div>

                </div>

                <div class="card-header"></div><br/>
                <div class="row align-items-center h-100 justify-content-center">
                  <div class="col-auto">
                    <a href="/maquinasDispensadoras/reportes" class="btn btn-default">Cancelar</a>
                    <button type="submit" class="btn btn-success">Marcar como resuelto</button>
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
</body>
</html>
