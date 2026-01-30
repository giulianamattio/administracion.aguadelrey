function productos(){ 
    var tbody = $('#lista_productos tbody'); 
    var fila_contenido = tbody.find('tr').first().html();
    //Agregar fila nueva. 
    $('#lista_productos .button_agregar_producto').click(function(){ 
       var fila_nueva = $('<tr></tr>');
       fila_nueva.append(fila_contenido); 
       tbody.append(fila_nueva); 
    }); 
    //Eliminar fila. 
    $('#lista_productos').on('click', '.button_eliminar_producto', function(){
       $(this).parents('tr').eq(0).remove();
    });
 }
 
     $(document).ready(function(){
        productos(); 
     }); 
 
     var fila_contenido = tbody.find('tr').first().html();
 
     $('#lista_productos .button_agregar_producto').click(function(){ 
         var fila_nueva = $('<tr></tr>');
         fila_nueva.append(fila_contenido); 
         tbody.append(fila_nueva); 
     });
 
     $('#lista_productos').on('click', '.button_eliminar_producto', function(){
         $(this).parents('tr').eq(0).remove();
     });