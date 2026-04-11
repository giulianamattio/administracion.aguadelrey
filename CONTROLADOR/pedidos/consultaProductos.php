<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$datos = [];

$stmtProductos = $conexionbd->prepare("SELECT id_producto, nombre, precio_unitario FROM producto WHERE fecha_baja IS NULL");
$stmtProductos->execute();
$listaProductos = $stmtProductos->fetchAll();

foreach($listaProductos as $rsProductos){
    $datos[] = [
        "idProducto"     => $rsProductos["id_producto"],
        "descripcion"    => $rsProductos["nombre"],
        "precioUnitario" => $rsProductos["precio_unitario"]  // ← corregido
    ];
}

echo json_encode($datos);
?>