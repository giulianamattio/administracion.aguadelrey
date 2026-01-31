<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$fecha = $_POST["fecha"];
$cliente = $_POST["cliente"];
$total = $_POST["total"];
$instagram = $_POST["instagram"];
$facebook = $_POST["facebook"];
$maestro = $_POST["maestro"];
$modalidad = $_POST["modalidad"];
$nombreObra = $_POST["nombreObra"];
$categoria = $_POST["categoria"];
$participacion = $_POST["participacion"];
$cantidadProductoActual= $_POST["cantidadProductoActual"];
$error = 0;



if($participacion == 1){
    $cantidadParticipantes = 1;
}else if($participacion == 2){
    $cantidadParticipantes = 2;
}else if($participacion == 3){
    $cantidadParticipantes = 3;
}else{
    $cantidadParticipantes = $_POST["cantidadParticipantes"];
}


$consultaUltimoId = $safesql->query("SELECT MAX(idCoreografia) as idCoreografia FROM coreografias ORDER BY idCoreografia DESC");	
if (mysqli_num_rows($consultaUltimoId) != 0) {	
	$rsUltimoId = mysqli_fetch_array($consultaUltimoId, MYSQLI_ASSOC);
    $ultimoId = $rsUltimoId["idCoreografia"];
    $idCoreografia = $ultimoId + 1;
}else{
    $idCoreografia = 1;
}

//AGREGAR ACADEMIA
$consultaAcademia = $safesql->query("SELECT idAcademia FROM academias WHERE nombre = ?s", $academia);	
if (mysqli_num_rows($consultaAcademia) != 0) {	
	$rsAcademia= mysqli_fetch_array($consultaAcademia, MYSQLI_ASSOC);
    $idAcademia = $rsAcademia["idAcademia"];
}else{
    //Agregar academia
    $consultaUltimoIdAcademia = $safesql->query("SELECT MAX(idAcademia) AS idAcademia FROM academias ORDER BY idAcademia DESC");	
    if (mysqli_num_rows($consultaUltimoIdAcademia) != 0) {	
        $rsUltimoIdAcademia = mysqli_fetch_array($consultaUltimoIdAcademia, MYSQLI_ASSOC);
        $ultimoIdAcademia = $rsUltimoIdAcademia["idAcademia"];
        $idAcademia = $ultimoIdAcademia + 1;
    }
    $nuevaAcademia= $safesql->query("INSERT INTO academias(idAcademia, nombre, facebook, instagram, telefono) 
    VALUES(?i, ?s, ?s, ?s, ?s)", $idAcademia, $academia, $facebook, $instagram, $telefono);
    $resultadoNuevaAcademia = mysqli_query($conexionbd, $nuevaAcademia, MYSQLI_STORE_RESULT); 
    if (!$resultadoNuevaAcademia) {
        $error++;
    }
}


//AGREGAR COREOGRAFIA
/*echo "INSERT INTO coreografias(idCoreografia, idAcademia, maestroPreparador, 
nombreObra, modalidad, idCategoria, idFormaParticipacion, idEvento, cantidadParticipantes) 
VALUES(".$idCoreografia.", ".$idAcademia.", ".$maestro.", ".$nombreObra.",".$modalidad.",".$categoria.", ".$participacion.", ".$evento.", ".$cantidadParticipantes.")";
*/
$nuevaCoreo = $safesql->query("INSERT INTO coreografias(idCoreografia, idAcademia, maestroPreparador, 
nombreObra, modalidad, idCategoria, idFormaParticipacion, idEvento, cantidadParticipantes) 
VALUES(?i, ?i, ?s, ?s, ?s, ?i, ?i, ?i, ?i)", 
$idCoreografia, $idAcademia, $maestro, $nombreObra, $modalidad, $categoria, $participacion, $evento, $cantidadParticipantes);
$resultadoNuevaCoreo = mysqli_query($conexionbd, $nuevaCoreo, MYSQLI_STORE_RESULT); 
if (!$resultadoNuevaCoreo) {
    $error++;
}

