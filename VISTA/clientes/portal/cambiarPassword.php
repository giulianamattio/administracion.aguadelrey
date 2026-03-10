<?php
// ============================================================
//  VISTA/clientes/portal/cambiarPassword.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Cambiar Contraseña</title>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/navbar.php'); ?>

<div class="portal-content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">

        <div class="card shadow" style="border-radius:12px; border:none;">
          <div class="card-header bg-primary text-white" style="border-radius:12px 12px 0 0;">
            <h5 class="mb-0"><i class="fas fa-key mr-2"></i>Modificar Contraseña</h5>
          </div>
          <div class="card-body p-4">

            <?php if (isset($_GET['ok'])): ?>
              <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i> Contraseña actualizada correctamente.
              </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <?= htmlspecialchars($_GET['error']) ?>
              </div>
            <?php endif; ?>

            <form action="/clientes/guardarPassword" method="POST">

              <div class="form-group">
                <label class="font-weight-bold">Contraseña actual <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  </div>
                  <input type="password" name="password_actual" class="form-control"
                         placeholder="Tu contraseña actual" required>
                </div>
              </div>

              <div class="form-group">
                <label class="font-weight-bold">Nueva contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  </div>
                  <input type="password" name="password_nueva" id="passNueva" class="form-control"
                         placeholder="Mínimo 8 caracteres" required>
                </div>
                <small class="text-muted">Mínimo 8 caracteres.</small>
              </div>

              <div class="form-group">
                <label class="font-weight-bold">Repetir nueva contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  </div>
                  <input type="password" name="password_nueva2" id="passNueva2" class="form-control"
                         placeholder="Repetí la nueva contraseña" required>
                </div>
                <small id="matchMsg" class="text-danger d-none">Las contraseñas no coinciden.</small>
              </div>

              <div class="row mt-3">
                <div class="col-sm-6">
                  <a href="/clientes/home" class="btn btn-default btn-block">Cancelar</a>
                </div>
                <div class="col-sm-6">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save mr-1"></i> Guardar
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/footer.php'); ?>
<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
// Validación visual en tiempo real
document.getElementById('passNueva2').addEventListener('input', function() {
    const p1 = document.getElementById('passNueva').value;
    const msg = document.getElementById('matchMsg');
    msg.classList.toggle('d-none', p1 === this.value);
});
</script>
</body>
</html>
