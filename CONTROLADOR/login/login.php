<?php
// ============================================================
//  CONTROLADOR/login/login.php
//  Se ejecuta ANTES que inicializacion.php desde principal.php
//  para que el POST se procese antes de la verificación de sesión
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['usuario'])) {

    // Iniciar buffer y sesión de forma independiente
    ob_start();
    if (session_status() === PHP_SESSION_NONE) {
        session_save_path('/var/lib/php/sessions');
        session_start();
    }

    // Conexión propia a la BD
    require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

    $usuario  = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    error_log("LOGIN INTENTO — usuario: " . $usuario);

    $stmt = $conexionbd->prepare(
        "SELECT * FROM usuario_empleado WHERE email = :email AND activo = TRUE"
    );
    $stmt->execute([':email' => $usuario]);
    $rsUsuario = $stmt->fetch();

    if ($rsUsuario && password_verify($password, $rsUsuario['password_hash'])) {

        error_log("LOGIN OK — id_empleado: " . $rsUsuario['id_empleado']);

        $_SESSION['usuario_global'] = $usuario;
        $_SESSION['id_empleado']    = $rsUsuario['id_empleado'];
        $_SESSION['rol']            = $rsUsuario['id_rol'];
        $_SESSION['nombre']         = $rsUsuario['nombre'] . ' ' . $rsUsuario['apellido'];

        error_log("SESSION ID post-login: " . session_id());

        header('Location: /principal');
        exit;

    } else {
        error_log("LOGIN FALLO — usuario: " . $usuario . " — encontrado: " . ($rsUsuario ? 'si, pass incorrecta' : 'no encontrado'));
        header('Location: /index?error=credenciales');
        exit;
    }
}
?>
































