<?php
$consultaMaquinas = $safesql->query("SELECT infoDisp.numeroSerie, infoDisp.numeroPrecinto, infoDisp.precioAlquiler,
tipo.descripcion as tipoDisp, estado. descripcion AS estadoDisp
 FROM infoDispensers infoDisp
 INNER JOIN tipoDispensers tipo ON tipo.idTipoDispenser = infoDisp.tipo
 INNER JOIN estadoDispensers estado ON estado.idEstadoDispenser = infoDisp.estado", );



?>