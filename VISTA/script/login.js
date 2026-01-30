function mostrarErrorUsuario(error){
    if(error == true){
      document.getElementById("usuario").classList.add("is-invalid");
      document.getElementById("mensajeErrorUsuario").style.display = "block";
    }else if(error == false){
      document.getElementById("usuario").classList.remove("is-invalid");
      document.getElementById("mensajeErrorUsuario").style.display = "none";
    }
  }

  function mostrarErrorPassword(error){
    if(error == true){
      document.getElementById("password").classList.add("is-invalid");
      document.getElementById("mensajeErrorPassword").style.display = "block";
    }else if(error == false){
      document.getElementById("password").classList.remove("is-invalid");
      document.getElementById("mensajeErrorPassword").style.display = "none";
    }
  }

  function validarInicioSesion(theForm){
    if (theForm.usuario.value == "" && theForm.password.value == ""){
      mostrarErrorUsuario(true);
      mostrarErrorPassword(true);
      return (false);
    }else if ((theForm.usuario.value != "" || theForm.usuario.value != NULL) && theForm.password.value == ""){
      mostrarErrorUsuario(false);
      mostrarErrorPassword(true);
      return (false);
    }else if (theForm.usuario.value == "" && (theForm.password.value != "" && theForm.password.value != NULL)){
      mostrarErrorUsuario(true);
      mostrarErrorPassword(false);
      return (false);
    }
    return (true);
  }

  function validarUsuario(usuario){
    if(usuario != ""){
      document.getElementById("usuario").classList.remove("is-invalid");
      document.getElementById("mensajeErrorUsuario").style.display = "none";
    }else{
      document.getElementById("usuario").classList.add("is-invalid");
      document.getElementById("mensajeErrorUsuario").style.display = "block";
    }
  }

  function validarContrasenia(contrasenia){
    if(contrasenia != ""){
      document.getElementById("password").classList.remove("is-invalid");
      document.getElementById("mensajeErrorPassword").style.display = "none";
    }else{
      document.getElementById("password").classList.add("is-invalid");
      document.getElementById("mensajeErrorPassword").style.display = "block";
    }
  }