function validarNuevoPedido(theForm){
    if (theForm.fecha.value === ""){
        alert ("La fecha es un dato requerido.");
        theForm.fecha.focus();
        return false;
    }
    if (theForm.cliente.value === ""  ||  theForm.cliente.value == 0){
        alert ("Por favor, ingrese el cliente.");
        theForm.cliente.focus();
        return false;
    }
    if (theForm.total.value === ""){
      alert ("El monto total es un dato requerido.");
      theForm.total.focus();
      return false;
    }

    //Ingrese al menos un producto
    if (theForm.productos.value === ""){
      alert ("Debe ingresar al menos un producto. ");
      theForm.total.focus();
      return false;
    }


      return true;
}


function validarEvento(theForm){
  if (theForm.nombreEvento.value === ""){
      alert ("Por favor, ingrese el nombre del evento.");
      theForm.nombreEvento.focus();
      return false;
  }
  if (theForm.fecha.value === ""){
      alert ("Por favor, ingrese la fecha del evento.");
      theForm.fecha.focus();
      return false;
  }

    return true;
}


function validarCoreografia(theForm){
    if (theForm.academia.value === ""){
      alert ("Por favor, ingrese el nombre de la academia.");
      theForm.academia.focus();
      return false;
    }
    else if (theForm.telefono.value === ""){
       alert("Por favor, ingrese el nro de telefono.");
       theForm.telefono.focus();
       return false;
    }
    else if (theForm.maestro.value === ""){
        alert("Por favor, ingrese el nombre del maestro preparador.");
        theForm.maestro.focus();
        return false;
    }
    else if (theForm.modalidad.value === ""){
        alert("Por favor, ingrese la modalidad de la coreografia.");
        theForm.modalidad.focus();
        return false;
    }
    else if (theForm.categoria.value === "" ||  theForm.categoria.value == 0){
        alert("Por favor, ingrese la categoria.");
        theForm.categoria.focus();
        return false;
    }
    else if (theForm.participacion.value === "" || theForm.participacion.value == 0){
        alert("Por favor, ingrese la forma de participacion.");
        theForm.participacion.focus();
        return false;
    }
    else if (theForm.participacion.value == 4){
        if (theForm.cantidadParticipantes.value === "" || theForm.cantidadParticipantes.value == 0){
            alert("Por favor, ingrese la cantidad de participantes.");
            theForm.cantidadParticipantes.focus();
            return (false);
        }
    }

    else if (theForm.participacion.value == 1){ //Solo
      if (theForm.nombre1.value === "" || theForm.apellido1.value === "" || theForm.dni1.value === ""){
            alert("Debe informar la totalidad de los participantes de la coreografia.");
        theForm.nombre1.focus();
        return false;
      }
    }else if (theForm.participacion.value == 2){ //Duo
      if ((theForm.nombre1.value === "" || theForm.apellido1.value === "" || theForm.dni1.value === "")
          && (theForm.nombre2.value === "" || theForm.apellido2.value === "" || theForm.dni2.value === "")){
        alert("Debe informar la totalidad de los participantes de la coreografia.");
        theForm.nombre1.focus();
        return false;
      }
    }else if (theForm.participacion.value == 3){ //Trio
      if ((theForm.nombre1.value === "" || theForm.apellido1.value === "" || theForm.dni1.value === "")
          && (theForm.nombre2.value === "" || theForm.apellido2.value === "" || theForm.dni2.value === "")
          && (theForm.nombre3.value === "" || theForm.apellido3.value === "" || theForm.dni3.value === "")){
        alert("Debe informar la totalidad de los participantes de la coreografia.");
        theForm.nombre1.focus();
        return false;
      }
    }
    /*else if (theForm.participacion.value == 4){ //Grupal
      //For
    }*/
    
    return true;
}