<?php
// 1. Primero procesar el login si hay POST — antes de cualquier verificación de sesión
require_once($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/login/login.php');

// 2. Recién ahora inicializar (verifica sesión, redirige si no hay)
require_once($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agua del Rey | Dashboard</title>

  <link rel="Agua del rey" href="/favicon.ico">
  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/css/cssGeneral.php');
  require($_SERVER["DOCUMENT_ROOT"].'/CONTROLADOR/login/login.php');
  ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="/VISTA/imagenes/logoAgua.jpg" alt="Agua del Rey" height="60" width="60">
  </div>

  

  <?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/encabezado.php');
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/menu.php');
  ?>

  <?php
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/modulosHome.php');
  ?>

  
  <?php 
    require($_SERVER["DOCUMENT_ROOT"].'/VISTA/footer.php');
  ?>
    

</div>
<!-- ./wrapper -->

<?php 
  require($_SERVER["DOCUMENT_ROOT"].'/VISTA/script/scriptGeneral.php');
?>

</body>
</html>