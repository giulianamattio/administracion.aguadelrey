function productos(){ 
   var tbody = $('#lista_productos tbody'); 
   //Agregar fila nueva. 
   $('#lista_productos .button_agregar_producto').click(function(){ 
     document.getElementById("cantidadProductoActual").value = parseInt(document.getElementById("cantidadProductoActual").value) +1; 
     var cantidadProductoActual = document.getElementById("cantidadProductoActual").value; 
     //var fila_contenido = tbody.find('tr').first().html();

     var fila_contenido = ` <td> <select class="form-control form-control-sm" id="producto${cantidadProductoActual}"> <option value="0">Seleccione el producto</option> </select> </td> <td> <input type="number" class="form-control form-control-sm" id="cantidad${cantidadProductoActual}" name="cantidad${cantidadProductoActual}" /> </td> <td> <i class="fas fa-minus-square fa-lg button_eliminar_producto" style="color: #dc3545;"></i> </td>`;

      var fila_nueva = $('<tr></tr>');

      fila_nueva.append(fila_contenido); 
      tbody.append(fila_nueva);

      cargarSelectProductos(cantidadProductoActual);
   }); 

   //Eliminar fila. 
   $('#lista_productos').on('click', '.button_eliminar_producto', function(){
      $(this).parents('tr').eq(0).remove();
      document.getElementById("cantidadProductoActual").value = parseInt(document.getElementById("cantidadProductoActual").value) -1; 
   });

}

    $(document).ready(function(){
       productos(); 
    }); 


$('#modalVerDatos').on('shown.bs.modal', function () {
     alert("Hola");
 $('#myInput').trigger('focus')
})


function cargarSelectProductos(idSelect) { 
   $.ajax({ 
      url: "/CONTROLADOR/pedidos/consultaProductos.php", 
      type: "GET", 
      dataType: "json", 
      success: function(data) { 
         let select = $("#producto" + idSelect); 
         select.empty(); 
         select.append('<option value="">Seleccione un producto</option>'); 
         data.forEach(function(item) { 
            select.append( `<option value="${item.idProducto}">${item.descripcion}</option>` ); 
         }); 
      } 
   }); 
}



/*$(document).ready(function() { 
   
   $.ajax({ 
      url: "/CONTROLADOR/pedidos/consultaProductos.php", 
      type: "GET", 
      dataType: "json", 
      success: function(data) { 
         $("#miSelect").empty(); // Limpia el select 
         data.forEach(
            function(item) { 
               $("#miSelect").append( '<option value="${item.id}">${item.nombre}</option>' ); });
             } 
            }); 
         });*/