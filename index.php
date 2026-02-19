<?php
session_start();
$error= "";
foreach ($_GET as $key => $value ) {
  if (isset($_GET[$key]))
    $$key = $value;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agua del Rey | Log in </title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="/dist/css/adminlte.min.css">

</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <div class="h1">
        <img src="/VISTA/imagenes/logoAgua.jpg" style="width: 25%;">
      </div>
    </div>
    <div class="card-body">

      <form action="/principal" method="post" onSubmit="return validarInicioSesion(this)">
        <div class="input-group mb-3">
          <input type="text" class="form-control" onkeypress="javascript:validarUsuario(this.value)" name="usuario" id="usuario" placeholder="Usuario">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
          <span id="mensajeErrorUsuario" style="display:none;" class="error invalid-feedback">Por favor, ingrese usuario.</span>
        </div>
        <div class="input-group mb-3">
          <input type="password" onkeypress="javascript:validarContrasenia(this.value)" class="form-control" name="password" id="password" placeholder="Contraseña">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
          <span id="mensajeErrorPassword" style="display:none;" class="error invalid-feedback">Por favor, ingrese la contraseña.</span>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Recordarme
              </label>
            </div>
          </div>
          <br clear= "all" /><br />
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesi&oacute;n</button>
          </div>
          <br clear= "all" /><br />
        </div>
      </form>

      <!--<p class="mb-1">
        <a href="forgot-password.html">Recuperar contrase&ntilde;a</a>
      </p>-->

    </div>
  </div>
</div>

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/dist/js/adminlte.min.js"></script>
<script src="/VISTA/script/login.js"></script>
</body>
</html>
