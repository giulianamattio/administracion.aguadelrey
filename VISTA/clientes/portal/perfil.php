<?php
// ============================================================
//  VISTA/clientes/portal/perfil.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// Traer datos actuales del cliente
$stmt = $conexionbd->prepare("
    SELECT nombre, apellido, razon_social, email, telefono,
           domicilio, localidad, provincia
    FROM cliente WHERE id_cliente = :id
");
$stmt->execute([':id' => $_SESSION['cliente_id']]);
$cliente = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/head.php'); ?>
  <title>Agua del Rey | Modificar Perfil</title>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/VISTA/clientes/portal/layout/navbar.php'); ?>

<div class="portal-content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">

        <div class="card shadow" style="border-radius:12px; border:none;">
          <div class="card-header bg-warning text-white" style="border-radius:12px 12px 0 0;">
            <h5 class="mb-0"><i class="fas fa-user-edit mr-2"></i>Modificar Perfil</h5>
          </div>
          <div class="card-body p-4">

            <?php if (isset($_GET['ok'])): ?>
              <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i> Datos actualizados correctamente.
              </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <?= htmlspecialchars($_GET['error']) ?>
              </div>
            <?php endif; ?>

            <form action="/clientes/guardarPerfil" method="POST">

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Apellido <span class="text-danger">*</span></label>
                    <input type="text" name="apellido" class="form-control"
                           value="<?= htmlspecialchars($cliente['apellido']) ?>" required>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="font-weight-bold">Razón Social <small class="text-muted">(solo empresas)</small></label>
                <input type="text" name="razon_social" class="form-control"
                       value="<?= htmlspecialchars($cliente['razon_social'] ?? '') ?>">
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($cliente['email']) ?>" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="font-weight-bold">Domicilio</label>
                <input type="text" name="domicilio" class="form-control"
                       value="<?= htmlspecialchars($cliente['domicilio'] ?? '') ?>">
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Localidad</label>
                    <input type="text" name="localidad" class="form-control"
                           value="<?= htmlspecialchars($cliente['localidad'] ?? '') ?>">
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Provincia</label>
                    <input type="text" name="provincia" class="form-control"
                           value="<?= htmlspecialchars($cliente['provincia'] ?? '') ?>">
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-sm-6">
                  <a href="/clientes/home" class="btn btn-default btn-block">Cancelar</a>
                </div>
                <div class="col-sm-6">
                  <button type="submit" class="btn btn-warning btn-block text-white">
                    <i class="fas fa-save mr-1"></i> Guardar cambios
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
</body>
</html>
