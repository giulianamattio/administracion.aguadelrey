<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$datos = [];


$stmtProductos = $conexionbd->prepare("SELECT id_producto, nombre FROM producto WHERE fecha_baja IS NULL");
$stmtProductos->execute();
$listaProductos = $stmtProductos->fetchAll();
foreach($listaProductos as $rsProductos){
    $idProducto = $rsProductos["id_producto"];
    $descProducto = $rsProductos["nombre"];
    $datos[] = [ "idProducto" => $idProducto, "descripcion" => $descProducto  ];
}

echo json_encode($datos);

?>