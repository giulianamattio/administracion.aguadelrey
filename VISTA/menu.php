<?php
$idUsuario = 1;
$consultaUsuario = $safesql->query("SELECT usuarios.nombre as nombreUsuario, usuarios.idRol as idRol, 
roles.descripcion as nombreRol FROM usuarios INNER JOIN roles ON roles.idRol = usuarios.idRol 
WHERE usuarios.idUsuario = ?i", $idUsuario);	

if (mysqli_num_rows($consultaUsuario) != 0) {	
	$rsUsuario = mysqli_fetch_array($consultaUsuario, MYSQLI_ASSOC);
  $nombreUsuario = $rsUsuario["nombreUsuario"];
	$descripcionRol = $rsUsuario["nombreRol"];
	$rol = $rsUsuario["idRol"];
}
?>

<?php
$idUsuario = 1;
require($_SERVER["DOCUMENT_ROOT"].'/MODELO/UsuarioClass.php');
$usuario = usuario::buscarUsuarioPorId(1);
if($usuario){
  $nombreUsuario = $usuario->getNombre();
  
}else{
  echo 'El usuario no ha podido ser encontrado';
}
?>
                

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/principal" class="brand-link">
      <img src="/VISTA/imagenes/logoAgua.jpg" alt="Agua del Rey" class="brand-image img-circle " style="opacity: .8">
      <span class="brand-text font-weight-light"> Agua del Rey
      </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">
          <?php
            echo $nombreUsuario." - ".$descripcionRol;
          ?>
          </a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
         
          <li class="nav-header">PEDIDOS</li>
          <?php
          if($rol == 2 || $rol == 1){
            ?> 
            <li class="nav-item">
              <a href="/pedidos/gestionarRutaRepartos" class="nav-link">
                <i class="nav-icon fas fa-map-marked-alt"></i>
                <p>
                  Gestionar ruta de reparto
                </p>
              </a>
            </li>
            <?php   
          }
          ?>
          <li class="nav-item">
            <a href="/pedidos/nuevoPedido" class="nav-link">
              <i class="nav-icon fas fa-plus-square"></i>
              <p>
                Nuevo pedido
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/pedidos/listado" class="nav-link">
            <i class="nav-icon fas fa-list-ul"></i>
              <p>
                Listado de pedidos
                <span class="badge badge-info right">2</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/cobros/nuevoCobro" class="nav-link">
              <i class="nav-icon fas fa-plus-square"></i>
              <p>
                Nuevo cobro
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/cobros/listado" class="nav-link">
            <i class="nav-icon fas fa-list-ul"></i>
              <p>
                Listado de cobros
                <span class="badge badge-info right">2</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/pedidos/reporte" class="nav-link">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>
                Reportes
              </p>
            </a>
          </li>

          <li class="nav-header">CLIENTES</li>
          <li class="nav-item">
            <a href="/clientes/listaDeEspera" class="nav-link">
              <i class="nav-icon fas fa-list-ul"></i>
              <p>
                Lista de espera
                <span class="badge badge-info right">4</span>
              </p>
            </a>
          </li>


          <li class="nav-header">M&Aacute;QUINAS DISPENSADORAS</li>
          <li class="nav-item">
            <a href="/maquinasDispensadoras/nuevoArreglo" class="nav-link">
              <i class="nav-icon fas fa-tools"></i>
              <p>
                Nuevo arreglo
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/maquinasDispensadoras/listado" class="nav-link">
              <i class="nav-icon fas fa-list-ul"></i>
              <p>
                Listado Máquinas
                <span class="badge badge-info right">4</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/maquinasDispensadoras/reportes" class="nav-link">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>
                Reportes
              </p>
            </a>
          </li>

          <li class="nav-header">SINCRONIZACI&Oacute;N</li>
          <li class="nav-item">
            <a href="/sincronizacion" class="nav-link">
              <i class="nav-icon fas fa-sync-alt"></i>
              <p>
                Sincronizar
              </p>
            </a>
          </li>
          
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>