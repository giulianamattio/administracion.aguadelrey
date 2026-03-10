<?php
// ============================================================
//  CONTROLADOR/clientes/guardarPerfil.php
// ============================================================
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/sessionCliente.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$nombre   = trim($_POST['nombre']      ?? '');
$apellido = trim($_POST['apellido']    ?? '');
$email    = trim($_POST['email']       ?? '');
$razon    = trim($_POST['razon_social']?? '');
$tel      = trim($_POST['telefono']    ?? '');
$dom      = trim($_POST['domicilio']   ?? '');
$loc      = trim($_POST['localidad']   ?? '');
$prov     = trim($_POST['provincia']   ?? '');

if (empty($nombre) || empty($apellido) || empty($email)) {
    header('Location: /clientes/perfil?error=Nombre,+apellido+y+email+son+obligatorios.');
    exit;
}

// Verificar que el email no lo use otro cliente
$stmtCheck = $conexionbd->prepare("
    SELECT id_cliente FROM cliente WHERE email = :email AND id_cliente != :id
");
$stmtCheck->execute([':email' => $email, ':id' => $_SESSION['cliente_id']]);
if ($stmtCheck->fetch()) {
    header('Location: /clientes/perfil?error=El+email+ya+está+en+uso+por+otra+cuenta.');
    exit;
}

$stmt = $conexionbd->prepare("
    UPDATE cliente SET
        nombre       = :nombre,
        apellido     = :apellido,
        razon_social = :razon,
        email        = :email,
        telefono     = :tel,
        domicilio    = :dom,
        localidad    = :loc,
        provincia    = :prov,
        updated_at   = NOW()
    WHERE id_cliente = :id
");
$stmt->execute([
    ':nombre'   => $nombre,
    ':apellido' => $apellido,
    ':razon'    => $razon ?: null,
    ':email'    => $email,
    ':tel'      => $tel ?: null,
    ':dom'      => $dom ?: null,
    ':loc'      => $loc ?: null,
    ':prov'     => $prov ?: null,
    ':id'       => $_SESSION['cliente_id'],
]);

// Actualizar sesión con el nuevo nombre
$_SESSION['cliente_nombre']  = $nombre;
$_SESSION['cliente_apellido']= $apellido;
$_SESSION['cliente_email']   = $email;

header('Location: /clientes/perfil?ok=1');
exit;
