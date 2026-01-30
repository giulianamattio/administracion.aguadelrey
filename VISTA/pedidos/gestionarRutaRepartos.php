<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $pagina = 'Rutas de Reparto';
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/encabezado.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/menu.php');

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
              <li class="breadcrumb-item"><a href="#">Pedidos</a></li>
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
                    <a href="/pedidos/nuevaRutaReparto" type="button" class="btn btn-block btn-success">Nueva ruta</a>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 10px">#</th>
                      <th>Fecha</th>
                      <th>Turno</th>
                      <th>Total KM</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>1.</td>
                      <td>24/07/2021</td>
                      <td>Mañana</td>
                      <td>50 Km</td>
                      <td>
                        <a href="" data-toggle="modal" data-target="#modalVerPedidos"><i class="fas fa-info-circle fa-lg"  style="color: #17a2b8;"></i></a>
                        &nbsp;
                        <a href="/pedidos/modificarRutaReparto/1" style="color: #ffc107;"><i class="fas fa-pen-square fa-lg"></i></a>
                      </td>
                    </tr>
                    <tr>
                      <td>2.</td>
                      <td>24/07/2021</td>
                      <td>Tarde</td>
                      <td>78 Km</td>
                      <td>
                        <a href="" data-toggle="modal" data-target="#modalVerPedidos"><i class="fas fa-info-circle fa-lg"  style="color: #17a2b8;"></i></a>
                        &nbsp;
                        <a href="/pedidos/modificarRutaReparto/2"  style="color: #ffc107;"><i class="fas fa-pen-square fa-lg"></i></a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->

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


  <div class="modal fade" id="modalVerPedidos">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Pedidos del reparto 24/07/2021 - Turno Mañana</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>
                <ol>
                    <li>Juan Pérez - Mitre 258 - 4 Bidones </li>
                    <li>Maria Gómez - Bv. Buenos Aires 1200 - 2 Bidones</li>
                    <li>Mariela Fernandez - Colón - 1 dispenser, 2 bidones </li>
                </ol>
            </p>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default">Cerrar</button>
        </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
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