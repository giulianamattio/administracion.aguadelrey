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
                    <a href="/pedidos/nuevoPedido" type="button" class="btn btn-block btn-success">Nuevo Pedido</a>
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
                      <th>Productos</th>
                      <th>Origen</th>
                      <th>Receptor</th>
                      <th>Monto</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    
                  <tr>
                      <td style="width: 10px">13</td>
                      <td>20/08/2021</td>
                      <td>Eduardo Fernández</td>
                      <td>
                        <ul>
                          <li>2 Bidón 20 litros</li>
                          <li>4 Cajas café</li>
                        </ul>
                      </td>
                      <td>Web</td>
                      <td>35698256</td>
                      <td>
                          <b>Total: </b> $1.500<br/>
                      </td>
                      <td><span class="badge badge-danger">Cancelado</span></td>
                      <td>
                        <!--<i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/pedidos/modificarPedido/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>-->
                      </td>
                    </tr>

                    <tr>
                        <td style="width: 10px">12</td>
                      <td>07/08/2021</td>
                      <td>María Gómez</td>
                      <td>
                        <ul>
                          <li>1 Bidón 10 litros</li>
                        </ul>
                      </td>
                      <td>Llamada telefónica</td>
                      <td>20355698</td>
                      <td>
                          <b>Total: </b> $500<br/>
                      </td>
                      <td><span class="badge badge-warning">Pendiente</span></td>
                      <td>
                        <i class="fas fa-minus-square fa-lg " style="color: #dc3545;"></i>
                        &nbsp;
                        <a href="/pedidos/modificarPedido/1">
                          <i class="fas fa-pen-square fa-lg" style="color: #ffc107;"></i>
                        </a>
                      </td>
                    </tr>


                    <tr>
                        <td style="width: 10px">11</td>
                      <td>27/07/2021</td>
                      <td>Eduardo Fernández</td>
                      <td>
                        <ul>
                          <li>2 Bidón 20 litros</li>
                          <li>4 Cajas café</li>
                        </ul>
                      </td>
                      <td>Web</td>
                      <td>35698256</td>
                      <td>
                          <b>Total: </b> $1.500<br/>
                          <b>Recibido: </b> $1.200
                      </td>
                      <td><span class="badge badge-success">Entregado</span></td>
                      <td>
                      </td>
                    </tr>


                    <tr>
                        <td style="width: 10px">10</td>
                      <td>01/07/2021</td>
                      <td>Ricardo Cabrera</td>
                      <td>
                        <b>Pedido:</b>
                        <ul>
                          <li>1 Bidón 10 litros</li>
                        </ul>
                        
                        <b>Entregado:</b>
                        <ul>
                          <li>2 Bidón 10 litros</li>
                        </ul>
                      </td>
                      <td>Whatsapp</td>
                      <td>25345698</td>
                      <td>
                          <b>Total: </b> $500<br/>
                          <b>Recibido: </b> $1000
                      </td>
                      <td><span class="badge badge-success">Entregado</span></td>
                      <td>
                      </td>
                    </tr>
                    
                  </tbody>
                </table>



                <?php

/*require_once '/MODELO/clienteClass.php';
$cliente = cliente::buscarCliente(1);
if($cliente){
   echo $cliente->getNombreCompleto();
   echo '<br />';
   echo $cliente->getDescripcion();
}else{
   echo 'El cliente no ha podido ser encontrado';
}*/

                ?>
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