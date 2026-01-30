<?php
session_start();
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT']."/configuraciones/conexionBD.php");

require($_SERVER['DOCUMENT_ROOT'].'/configuraciones/safemysql.class.php');

$conf = array(
    'user'    => 'root',
    'pass'    => 'admin',
    'db'      => 'aguadelrey.local',
    'charset' => 'latin1');
$safesql = new SafeMySQL($conf); // with some of the default settings overwritten
?>