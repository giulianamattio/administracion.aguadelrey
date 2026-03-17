<?php
$stmt = $conexionbd->prepare("
    SELECT id_espera, dni_cuit, nombre, apellido, razon_social,
           email, telefono, domicilio, localidad, provincia,
           estado, created_at
    FROM lista_espera
    WHERE estado = 'pendiente'
    ORDER BY created_at ASC
");
$stmt->execute();
$clientesPendientes = $stmt->fetchAll();