<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $pagina = 'Sincronización';
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>

  <script>
    function sincronizarDatos(){
      var exito = true;
      if(exito == true){
        document.getElementById("mensajeExitoSincronizacion").style.display = "block";
        document.getElementById("mensajeErrorSincronizacion").style.display = "none";
      }else{
        document.getElementById("mensajeErrorSincronizacion").style.display = "block";
        document.getElementById("mensajeExitoSincronizacion").style.display = "none";
      }
    }
  </script>
  
  <style>
    .alert-success {
      color: #155724;
      background-color: #d4edda;
      border-color: #c3e6cb;
    }
    .alert-danger {
      color: #721c24;
      background-color: #f8d7da;
      border-color: #f5c6cb;
    }
  </style>
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
              <li class="breadcrumb-item"><a href="#">Datos</a></li>
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
              <div class="card-body">

              <div id="mensajeErrorSincronizacion" class="alert alert-danger alert-dismissable"  style="display: none;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Atenci&oacute;n!</strong> Ocurri&oacute; un error al sincronizar los datos. Int&eacute;ntelo nuevamente mas tarde.
              </div>

              <div id="mensajeExitoSincronizacion" class="alert alert-success alert-dismissable" style="display: none;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>&Eacute;xito!</strong> Los datos se sincronizaron correctamente.
              </div>

              <div class="row align-items-center h-100 justify-content-center">
                <button type="button" class="btn btn-primary" onclick="javascript: sincronizarDatos()"><i class="fas fa-sync-alt"></i> SINCRONIZAR DATOS</button>
              </div>
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