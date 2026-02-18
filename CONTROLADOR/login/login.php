<?php
// ============================================================
//  login.php — autenticación con PDO + PostgreSQL
// ============================================================

$usuario  = "";
$password = "";

// Recibir variables del formulario POST
foreach ($_POST as $key => $value) {
    if (isset($_POST[$key]))
        $$key = $value;
}

// Si no vienen por POST, intentar recuperar de sesión activa
if ($usuario == "") {
    if (isset($_SESSION['usuario_global'])) {
        $usuario  = $_SESSION['usuario_global'];
        $password = $_SESSION['password_global'];
    }
}

// Consulta con prepared statement PDO — busca por email
// Usamos email como campo de login porque es único en la tabla
$stmt = $conexionbd->prepare(
    "SELECT * FROM usuario_empleado WHERE email = :email AND activo = TRUE"
);
$stmt->execute([':email' => $usuario]);
$rsUsuario = $stmt->fetch();

// Verificar que el usuario existe Y que la contraseña es correcta
// password_verify compara el input contra el hash almacenado en BD
if ($rsUsuario && password_verify($password, $rsUsuario['password_hash'])) {

    // Establecer variables de sesión si no estaban definidas
    if (!isset($_SESSION['usuario_global']) ||
        $_SESSION['usuario_global'] != $usuario) {

        $_SESSION['usuario_global']  = $usuario;
        $_SESSION['password_global'] = $password;
        $_SESSION['id_empleado']     = $rsUsuario['id_empleado'];
        $_SESSION['rol']             = $rsUsuario['id_rol'];
        $_SESSION['nombre']          = $rsUsuario['nombre'] . ' ' . $rsUsuario['apellido'];
    }

} else {
    // Login fallido — redirigir a página de error
    ?>
    <html>
    <head>
    <script language="JavaScript">
        window.top.location.href = "/index/error"
    </script>
    </head>
    </html>
    <?php
    die();
}
?>