<?php
require_once 'conexionClass.php';
class usuario{
    const TABLA = 'usuarios';
    // Declaración de una propiedad
    protected $idUsuario;
    protected $email;
    protected $contrasenia;
    protected $fechaCreacion;
    protected $nombre;
    protected $idRol;

    public function __construct($idUsuario, $email, $contrasenia, $fechaCreacion, $nombre, $idRol){
        $this->idUsuario = $idUsuario;
        $this->email = $email;
        $this->contrasenia = $contrasenia;
        $this->fechaCreacion = $fechaCreacion;
        $this->nombre = $nombre;
        $this->idRol = $idRol;
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