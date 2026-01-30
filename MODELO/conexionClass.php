<?php 
 class Conexion extends PDO { 
   private $tipoBase = 'mysql';
   private $host = "localhost";
   private $usuario = "root";
    private $password = "admin";
    private $base = "aguadelrey.local";

   public function __construct() {
      //Sobreescribo el método constructor de la clase PDO.
      try{
         parent::__construct("{$this->tipoBase}:dbname={$this->base};host={$this->host};charset=utf8", $this->usuario, $this->password);
      }catch(PDOException $e){
         echo 'Ha surgido un error y no se puede conectar a la base de datos. Detalle: ' . $e->getMessage();
         exit;
      }
   } 
 } 
?>