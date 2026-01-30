<?php
require_once 'conexionClass.php';
class cliente{
    const TABLA = 'clientes';
    // Declaración de una propiedad
    protected $idCliente;
    protected $nombreCompleto;
    protected $dni;
    protected $email;
    protected $cuit;
    protected $razonSocial;
    protected $condicionIva;
    protected $telefono;
    protected $celular;
    protected $estado;
    protected $esEmpresa;
    protected $idUsuario;
    protected $idDomicilio;
    protected $idDomicilioEntrega;

    public function __construct($idCliente, $nombreCompleto, $dni, $email, $cuit, $razonSocial, $condicionIva,
                                 $telefono, $celular, $estado, $esEmpresa, $idUsuario, $idDomicilio, $idDomicilioEntrega){
        $this->idCliente = $idCliente;
        $this->nombreCompleto = $nombreCompleto;
        $this->dni = $dni;
        $this->email = $email;
        $this->cuit = $cuit;
        $this->razonSocial = $razonSocial;
        $this->condicionIva = $condicionIva;
        $this->telefono = $telefono;
        $this->celular = $celular;
        $this->estado = $estado;
        $this->esEmpresa = $esEmpresa;
        $this->idUsuario = $idUsuario;
        $this->idDomicilio = $idDomicilio;
        $this->idDomicilioEntrega = $idDomicilioEntrega;
    }


    // GET y SET
    public function getNombreCompleto(){
        return $this->nombreCompleto;
    }

    public function setNombreCompleto($nombreCompleto){
        if(!empty($nombreCompleto)){
            $this->nombreCompleto = $nombreCompleto;
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


     public static function buscarClientes(){
       $conexion = new Conexion();
       $consulta = $conexion->prepare('SELECT idCliente, nombreCompleto, dni, email FROM ' . self::TABLA . ' ORDER BY nombreCompleto');
       $consulta->execute();
       $registros = $consulta->fetchAll();
       return $registros;
     }

     public static function buscarClientePorId($id){
        $conexion = new Conexion();
        $consulta = $conexion->prepare('SELECT idCliente, nombreCompleto, dni, email, cuit, razonSocial,
        condicionIva, telefono, celular, estado, esEmpresa, idUsuario, idDomicilio, idDomicilioEntrega
         FROM ' . self::TABLA . ' WHERE idCliente = :id');
        $consulta->bindParam(':id', $id);
        $consulta->execute();
        $registro = $consulta->fetch();
        if($registro){
           return new self($registro['idCliente'], $registro['nombreCompleto'], $registro['dni'], $registro['email'],
           $registro['cuit'], $registro['razonSocial'], $registro['condicionIva'], $registro['telefono'], $registro['celular'],
           $registro['estado'], $registro['esEmpresa'], $registro['isUsuario'], $registro['idDomicilio'], $registro['idDomicilioEntrega']);
        }else{
           return false;
        }
     }

}
?>