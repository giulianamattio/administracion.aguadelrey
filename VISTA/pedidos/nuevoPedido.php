<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $pagina = 'Nuevo pedido';
  ?>
  <title>Agua del Rey | <?php echo $pagina; ?></title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  ?>

  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

  <style>
    .table td, .table th {
        border-top: none;
    }
    .table thead th {
        border-bottom: none;
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
          <!-- left column -->
          <div class="col-md-12">
            <div class="card card-primary">
              <form onSubmit="return validarNuevoPedido(this)" method="post" action="/CONTROLADOR/pedidos/nuevoPedido.php" >
                <div class="card-body">
                    
                <div class="row">
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control form-control-sm">  
                      </div>
                    </div>

                    <div class="col-sm-4" data-select2-id="44">
                      <div class="form-group">
                        <label for="cliente">Cliente</label>
                        <select class="form-control form-control-sm select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="1" tabindex="-1" aria-hidden="true">
                            <option selected="selected" data-select2-id="3">Luciana</option>
                            <option data-select2-id="45">Jose</option>
                            <option data-select2-id="46">Maria</option>
                            <option data-select2-id="47">Ricardo</option>
                            <option data-select2-id="48">Carla</option>
                        </select>

                      </div>
                    </div>

                    
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="total">Monto total del pedido</label>
                        <input type="texto" id="total" name="total" class="form-control form-control-sm" placeholder="">  
                      </div>
                    </div>

                  </div>

                <label for="productos">Productos</label>
 
                <div class="col-md-8 card-body p-0">
                    <table id="lista_productos" class="table table-sm" style="border: none;"> 
                    <thead>
                        <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>  </th> 
                        </tr> 
                    </thead> 
                    <tbody> 
                        <tr>
                            <td>
                                <select class="form-control form-control-sm" id="productos">
                                    <option value="bidon20" selected>Bidón 20 litros</option>
                                    <option value="bidon10">Bidán 10 litros</option>
                                </select> 
                            </td> 
                            <td> 
                                <input type="number" class="form-control form-control-sm" name="cantidad" class="cantidad" />  
                            </td> 
                            <td>  
                                <i class="fas fa-minus-square fa-lg button_eliminar_producto" style="color: #dc3545;"></i>
                            </td> 
                        </tr> 
                    </tbody> 
                    <tfoot> 
                        <tr> 
                        <td colspan="3"> 
                            <div class="row align-items-center h-100 justify-content-center" style="margin-top: 5px;">
                                <i class="nav-icon fas fa-plus-square fa-lg button_agregar_producto" style="color: #28a745;"></i>
                            </div>
                        </td> 
                    </tr> 
                    </tfoot> 
                </table> 
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
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
})
</script>

</body>
</html>