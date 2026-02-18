<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $pagina = 'Listado Máquinas';
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/encabezado.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/menu.php');

  require($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/maquinasDispensadoras/maquinas.php');
  ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><?=$pagina?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Máquinas Dispensadoras</a></li>
              <li class="breadcrumb-item active"><?=$pagina?></li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                    <a href="/maquinasDispensadoras/nuevaMaquinaDispensadora" type="button" class="btn btn-block btn-success">Nueva Máquina</a>
                </h3>
                </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Serie</th>
                      <th>Precinto</th>
                      <th>Tipo</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                    <tbody>
                    <?php if (count($listadoMaquinas) > 0): ?>
                        <?php foreach ($listadoMaquinas as $maquina): ?>
                        <tr>
                            <td><?= htmlspecialchars($maquina['numero_serie']) ?></td>
                            <td><?= htmlspecialchars($maquina['numero_precinto'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($maquina['marca'] ?? '-') ?></td>
                            <td>
                                <?php
                                // Asignamos color del badge según el estado
                                $badges = [
                                    'disponible'    => 'success',
                                    'en_cliente'    => 'info',
                                    'en_reparacion' => 'warning',
                                    'baja'          => 'danger',
                                ];
                                $color = $badges[$maquina['estado']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $color ?>">
                                    <?= htmlspecialchars($maquina['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/maquinasDispensadoras/modificarMaquinaDispensadora/<?= $maquina['id_maquina'] ?>">
                                    <i class="fas fa-pen-square fa-lg" style="color: #ffc107;" title="Modificar"></i>
                                </a>
                                &nbsp;
                                <a href="/maquinasDispensadoras/bajaMaquina/<?= $maquina['id_maquina'] ?>"
                                  onclick="return confirm('¿Confirmás la baja de esta máquina?')">
                                    <i class="fas fa-minus-square fa-lg" style="color: #dc3545;" title="Dar de baja"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron máquinas registradas.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                  <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                  <li class="page-item"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                </ul>
              </div>
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
        
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>


  <?php 
    require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php');
  ?>
    

</div>
<!-- ./wrapper -->

<?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php');
?>

</body>
</html>