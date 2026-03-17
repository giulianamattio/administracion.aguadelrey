<?php
// ============================================================
//  VISTA/clientes/portal/layout/navbar.php
//  Navbar del portal de clientes — se incluye en todas las páginas
// ============================================================
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/clientes/home">
      <img src="/VISTA/imagenes/logoAgua.jpg" alt="Agua del Rey" height="40" class="mr-2">
      <span class="font-weight-bold text-primary">Sistema de Clientes</span>
    </a>

    <div class="d-flex align-items-center ml-auto">
      <!-- Buscador -->
      <form class="form-inline mr-3" action="/clientes/buscar" method="GET">
        <div class="input-group input-group-sm">
          <input class="form-control" type="search" name="q" placeholder="Buscar"
                 value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="submit">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </form>

      <!-- Dropdown usuario -->
      <div class="dropdown">
        <a class="btn btn-link text-dark dropdown-toggle text-decoration-none"
           href="#" role="button" data-toggle="dropdown">
          <i class="fas fa-user-circle fa-lg mr-1"></i>
          <?= htmlspecialchars(($_SESSION['cliente_nombre'] ?? '') . ' ' . ($_SESSION['cliente_apellido'] ?? '')) ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow">
          <a class="dropdown-item" href="/clientes/perfil">
            <i class="fas fa-user-edit mr-2 text-muted"></i> Modificar perfil
          </a>
          <a class="dropdown-item" href="/clientes/cambiarPassword">
            <i class="fas fa-key mr-2 text-muted"></i> Modificar Contraseña
          </a>
          <a class="dropdown-item" href="/clientes/misPedidos">
            <i class="fas fa-list-alt mr-2 text-muted"></i> Mis pedidos
          </a>
          <a class="dropdown-item" href="/clientes/estadisticas">
            <i class="fas fa-chart-line mr-2 text-muted"></i> Estadísticas
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="/clientes/logout">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>
