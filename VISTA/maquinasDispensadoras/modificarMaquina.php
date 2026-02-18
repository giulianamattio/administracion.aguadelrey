<?php require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $idMaquina = $_GET["idMaquina"];
  $pagina = 'Modificar máquina';
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>

  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

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
          <!-- left column -->
          <div class="col-md-12">
            <div class="card card-primary">
              <form>
                <div class="card-body">
                    
                <div class="row">
                  
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="descripcion">Descripci&oacute;n</label>
                        <input type="text" id="descripcion" name="descripcion" class="form-control form-control-sm"> 
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="serie">Serie</label>
                        <input type="text" id="serie" name="serie" class="form-control form-control-sm">  
                      </div>
                    </div>
                    
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="precinto">Precinto</label>
                        <input type="text" id="precinto" name="precinto" class="form-control form-control-sm">  
                      </div>
                    </div>
                  </div>


                  <div class="row">
                    
                    <div class="col-sm-4" data-select2-id="44">
                      <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" class="form-control form-control-sm">
                            <option value="seleccioneTipo" selected>Tipo de Máquina</option>
                            <option value="">Manual</option>
                            <option value="">Eléctrica</option>
                            <option value="">Eléctrica Digital</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="precio">Precio</label>
                        <input type="text" id="precio" name="precio" class="form-control form-control-sm"> 
                      </div>
                    </div>

                  </div>
            
                <div class="card-header"></div> <br/>

                <div class="row align-items-center h-100 justify-content-center">
                    <div class="col-auto">
                        <button type="button" class="btn btn-default" onclick="javascript:volverHome()">Cancelar</button>
                        <button type="submit" class="btn btn-success ">Guardar</button>
                    </div>
                </div>

              </form>
            </div>
            <!-- /.card -->

          </div>
        </div>
      </div>
      </section>

  </div>
  
  
  
  <?php 
    require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php');
  ?>
    

</div>
<!-- ./wrapper -->

<?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php');
?>
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script src="/VISTA/script/productos.js"></script>

</body>
</html>