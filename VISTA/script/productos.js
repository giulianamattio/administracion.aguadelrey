function productos(){ 
   var tbody = $('#lista_productos tbody'); 

   // Agregar fila nueva. 
   $('#lista_productos .button_agregar_producto').click(function(){ 
      document.getElementById("cantidadProductoActual").value = parseInt(document.getElementById("cantidadProductoActual").value) + 1; 
      var cantidadProductoActual = document.getElementById("cantidadProductoActual").value; 

      var fila_contenido = `
         <td>
            <select class="form-control form-control-sm select-producto" id="producto${cantidadProductoActual}" name="producto${cantidadProductoActual}">
               <option value="0">Seleccione el producto</option>
            </select>
            <div class="invalid-feedback d-block text-danger small error-producto${cantidadProductoActual}"></div>
         </td>
         <td>
            <input type="number" class="form-control form-control-sm input-cantidad" id="cantidad${cantidadProductoActual}" name="cantidad${cantidadProductoActual}" min="1" />
            <div class="invalid-feedback d-block text-danger small error-cantidad${cantidadProductoActual}"></div>
         </td>
         <td>
            <i class="fas fa-minus-square fa-lg button_eliminar_producto" style="color: #dc3545;"></i>
         </td>`;

      var fila_nueva = $('<tr></tr>');
      fila_nueva.append(fila_contenido); 
      tbody.append(fila_nueva);
      cargarSelectProductos(cantidadProductoActual);
   }); 

   // Eliminar fila. 
   $('#lista_productos').on('click', '.button_eliminar_producto', function(){
      $(this).parents('tr').eq(0).remove();
      document.getElementById("cantidadProductoActual").value = parseInt(document.getElementById("cantidadProductoActual").value) - 1;
      recalcularTotal();
   });

}

// Recorre todas las filas y suma precio * cantidad
function recalcularTotal() {
   var total = 0;

   $('#lista_productos tbody tr').each(function() {
      var select   = $(this).find('select');
      var cantidad = parseFloat($(this).find('input[type="number"]').val()) || 0;
      var precio   = parseFloat(select.find('option:selected').data('precio')) || 0;
      total += precio * cantidad;
   });

   $('#total').val(total > 0 ? total.toFixed(2) : '');
}

// Al cambiar producto → recalcular
$(document).on('change', '.select-producto', function() {
   recalcularTotal();
});

// Al cambiar cantidad → recalcular
$(document).on('input', '.input-cantidad', function() {
   recalcularTotal();
});


$(document).ready(function(){
   productos(); 

   // Limpiar error al completar cada campo
   $('#fecha').on('change', function () {
      $(this).removeClass('is-invalid');
      $('#error-fecha').text('');
   });

   $('#cliente').on('change', function () {
      $(this).removeClass('is-invalid');
      $('#error-cliente').text('');
   });

   $('#total').on('input', function () {
      $(this).removeClass('is-invalid');
      $('#error-total').text('');
   });

   // Limpiar errores en productos y cantidades dinámicas
   $('#lista_productos').on('change', 'select', function () {
      $(this).removeClass('is-invalid');
      var id = $(this).attr('id').replace('producto', '');
      $('.error-producto' + id).text('');
   });

   $('#lista_productos').on('input', 'input[type="number"]', function () {
      $(this).removeClass('is-invalid');
      var id = $(this).attr('id').replace('cantidad', '');
      $('.error-cantidad' + id).text('');
   });
}); 


function cargarSelectProductos(idSelect) {
   $.ajax({ 
      url: "/CONTROLADOR/pedidos/consultaProductos.php", 
      type: "GET", 
      dataType: "json", 
      success: function(data) { 
         let select = $("#producto" + idSelect);
         select.empty(); 
         select.append('<option value="0">Seleccione un producto</option>'); 
         data.forEach(function(item) { 
            select.append(
               $('<option></option>')
                  .val(item.idProducto)
                  .text(item.descripcion)
                  .data('precio', item.precioUnitario)  // precio en data attribute
            ); 
         }); 
      } 
   }); 
}