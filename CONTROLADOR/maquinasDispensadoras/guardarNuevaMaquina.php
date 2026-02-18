<?php
// ============================================================
//  CONTROLADOR/maquinasDispensadoras/guardarNuevaMaquina.php
//  Procesa el POST del formulario nuevaMaquina
// ============================================================

require($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/inicializacion.php');

// Validación básica — que vengan los campos obligatorios
if (empty($_POST['serie'])) {
    header('Location: /maquinasDispensadoras/nuevaMaquinaDispensadora?error=serie_requerida');
    exit;
}

$stmt = $conexionbd->prepare("
    INSERT INTO maquina_dispensadora 
        (id_estado, numero_serie, marca, modelo, numero_precinto, observaciones)
    VALUES 
        (:id_estado, :numero_serie, :marca, :modelo, :numero_precinto, :observaciones)
");

$stmt->execute([
    ':id_estado'       => $_POST['tipo'],           // el select de estado
    ':numero_serie'    => trim($_POST['serie']),
    ':marca'           => trim($_POST['descripcion']),
    ':modelo'          => '',                        // campo para ampliar después
    ':numero_precinto' => trim($_POST['precinto']),
    ':observaciones'   => '',
]);

// Redirigir al listado con mensaje de éxito
header('Location: /maquinasDispensadoras/listadoMaquinasDispensadoras?ok=1');
exit;