<?php
// ============================================================
//  VISTA/clientes/portal/resetPassword.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$token = $_GET['token'] ?? '';
$tokenValido = false;
$idCliente   = null;

if ($token) {
    $stmt = $conexionbd->prepare("
        SELECT id_cliente FROM password_reset_tokens
        WHERE token     = :token
          AND usado     = FALSE
          AND expira_en > NOW()
    ");
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch();
    if ($row) {
        $tokenValido = true;
        $idCliente   = $row['id_cliente'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Nueva contraseña</title>
  <style>
    body { background-color: #f0f2f5; }
    .login-card {
      max-width: 420px; margin: 80px auto;
      border-radius: 12px; overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    }
    .login-header {
      background: linear-gradient(135deg, #007bff, #0056b3);
      padding: 30px; text-align: center; color: #fff;
    }
    .login-header img { height: 70px; border-radius: 8px; margin-bottom: 12px; }
    .login-header h4  { margin: 0; font-weight: 700; }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-header">
    <img src="/VISTA/imagenes/logoAgua.jpg" alt="Agua del Rey">
    <h4>Nueva contraseña</h4>
  </div>

  <div class="card border-0">
    <div class="card-body p-4">

      <?php if (!$tokenValido): ?>
        <div class="alert alert-danger">
          <i class="fas fa-times-circle mr-1"></i>
          El link es inválido o ya expiró. Solicitá uno nuevo.
        </div>
        <a href="/clientes/login" class="btn btn-primary btn-block">
          Volver al login
        </a>

      <?php elseif (isset($_GET['ok'])): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle mr-1"></i>
          Tu contraseña fue actualizada correctamente.
        </div>
        <a href="/clientes/login" class="btn btn-primary btn-block">
          Iniciar sesión
        </a>

      <?php else: ?>
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <?= htmlspecialchars($_GET['error']) ?>
          </div>
        <?php endif; ?>

        <form action="/clientes/guardarNuevaPassword" method="POST">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <div class="form-group">
            <label class="font-weight-bold">Nueva contraseña</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
              </div>
              <input type="password" name="password" id="password"
                     class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>
          </div>

          <div class="form-group">
            <label class="font-weight-bold">Repetir contraseña</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
              </div>
              <input type="password" name="password_confirm" id="password_confirm"
                     class="form-control" placeholder="Repetí la contraseña" required minlength="8">
            </div>
            <div class="text-danger small mt-1" id="error-pass"></div>
          </div>

          <button type="submit" class="btn btn-primary btn-block mt-3">
            <i class="fas fa-save mr-1"></i> Guardar contraseña
          </button>
        </form>
      <?php endif; ?>

    </div>
  </div>
</div>

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelector('form')?.addEventListener('submit', function(e) {
    var pass    = document.getElementById('password').value;
    var confirm = document.getElementById('password_confirm').value;
    if (pass !== confirm) {
        e.preventDefault();
        document.getElementById('error-pass').textContent = 'Las contraseñas no coinciden.';
    }
});
</script>
</body>
</html>