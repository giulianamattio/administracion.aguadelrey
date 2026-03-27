<?php
// ============================================================
//  VISTA/clientes/portal/home.php
//  Pantalla principal del portal de clientes
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Inicio</title>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/navbar.php'); ?>

<div class="portal-content">
  <div class="container">

    <div class="text-center mb-4">
      <h4 class="text-muted">
        Bienvenido/a, <strong class="text-dark">
          <?= htmlspecialchars($_SESSION['cliente_nombre'] . ' ' . $_SESSION['cliente_apellido']) ?>
        </strong>
      </h4>
      <p class="text-muted small">¿Qué querés hacer hoy?</p>
    </div>

    <div class="row justify-content-center">

      <!-- Nuevo Pedido -->
      <div class="col-md-5 mb-4">
        <a href="/clientes/nuevoPedido" class="card card-opcion bg-verde shadow d-block">
          <div class="card-body p-4">
            <p class="card-title mb-1">Nuevo Pedido</p>
            <p class="card-text">Realizá un nuevo pedido de nuestros productos y te lo llevamos.</p>
            <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
          </div>
        </a>
      </div>

      <!-- Mis Pedidos -->
      <div class="col-md-5 mb-4">
        <a href="/clientes/misPedidos" class="card card-opcion bg-celeste shadow d-block">
          <div class="card-body p-4">
            <p class="card-title mb-1">Mis pedidos</p>
            <p class="card-text">Accedé a la información de los pedidos realizados.</p>
            <div class="card-icon"><i class="fas fa-clipboard-list"></i></div>
          </div>
        </a>
      </div>

      <!-- Modificar Perfil -->
      <div class="col-md-5 mb-4">
        <a href="/clientes/perfil" class="card card-opcion bg-amarillo shadow d-block">
          <div class="card-body p-4">
            <p class="card-title mb-1">Modificar perfil</p>
            <p class="card-text">Modificá tus datos desde un simple formulario.</p>
            <div class="card-icon"><i class="fas fa-user-edit"></i></div>
          </div>
        </a>
      </div>

      <!-- Estadísticas -->
      <div class="col-md-5 mb-4">
        <a href="/clientes/misEstadisticas" class="card card-opcion bg-rojo shadow d-block">
          <div class="card-body p-4">
            <p class="card-title mb-1">Estadísticas</p>
            <p class="card-text">Accedé a las estadísticas en base a tu consumo.</p>
            <div class="card-icon"><i class="fas fa-chart-line"></i></div>
          </div>
        </a>
      </div>

    </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/footer.php'); ?>

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
