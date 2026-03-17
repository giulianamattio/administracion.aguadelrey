<?php
// ============================================================
//  VISTA/clientes/registroCliente.php
//  Formulario público — no requiere login de admin
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agua del Rey | Registrarme</title>
  <link rel="stylesheet" href="/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <style>
    body { background: #f4f6f9; }
    .register-card { max-width: 700px; margin: 40px auto; }
    .brand-header { background: #007bff; color: #fff; padding: 20px; text-align: center; border-radius: 4px 4px 0 0; }
    .brand-header img { height: 60px; margin-bottom: 8px; }
  </style>
</head>
<body>

<div class="register-card">

  <div class="brand-header">
    <img src="/VISTA/imagenes/logoAgua.jpg" alt="Agua del Rey">
    <h4 class="mb-0">Registrarme como cliente</h4>
  </div>

  <div class="card" style="border-radius: 0 0 4px 4px;">
    <div class="card-body p-4">

      <?php
      // Mensajes de resultado
      if (isset($_GET['ok'])):
          if ($_GET['ok'] === 'activo'): ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle"></i>
              <strong>¡Registro exitoso!</strong> Tu cuenta fue creada correctamente. Ya podés iniciar sesión.
            </div>
          <?php elseif ($_GET['ok'] === 'espera'): ?>
            <div class="alert alert-warning">
              <i class="fas fa-clock"></i>
              <strong>Solicitud recibida.</strong> Tu DNI/CUIT no figura en nuestro sistema de facturación.
              Un administrador revisará tu solicitud y te notificará por email cuando sea aprobada.
            </div>
          <?php endif;
      endif;

      if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i>
          <?= htmlspecialchars(str_replace('|', '<br>', $_GET['error'])) ?>
        </div>
      <?php endif; ?>

      <form action="/clientes/procesarRegistro" method="POST">

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>DNI / CUIT <span class="text-danger">*</span></label>
              <input type="text" name="dni_cuit" class="form-control"
                     placeholder="Ej: 27456789 o 30-12345678-9" required>
              <small class="text-muted">Usamos este dato para verificar si ya sos cliente.</small>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Razón Social <small class="text-muted">(solo empresas)</small></label>
              <input type="text" name="razon_social" class="form-control"
                     placeholder="Nombre de la empresa">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>Nombre <span class="text-danger">*</span></label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Apellido <span class="text-danger">*</span></label>
              <input type="text" name="apellido" class="form-control" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" required>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Teléfono</label>
              <input type="text" name="telefono" class="form-control"
                     placeholder="Ej: 3564-456789">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Domicilio</label>
          <input type="text" name="domicilio" class="form-control"
                 placeholder="Calle y número">
        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>Localidad</label>
              <input type="text" name="localidad" class="form-control"
                     value="San Francisco">
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Provincia</label>
              <input type="text" name="provincia" class="form-control"
                     value="Córdoba">
            </div>
          </div>
        </div>

        <hr>
        <h6 class="text-muted mb-3">Creá tu contraseña</h6>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>Contraseña <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control"
                     placeholder="Mínimo 8 caracteres" required>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Repetir contraseña <span class="text-danger">*</span></label>
              <input type="password" name="password2" class="form-control" required>
            </div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-sm-6">
            <a href="/" class="btn btn-default btn-block">Cancelar</a>
          </div>
          <div class="col-sm-6">
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-user-plus"></i> Registrarme
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