if($cantidadProductoActual >= 1){
    //AGREGAR PARTICIPANTES
    
    for ($i = 1; $i <= $cantidadProductoActual; $i++) {
        $nombre = $_POST['nombre'.$i];
        $apellido = $_POST['apellido'.$i];
        $dni = $_POST['dni'.$i];
        $fechaNacimiento = $_POST['fechaNacimiento'.$i];

        $consultaParticipante = $safesql->query("SELECT idParticipante FROM participantes WHERE dni = ?i", $dni);	
        if (mysqli_num_rows($consultaParticipante) != 0) {	
            $rsParticipante= mysqli_fetch_array($consultaParticipante, MYSQLI_ASSOC);
            $idParticipante = $rsParticipante["idParticipante"];
        }else{
            //Agregar participante
            $consultaUltimoIdParticipante = $safesql->query("SELECT MAX(idParticipante) AS idParticipante FROM participantes ORDER BY idParticipante DESC");	
            if (mysqli_num_rows($consultaUltimoIdParticipante) != 0) {	
                $rsUltimoIdParticipante = mysqli_fetch_array($consultaUltimoIdParticipante, MYSQLI_ASSOC);
                $ultimoIdParticipante  = $rsUltimoIdParticipante["idParticipante"];
                $idParticipante = $ultimoIdParticipante + 1;
            }
            $nuevoParticipante= $safesql->query("INSERT INTO participantes(idParticipante, nombre, apellido, dni, fechaNacimiento) 
            VALUES(?i, ?s, ?s, ?i, ?s)", $idParticipante, $nombre, $apellido, $dni, $fechaNacimiento);
            $resultadoNuevoParticipante= mysqli_query($conexionbd, $nuevoParticipante, MYSQLI_STORE_RESULT); 
            if (!$resultadoNuevoParticipante) {
                $error++;
            }
        }

        //Agregar relacion partipante y academia
        $consultaRelacionParticipanteAcademia = $safesql->query("SELECT idParticipanteAcademia
        FROM participantesacademias WHERE idParticipante = ?i AND idAcademia = ?i", $idParticipante, $idAcademia);	
        if (mysqli_num_rows($consultaRelacionParticipanteAcademia) != 0) {	
            $rsParticipanteAcademia= mysqli_fetch_array($consultaRelacionParticipanteAcademia, MYSQLI_ASSOC);
            $idParticipanteAcademia = $rsParticipanteAcademia["idParticipanteAcademia"];
        }else{
            //Agregar agregar relacion participante/academia
            $consultaUltimoIdParticipanteAcademia = $safesql->query("SELECT MAX(idParticipanteAcademia) 
            AS idParticipanteAcademia FROM participantesacademias ORDER BY idParticipanteAcademia DESC");	
            if (mysqli_num_rows($consultaUltimoIdParticipanteAcademia) != 0) {	
                $rsUltimoIdParticipanteAcademia = mysqli_fetch_array($consultaUltimoIdParticipanteAcademia, MYSQLI_ASSOC);
                $ultimoIdParticipanteAcademia  = $rsUltimoIdParticipanteAcademia["idParticipanteAcademia"];
                $idParticipanteAcademia = $ultimoIdParticipanteAcademia + 1;
            }
            $nuevaRelacionParticipanteAcademia= $safesql->query("INSERT INTO participantesacademias(idParticipanteAcademia, idParticipante, idAcademia) 
            VALUES(?i, ?i, ?i)", $idParticipanteAcademia, $idParticipante, $idAcademia);
            $resultadoNuevaRelacionParticipanteAcademia= mysqli_query($conexionbd, $nuevaRelacionParticipanteAcademia, MYSQLI_STORE_RESULT); 
            if (!$resultadoNuevaRelacionParticipanteAcademia) {
                $error++;
            }
        }



        //Agregar relacion particpante y coreografia
        $consultaUltimoIdCoreografiaParticipante = $safesql->query("SELECT MAX(idCoreografiaParticipante) AS idCoreografiaParticipante FROM coreografiasparticipantes ORDER BY idCoreografiaParticipante DESC");	
        if (mysqli_num_rows($consultaUltimoIdCoreografiaParticipante) != 0) {	
            $rsUltimoIdCoreografiaParticipante = mysqli_fetch_array($consultaUltimoIdCoreografiaParticipante, MYSQLI_ASSOC);
            $ultimoIdCoreografiaParticipante  = $rsUltimoIdCoreografiaParticipante["idCoreografiaParticipante"];
            $idCoreografiaParticipante = $ultimoIdCoreografiaParticipante + 1;
        }
        $nuevaCoreografiaParticipante= $safesql->query("INSERT INTO coreografiasparticipantes(idCoreografiaParticipante, idParticipante, idCoreografia) 
            VALUES(?i, ?i, ?i)", $idCoreografiaParticipante, $idParticipante, $idCoreografia);
        $resultadoNuevaCoreografiaParticipante= mysqli_query($conexionbd, $nuevaCoreografiaParticipante, MYSQLI_STORE_RESULT);
        if (!$resultadoNuevaCoreografiaParticipante) {
            $error++;
        }
    }
}

?>
<html>
    <head>
        <script language="JavaScript">
        window.top.location.href="/listaDeCoreografias"; 
        </script>
    </head>
</html>