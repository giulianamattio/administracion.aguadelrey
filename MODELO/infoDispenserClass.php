<?php
require_once 'conexionClass.php';
class usuario{
    const TABLA = 'infoDispensers';
    // Declaración de una propiedad
    protected $idInfoDispenser;
    protected $numeroSerie;
    protected $numeroPrecinto;
    protected $tipo;
    protected $precioAlquiler;
    protected $estado;
    protected $idProducto;

    public function __construct($idInfoDispenser, $numeroSerie, $numeroPrecinto, $tipo, $precioAlquiler, 
    $estado, $idProducto){
        $this->idInfoDispenser = $idInfoDispenser;
        $this->numeroSerie = $numeroSerie;
        $this->numeroPrecinto = $numeroPrecinto;
        $this->tipo = $tipo;
        $this->precioAlquiler = $precioAlquiler;
        $this->estado = $estado;
        $this->idProducto = $idProducto;
    }


    // GET y SET
    public function getNombre(){
        return $this->nombre;
    }

    public function setNombre($nombre){
        if(!empty($nombre)){
            $this->nombre = $nombre;
        }
        
    }



    public function guardar(){
        $conexion = new Conexion();
        if($this->id) /*Modifica*/ {
           $consulta = $conexion->prepare('UPDATE ' . self::TABLA .' SET nombre = :nombre, descripcion = :descripcion WHERE id = :id');
           $consulta->bindParam(':nombre', $this->nombre);
           $consulta->bindParam(':descripcion', $this->descripcion);
           $consulta->bindParam(':id', $this->id);
           $consulta->execute();
        }else /*Inserta*/ {
           $consulta = $conexion->prepare('INSERT INTO ' . self::TABLA .' (nombre, descripcion) VALUES(:nombre, :descripcion)');
           $consulta->bindParam(':nombre', $this->nombre);
           $consulta->bindParam(':descripcion', $this->descripcion);
           $consulta->execute();
           $this->id = $conexion->lastInsertId();
        }
        $conexion = null;
     }


     public static function buscarUsuarios(){
       $conexion = new Conexion();
       $consulta = $conexion->prepare('SELECT idCliente, nombreCompleto, dni, email FROM ' . self::TABLA . ' ORDER BY nombreCompleto');
       $consulta->execute();
       $registros = $consulta->fetchAll();
       return $registros;
     }

     public static function buscarUsuarioPorId($id){
        $conexion = new Conexion();
        $consulta = $conexion->prepare('SELECT idUsuario, email, contrasenia, fechaCreacion, nombre, idRol FROM ' . self::TABLA . ' WHERE idUsuario = :id');
        $consulta->bindParam(':id', $id);
        $consulta->execute();
        $registro = $consulta->fetch();
        if($registro){
           return new self($registro['idUsuario'], $registro['email'], $registro['contrasenia'],
                               $registro['fechaCreacion'], $registro['nombre'], $registro['idRol']);
        }else{
           return false;
        }
     }

}
?>