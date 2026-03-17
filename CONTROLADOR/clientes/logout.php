<?php
// ============================================================
//  CONTROLADOR/clientes/logout.php
// ============================================================
session_start();
session_unset();
session_destroy();
header('Location: /clientes/login?logout=1');
exit;
