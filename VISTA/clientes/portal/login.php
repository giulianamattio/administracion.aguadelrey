<!DOCTYPE html>
<?php
// ============================================================
//  VISTA/clientes/portal/login.php
//  Login exclusivo para clientes
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// Si ya tiene sesión activa de cliente, redirigir al home
if (!empty($_SESSION['cliente_id'])) {
    header('Location: /clientes/home');
    exit;
}
?>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Acceso Clientes</title>
  <style>
    body { background-color: #f0f2f5; }
    .login-card {
      max-width: 420px;
      margin: 80px auto;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    }
    .login-header {
      background: linear-gradient(135deg, #007bff, #0056b3);
      padding: 30px;
      text-align: center;
      color: #fff;
    }
    .login-header img { height: 70px; border-radius: 8px; margin-bottom: 12px; }
    .login-header h4 { margin: 0; font-weight: 700; }
    .login-header small { opacity: 0.85; }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-header">
    <img src="/VISTA/imagenes/logoAgua.jpg" alt="Agua del Rey">
    <h4>Agua del Rey</h4>
    <small>Portal de Clientes</small>
  </div>

  <div class="card border-0">
    <div class="card-body p-4">

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          <?= htmlspecialchars($_GET['error']) ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-info">
          <i class="fas fa-info-circle mr-1"></i>
          Sesión cerrada correctamente.
        </div>
      <?php endif; ?>

      <form action="/clientes/procesarLogin" method="POST">
        <div class="form-group">
          <label class="font-weight-bold">Email</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            </div>
            <input type="email" name="email" class="form-control"
                   placeholder="tu@email.com" required autofocus>
          </div>
        </div>

        <div class="form-group">
          <label class="font-weight-bold">Contraseña</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" name="password" class="form-control"
                   placeholder="Tu contraseña" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mt-3">
          <i class="fas fa-sign-in-alt mr-1"></i> Ingresar
        </button>
      </form>

      <hr>
      <div class="text-center">
        <small class="text-muted">¿No tenés cuenta?</small>
        <a href="/clientes/registro" class="btn btn-sm btn-outline-success ml-2">
          <i class="fas fa-user-plus mr-1"></i> Registrarme
        </a>
      </div>

    </div>
  </div>
</div>

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
