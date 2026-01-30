<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $pagina = 'Listado';
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
              <li class="breadcrumb-item"><a href="#">Cobros</a></li>
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
                    <a href="/cobros/nuevoCobro" type="button" class="btn btn-block btn-success">Nuevo Cobro</a>
                </h3>
                </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 10px;">#</th>
                      <th>Fecha</th>
                      <th>Cliente</th>
                      <th>Monto</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    
                  <tr>
                      <td style="width: 10px">152</td>
                      <td>20/08/2021</td>
                      <td>Eduardo Fernández</td>
                      <td>
                          <b>Total: </b> $2.000<br/>
                      </td>
                      <td><span class="badge badge-danger">Cancelado</span></td>
                      <td>
                        <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/cobros/modificarCobro/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>
                      </td>
                    </tr>

                    <tr>
                      <td style="width: 10px">458</td>
                      <td>07/08/2021</td>
                      <td>María Gómez</td>
                      <td>
                          <b>Total: </b> $500<br/>
                      </td>
                      <td><span class="badge badge-warning">Pendiente</span></td>
                      <td>
                        <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/cobros/modificarCobro/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>
                      </td>
                    </tr>


                    <tr>
                      <td style="width: 10px">11</td>
                      <td>27/07/2021</td>
                      <td>Eduardo Fernández</td>
                      <td>
                          <b>Total: </b> $1.500<br/>
                      </td>
                      <td><span class="badge badge-success">Realizado</span></td>
                      <td>
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