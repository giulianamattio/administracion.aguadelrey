<?php
/*
Base de datos: aguadelr_bd
Usuario: aguadelr_admin
Pass: aguaDelRey***

$password = "aguaDelRey***"
*/  
$host = "localhost";
$usuario = "root";
$password = "admin";
$base = "aguadelrey.local";

$conexionbd = mysqli_connect($host, $usuario, $password) or die('Error de conexion a la base de datos : ' . mysqli_error($conexionbd));

mysqli_select_db($conexionbd, $base) or die('Error al seleccionar la base de datos.');
	
?>