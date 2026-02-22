<?php require_once($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/maquinasDispensadoras/arreglos.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pagina = 'Arreglos de Máquinas'; ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>
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
              <li class="breadcrumb-item"><a href="/maquinasDispensadoras/listado">Máquinas Dispensadoras</a></li>
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
                <a href="/maquinasDispensadoras/nuevoArreglo" class="btn btn-success">
                  Nuevo Arreglo
                </a>
              </div>
              <div class="card-body">
                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>Máquina (Serie)</th>
                      <th>Precinto</th>
                      <th>Reparador</th>
                      <th>Fecha ingreso</th>
                      <th>Fecha egreso</th>
                      <th>Descripción</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($listadoArreglos) > 0): ?>
                      <?php foreach ($listadoArreglos as $arreglo): ?>
                        <tr>
                          <td><?= htmlspecialchars($arreglo['numero_serie']) ?></td>
                          <td><?= htmlspecialchars($arreglo['numero_precinto'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($arreglo['nombre_empleado'] . ' ' . $arreglo['apellido_empleado']) ?></td>
                          <td><?= htmlspecialchars($arreglo['fecha_ingreso']) ?></td>
                          <td><?= $arreglo['fecha_egreso'] ? htmlspecialchars($arreglo['fecha_egreso']) : '-' ?></td>
                          <td><?= htmlspecialchars($arreglo['descripcion']) ?></td>
                          <td>
                            <?php if ($arreglo['resuelto']): ?>
                              <span class="badge badge-success">Resuelto</span>
                            <?php else: ?>
                              <span class="badge badge-warning">En curso</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if (!$arreglo['resuelto']): ?>
                              <a href="/maquinasDispensadoras/finalizarArreglo/<?= $arreglo['id_arreglo'] ?>"
                                 title="Finalizar arreglo">
                                <i class="fas fa-check-square fa-lg" style="color: #28a745;"></i>
                              </a>
                            <?php else: ?>
                              <span class="text-muted"><i class="fas fa-check fa-lg"></i></span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="8" class="text-center">No hay arreglos registrados.</td>
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

  <?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php'); ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php'); ?>
</body>
</html>
