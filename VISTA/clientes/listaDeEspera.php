<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $pagina = 'Lista de Espera';
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
            <h1>Lista de Espera</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Clientes</a></li>
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
                <h3 class="card-title">Clientes en Lista de Espera</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
              <?php
              
                require($_SERVER["DOCUMENT_ROOT"].'/MODELO/clienteClass.php');
                $clientes = cliente::buscarClientes();
                ?>
                <html>
                  <head></head>
                  <body>
                      <ul>
                      <?php foreach($clientes as $item): ?>
                      <li> <?php echo $item['nombreCompleto'] . ' - ' . $item['dni']; ?> </li>
                      <?php endforeach; ?>
                      </ul>
                  </body>
                </html>







                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 10px">#</th>
                      <th>Nombre y Apellido</th>
                      <th>Teléfono</th>
                      <th>Domicilio</th>
                      <th>Email</th>
                      <th>Acciones</th>
                      <!--<th>Progress</th>
                      <th style="width: 40px">Label</th>-->
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>1.</td>
                      <td>Juan Pérez</td>
                      <td> 458698 </td>
                      <td>
                          Bv. Buenos Aires 258
                        <!--<div class="progress progress-xs">
                          <div class="progress-bar progress-bar-danger" style="width: 55%"></div>
                        </div>-->
                      </td>
                      <td>juan@gmail.com</td>
                      <td>
                        <a href="" data-toggle="modal" data-target="#modalAprobarCliente"><i class="fas fa-check-square fa-lg" style="color: #28a745;"></i></a> &nbsp; 
                        <a href="" data-toggle="modal" data-target="#modalVerDatos"><i class="fas fa-info-circle fa-lg"  style="color: #17a2b8;"></i></a>
                        <!--<span class="badge bg-danger">55%</span>-->
                      </td>
                    </tr>
                    <tr>
                      <td>2.</td>
                      <td>María Giménez</td>
                      <td>15477858</td>
                      <td>
                          Colón 878
                        <!--<div class="progress progress-xs">
                          <div class="progress-bar bg-warning" style="width: 70%"></div>
                        </div>-->
                      </td>
                      <td>maria_gimenez@hotmail.com</td>
                      <td>
                        <a href="" data-toggle="modal" data-target="#modalAprobarCliente"><i class="fas fa-check-square fa-lg" style="color: #28a745;"></i></a> &nbsp; 
                        <a href="" data-toggle="modal" data-target="#modalVerDatos"><i class="fas fa-info-circle fa-lg"  style="color: #17a2b8;"></i></a>
                          <!--<span class="badge bg-warning">70%</span>-->
                        </td>
                    </tr>
                    <tr>
                      <td>3.</td>
                      <td>Roberto Gómez</td>
                      <td></td>
                      <td>
                          Bv. 25 de Mayo 1852
                        <!--<div class="progress progress-xs progress-striped active">
                          <div class="progress-bar bg-primary" style="width: 30%"></div>
                        </div>-->
                      </td>
                      <td>rober@hotmail.com</td>
                      <td>
                        <a href="" data-toggle="modal" data-target="#modalAprobarCliente"><i class="fas fa-check-square fa-lg" style="color: #28a745;"></i></a> &nbsp; 
                        <a href="" data-toggle="modal" data-target="#modalVerDatos"><i class="fas fa-info-circle fa-lg"  style="color: #17a2b8;"></i></a>
                          <!--<span class="badge bg-primary">30%</span>-->
                        </td>
                    </tr>
                    <tr>
                      <td>4.</td>
                      <td>Ricardo Cabrera</td>
                      <td>3564 15585698</td>
                      <td>
                          Mitre 2332
                        <!--<div class="progress progress-xs progress-striped active">
                          <div class="progress-bar bg-success" style="width: 90%"></div>
                        </div>-->
                      </td>
                      <td>rcabrera@hotmail.com</td>
                      <td>
                        <a href="" data-toggle="modal" data-target="#modalAprobarCliente">
                            <i class="fas fa-check-square fa-lg" style="color: #28a745;"></i></a> &nbsp; 
                        <a href="" data-toggle="modal" data-target="#modalVerDatos"><i class="fas fa-info-circle fa-lg"  style="color: #17a2b8;"></i></a>
                          <!--<span class="badge bg-success">90%</span>-->
                       </td>
                    </tr>
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

  
  <div class="modal fade" id="modalAprobarCliente">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Aprobar cliente</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>Se enviará un email a la siguiente dirección maria_gimenez@hotmail.com</p>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success">Aprobar</button>
        </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
    </div>


    <div class="modal fade" id="modalVerDatos">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Datos Cliente</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p> </p>
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