<?php
// Recuperar datos del usuario logueado desde la sesión
$idEmpleado = $_SESSION['id_empleado'] ?? null;
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? null;

// Si hay sesión activa, enriquecer con datos de BD
if ($idEmpleado) {
    $stmt = $conexionbd->prepare("
        SELECT e.nombre, e.apellido, r.nombre AS nombreRol, e.id_rol
        FROM usuario_empleado e
        INNER JOIN rol r ON r.id_rol = e.id_rol
        WHERE e.id_empleado = :id
    ");
    $stmt->execute([':id' => $idEmpleado]);
    $rsUsuario = $stmt->fetch();

    if ($rsUsuario) {
        $nombreUsuario   = $rsUsuario['nombre'] . ' ' . $rsUsuario['apellido'];
        $descripcionRol  = $rsUsuario['nombrerol'];
        $rol             = $rsUsuario['id_rol'];
    }
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
                      <?php
                      $stmtEnReparacion = $conexionbd->prepare("
                          SELECT COUNT(*) AS total
                          FROM maquina_dispensadora m
                          INNER JOIN estado_maquina e ON e.id_estado = m.id_estado
                          WHERE e.nombre = 'en_reparacion'
                      ");
                      $stmtEnReparacion->execute();
                      $totalEnReparacion = $stmtEnReparacion->fetch()['total'];
                      if ($totalEnReparacion > 0):
                      ?>
                          <span class="badge badge-warning right"><?= $totalEnReparacion ?></span>
                      <?php endif; ?>
                  </p>
              </a>
          </li>
          <li class="nav-item">
            <a href="/maquinasDispensadoras/reportes" class="nav-link">
              <i class="nav-icon fas fa-tools"></i>
              <p>
                Arreglos de Máquinas
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