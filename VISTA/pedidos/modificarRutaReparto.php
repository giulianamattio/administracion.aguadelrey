<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  $idRuta = $_GET['idRutaReparto'];
  $pagina = 'Modificar ruta de reparto';
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
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
              <!--<div class="card-header">
                <h3 class="card-title">Quick Example</h3>
              </div>-->
              <!-- /.card-header -->
              <!-- form start -->
              <form>
                <div class="card-body">
                    
                <div class="row">
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control form-control-sm" placeholder="Enter email">  
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label for="turno">Turno</label>
                        <select class="form-control form-control-sm" id="turno">
                          <option value="turnoManiana" selected>Mañana</option>
                          <option value="turnoTarde">Tarde</option>
                        </select>
                      </div>
                    </div>
                  </div>



                <div class="card-header"></div> <br/>
                
                <label for="pedidos">Seleccione los pedidos</label>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <!-- checkbox -->
                            <div class="form-group clearfix">
                                <input type="checkbox" id="pedido1" checked>
                                <label for="pedido1" style="font-weight:normal;"> Juan - 25 de Mayo 258</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <!-- checkbox -->
                            <div class="form-group clearfix">
                                <input type="checkbox" id="pedido2" checked>
                                <label for="pedido2" style="font-weight:normal;"> Carlos - Av. Garibaldi 854</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <!-- checkbox -->
                            <div class="form-group clearfix">
                                <input type="checkbox" id="pedido3" checked>
                                <label for="pedido3" style="font-weight:normal;"> Mariela - Avellaneda 2545</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-4">
                            <!-- checkbox -->
                            <div class="form-group clearfix">
                                <input type="checkbox" id="pedido4" checked>
                                <label for="pedido4" style="font-weight:normal;"> Juan - 9 de Julio 855</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <!-- checkbox -->
                            <div class="form-group clearfix">
                                <input type="checkbox" id="pedido5" checked>
                                <label for="pedido5" style="font-weight:normal;"> Maria - Mitre 524</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <!-- checkbox -->
                            <div class="form-group clearfix">
                                <input type="checkbox" id="pedido6" checked>
                                <label for="pedido6" style="font-weight:normal;"> Carla - Mexico 2589</label>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-sm-4"><label>Cantidad total de Bidones:</label>&nbsp;&nbsp;<span >8</span></div>
                </div>

                <div class="row">
                    <div class="col-sm-8">
                        <button id="btnCalcularRepartos" type="button" class="btn btn-primary" onclick="calcularRutaRepartos()">
                            <span id="divLoad" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;">&nbsp;</span>
                            Calcular Ruta
                        </button>
                        
                    </div>
                </div>

                <br />
                
                <div class="row">
                  <div class="col-sm-8">
                    <table id="tblPedidos" class="table table-bordered">
                      <tr>
                          <th>Posición</th>
                          <th>Cliente</th>
                          <th>Domicilio</th>
                      </tr>
                      <tr>
                          <td>1</td>
                          <td>Carla</td>
                          <td>Mexico 2589</td>
                      </tr>
                      <tr>
                          <td>2</td>
                          <td>Maria</td>
                          <td>Mitre 524</td>
                      </tr>
                      <tr>
                          <td>3</td>
                          <td>Juan</td>
                          <td>25 de Mayo 258</td>
                      </tr>
                      <tr>
                          <td>4</td>
                          <td>Carlos</td>
                          <td>Av. Garibaldi 854</td>
                      </tr>
                      <tr>
                          <td>5</td>
                          <td>Mariela</td>
                          <td>Avellaneda 2545</td>
                      </tr>
                    </table>
                  </div>
                </div>

                <div class="card-header"></div> <br/>

                <div class="row align-items-center h-100 justify-content-center">
                    <div class="col-auto">
                        <button type="button" class="btn btn-default" onclick="javascript:volverAlListadoRutas()">Cancelar</button>
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

</body>
</html>