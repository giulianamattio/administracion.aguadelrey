<?php
// ============================================================
//  CONTROLADOR/login/login.php
//  Solo procesa autenticación cuando hay un POST del formulario.
//  Si no hay POST, no hace nada y principal.php muestra el dashboard.
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['usuario'])) {

    $usuario  = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $stmt = $conexionbd->prepare(
        "SELECT * FROM usuario_empleado WHERE email = :email AND activo = TRUE"
    );
    $stmt->execute([':email' => $usuario]);
    $rsUsuario = $stmt->fetch();

    if ($rsUsuario && password_verify($password, $rsUsuario['password_hash'])) {

        $_SESSION['usuario_global']  = $usuario;
        $_SESSION['password_global'] = $password;
        $_SESSION['id_empleado']     = $rsUsuario['id_empleado'];
        $_SESSION['rol']             = $rsUsuario['id_rol'];
        $_SESSION['nombre']          = $rsUsuario['nombre'] . ' ' . $rsUsuario['apellido'];

        // Login exitoso — redirigir al dashboard
        header('Location: /principal');
        exit;

    } else {
        // Credenciales incorrectas — volver al login con error
        header('Location: /index?error=credenciales');
        exit;
    }
}
?>































