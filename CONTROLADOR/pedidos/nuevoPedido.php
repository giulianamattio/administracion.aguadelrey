<?php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

$fecha = $_POST["fecha"];
$cliente = $_POST["cliente"];
$total = $_POST["total"];
$cantidadProductoActual= $_POST["cantidadProductoActual"];
$error = 0;



$consultaUltimoId = $safesql->query("SELECT MAX(idPedido) as idPedido FROM pedidos ORDER BY idPedido DESC");	
if (mysqli_num_rows($consultaUltimoId) != 0) {	
	$rsUltimoId = mysqli_fetch_array($consultaUltimoId, MYSQLI_ASSOC);
    $ultimoId = $rsUltimoId["idPedido"];
    $idPedido = $ultimoId + 1;
}else{
    $idPedido = 1;
}




if($cantidadProductoActual >= 1){
    //AGREGAR PARTICIPANTES
    
    for ($i = 1; $i <= $cantidadProductoActual; $i++) {
        $producto = $_POST['producto'.$i];
        $cantidad = $_POST['cantidad'.$i];

      
            //Agregar producto-Pedido
            $bggbbgbbjmhnjhnm= $safesql->query("SELECT MAX(idParticipante) AS idParticipante FROM participantes ORDER BY idParticipante DESC");	
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
        window.top.location.href="/nuevoPedido"; 
        </script>
    </head>
</html>