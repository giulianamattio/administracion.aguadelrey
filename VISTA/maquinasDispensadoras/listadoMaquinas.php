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
  require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');
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
                    <?php
                    /*if (mysqli_num_rows($consultaMaquinas) != 0) {
                      $rsMaquinas = mysqli_fetch_array($consultaMaquinas, MYSQLI_ASSOC);

                      while($rsMaquinas!=NULL){
                        ?>
                        <tr>
                          <td><?=$rsMaquinas['numeroSerie']?></td>
                          <td><?=$rsMaquinas['numeroPrecinto']?></td>
                          <td><?=$rsMaquinas['tipoDisp']?></td>
                          <td><span class="badge badge-warning"><?=$rsMaquinas['estadoDisp']?></span></td>
                          <td>
                            <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                            &nbsp;
                            <a href="/maquinasDispensadoras/modificarMaquinaDispensadora/1">
                              <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                            </a>
                          </td>
                        </tr>
                        <?php                        
                        }
                      ?>

                      <?php
                    }else{
                      ?>
                      <tr>
                        <td>No se encontraron resultados</td>
                      </tr
                      <?php
                    }*/
                    ?>
                   <tr>
                      <td>R125899</td>
                      <td>356</td>
                      <td>Electrónica</td>
                      <td><span class="badge badge-warning">En reparación</span></td>
                      <td>
                        <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/maquinasDispensadoras/modificarMaquinaDispensadora/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>
                      </td>
                    </tr>
                    <tr>
                      <td>Q8569455</td>
                      <td>200</td>
                      <td>Manual</td>
                      <td><span class="badge badge-success">En domicilio</span></td>
                      <td>
                        <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/maquinasDispensadoras/modificarMaquinaDispensadora/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>
                      </td>
                    </tr>
                    <tr>
                      <td>R8996555</td>
                      <td>450</td>
                      <td>Electr&oacute;nica Digital</td>
                      <td><span class="badge badge-danger">Baja</span></td>
                      <td>
                        <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/maquinasDispensadoras/modificarMaquinaDispensadora/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>
                      </td>
                    </tr>
                    <tr>
                      <td>E89152555</td>
                      <td>145</td>
                      <td>Electr&oacute;nica Digital</td>
                      <td><span class="badge badge-info">En planta</span></td>
                      <td>
                      <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/maquinasDispensadoras/modificarMaquinaDispensadora/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>
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