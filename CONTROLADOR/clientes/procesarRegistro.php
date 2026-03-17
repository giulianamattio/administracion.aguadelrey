<?php
// ============================================================
//  CONTROLADOR/clientes/procesarRegistro.php
//  Procesa el formulario de registro público de clientes.
//  Verifica si el DNI/CUIT está en sistema_facturacion:
//    SÍ  → crea cliente activo en tabla cliente
//    NO  → lo manda a lista_espera como pendiente
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

// Validaciones básicas
$errores = [];
$dni     = trim($_POST['dni_cuit']    ?? '');
$nombre  = trim($_POST['nombre']      ?? '');
$apellido= trim($_POST['apellido']    ?? '');
$email   = trim($_POST['email']       ?? '');
$tel     = trim($_POST['telefono']    ?? '');
$dom     = trim($_POST['domicilio']   ?? '');
$loc     = trim($_POST['localidad']   ?? '');
$prov    = trim($_POST['provincia']   ?? '');
$pass    = trim($_POST['password']    ?? '');
$pass2   = trim($_POST['password2']   ?? '');
$razon   = trim($_POST['razon_social']?? '');

if (empty($dni))      $errores[] = 'El DNI/CUIT es obligatorio.';
if (empty($nombre))   $errores[] = 'El nombre es obligatorio.';
if (empty($apellido)) $errores[] = 'El apellido es obligatorio.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                      $errores[] = 'El email no es válido.';
if (strlen($pass) < 8)$errores[] = 'La contraseña debe tener al menos 8 caracteres.';
if ($pass !== $pass2) $errores[] = 'Las contraseñas no coinciden.';

if (!empty($errores)) {
    $query = http_build_query(['error' => implode('|', $errores)]);
    header("Location: /clientes/registro?$query");
    exit;
}

// Verificar si ya existe un cliente con ese email o DNI
$stmtDup = $conexionbd->prepare("
    SELECT id_cliente FROM cliente WHERE email = :email
");
$stmtDup->execute([':email' => $email]);
if ($stmtDup->fetch()) {
    header("Location: /clientes/registro?error=El+email+ya+está+registrado.");
    exit;
}

// Verificar también en lista_espera
$stmtDupEsp = $conexionbd->prepare("
    SELECT id_espera FROM lista_espera WHERE email = :email AND estado = 'pendiente'
");
$stmtDupEsp->execute([':email' => $email]);
if ($stmtDupEsp->fetch()) {
    header("Location: /clientes/registro?error=Ya+tenés+una+solicitud+pendiente+de+aprobación.");
    exit;
}

$passwordHash = password_hash($pass, PASSWORD_BCRYPT);

// ¿Está en el sistema de facturación?
$stmtFac = $conexionbd->prepare("
    SELECT * FROM sistema_facturacion WHERE dni_cuit = :dni
");
$stmtFac->execute([':dni' => $dni]);
$enFacturacion = $stmtFac->fetch();

if ($enFacturacion) {
    // ── CAMINO NORMAL: cliente existente en facturación ──────────────────
    // Se registra directamente como cliente activo
    $stmt = $conexionbd->prepare("
        INSERT INTO cliente
            (id_facturacion, nombre, apellido, razon_social, email, telefono,
             domicilio, localidad, provincia, password_hash, estado, created_at, updated_at)
        VALUES
            (:id_fac, :nombre, :apellido, :razon, :email, :tel,
             :dom, :loc, :prov, :pass, 'activo', NOW(), NOW())
    ");
    $stmt->execute([
        ':id_fac'   => $enFacturacion['id_facturacion'],
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':razon'    => $razon ?: null,
        ':email'    => $email,
        ':tel'      => $tel ?: null,
        ':dom'      => $dom ?: null,
        ':loc'      => $loc ?: null,
        ':prov'     => $prov ?: null,
        ':pass'     => $passwordHash,
    ]);
    header("Location: /clientes/registro?ok=activo");
} else {
    // ── CAMINO ALTERNATIVO: cliente nuevo, va a lista de espera ──────────
    $stmt = $conexionbd->prepare("
        INSERT INTO lista_espera
            (dni_cuit, nombre, apellido, razon_social, email, telefono,
             domicilio, localidad, provincia, password_hash, estado, created_at)
        VALUES
            (:dni, :nombre, :apellido, :razon, :email, :tel,
             :dom, :loc, :prov, :pass, 'pendiente', NOW())
    ");
    $stmt->execute([
        ':dni'      => $dni,
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':razon'    => $razon ?: null,
        ':email'    => $email,
        ':tel'      => $tel ?: null,
        ':dom'      => $dom ?: null,
        ':loc'      => $loc ?: null,
        ':prov'     => $prov ?: null,
        ':pass'     => $passwordHash,
    ]);
    header("Location: /clientes/registro?ok=espera");
}
exit;
