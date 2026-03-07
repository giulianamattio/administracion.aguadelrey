<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$datos = [];

$consultaProductos = $safesql->query("SELECT idProducto, descripcion FROM productos WHERE fechaBaja IS NULL");
while($rsProductos= mysqli_fetch_array($consultaProductos, MYSQLI_ASSOC)){
    $idProducto = $rsProductos["idProducto"];
    $descProducto = $rsProductos["descripcion"];

    $datos[] = [ "idProducto" => $idProducto, "descripcion" => $descProducto  ];
}

echo json_encode($datos);

?>