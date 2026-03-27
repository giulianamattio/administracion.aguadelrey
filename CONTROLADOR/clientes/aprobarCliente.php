<?php
// ============================================================
//  CONTROLADOR/clientes/aprobarCliente.php
//  Mueve el cliente de lista_espera a la tabla cliente
// ============================================================
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

if (empty($_POST['idEspera'])) {
    header('Location: /clientes/listaDeEspera?error=id_requerido');
    exit;
}

$idEspera = (int) $_POST['idEspera'];

// Traer datos del pendiente
$stmt = $conexionbd->prepare("
    SELECT * FROM lista_espera WHERE id_espera = :id AND estado = 'pendiente'
");
$stmt->execute([':id' => $idEspera]);
$pendiente = $stmt->fetch();

if (!$pendiente) {
    header('Location: /clientes/listaDeEspera?error=no_encontrado');
    exit;
}

// Insertar en tabla cliente como activo
$stmtInsert = $conexionbd->prepare("
    INSERT INTO cliente
        (nombre, apellido, razon_social, email, telefono,
         domicilio, localidad, provincia, password_hash, estado, created_at, updated_at)
    VALUES
        (:nombre, :apellido, :razon, :email, :tel,
         :dom, :loc, :prov, :pass, 'activo', NOW(), NOW())
");
$stmtInsert->execute([
    ':nombre'   => $pendiente['nombre'],
    ':apellido' => $pendiente['apellido'],
    ':razon'    => $pendiente['razon_social'],
    ':email'    => $pendiente['email'],
    ':tel'      => $pendiente['telefono'],
    ':dom'      => $pendiente['domicilio'],
    ':loc'      => $pendiente['localidad'],
    ':prov'     => $pendiente['provincia'],
    ':pass'     => $pendiente['password_hash'],
]);

// Actualizar estado en lista_espera
$stmtUpdate = $conexionbd->prepare("
    UPDATE lista_espera SET estado = 'aprobado' WHERE id_espera = :id
");
$stmtUpdate->execute([':id' => $idEspera]);

header('Location: /clientes/listaDeEspera?ok=aprobado');
exit;
