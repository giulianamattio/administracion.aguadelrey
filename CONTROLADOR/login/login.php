<?php 
//Si no obtengo las variables por formulario
//reviso las variables session
$usuario = "";
$password = "";

foreach ($_POST as $key => $value ) {
    if (isset($_POST[$key]))
        $$key = $value;
 }


//si usuario esta vacio, se crean las variables
if ($usuario == ""){
	//si esta definido el usuario global se establecen las variables de session
	if (isset($_SESSION['usuario_global'])){
		$usuario = $_SESSION['usuario_global'];
		$password = $_SESSION['password_global'];
	}
}

//CREO LA CONSULTA con las dos variables que no tienen las comillas
$consultaUsuario = $safesql->query("SELECT * FROM usuarios WHERE nombre = ?s AND contrasenia = sha1(?s)", 
$usuario, $password);
//$resultadoUsuario = mysqli_query($consultaUsuario, MYSQLI_STORE_RESULT); 

//Si hay resultados (es diferente de cero), quiere decir que se encontro el usuario y contrasena ingresados
if (mysqli_num_rows($consultaUsuario) != 0) {

	$rsUsuario  = mysqli_fetch_array($consultaUsuario, MYSQLI_ASSOC);

	
//si no esta definida la variable de session, se definen usando las variables de la consulta
	if (!isset($_SESSION['usuario_global']) or ($_SESSION['usuario_global'] != $usuario or $_SESSION['password_global'] != $password)){

		$_SESSION['usuario_global'] = $usuario;
		$_SESSION['password_global'] = $password;
	}
}

else { 
?>
<html>
<head>
<script language="JavaScript">
window.top.location.href="/index/error" 
</script>
</head>
</html>
<?php
	die();
}
?>