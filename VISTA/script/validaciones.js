function validarNuevoPedido(form) {
    let valido = true;

    // Limpiar errores previos
    $('.error-msg').text('');
    $('.form-control').removeClass('is-invalid');

    // Validar fecha
    const fecha = $('#fecha').val();
    if (!fecha) {
        $('#error-fecha').text('La fecha es obligatoria.');
        $('#fecha').addClass('is-invalid');
        valido = false;
    }

    // Validar cliente
    const cliente = $('#cliente').val();
    if (!cliente || cliente == '0') {
        $('#error-cliente').text('Debe seleccionar un cliente.');
        $('#cliente').addClass('is-invalid');
        valido = false;
    }

    // Validar total
    const total = $('#total').val();
    if (!total || isNaN(total) || parseFloat(total) <= 0) {
        $('#error-total').text('El monto total debe ser un número mayor a 0.');
        $('#total').addClass('is-invalid');
        valido = false;
    }

    // Validar productos y cantidades
    const cantidad = parseInt($('#cantidadProductoActual').val());
    const productosSeleccionados = [];

    for (let i = 1; i <= cantidad; i++) {
        const producto = $('#producto' + i).val();
        const cant     = $('#cantidad' + i).val();

        if (!producto || producto == '0') {
            $('.error-producto' + i).text('Debe seleccionar un producto.');
            $('#producto' + i).addClass('is-invalid');
            valido = false;
        } else if (productosSeleccionados.includes(producto)) {
            // Producto duplicado
            $('.error-producto' + i).text('Este producto ya fue agregado.');
            $('#producto' + i).addClass('is-invalid');
            valido = false;
        } else {
            productosSeleccionados.push(producto);
        }

        if (!cant || isNaN(cant) || parseInt(cant) <= 0) {
            $('.error-cantidad' + i).text('La cantidad debe ser mayor a 0.');
            $('#cantidad' + i).addClass('is-invalid');
            valido = false;
        }
    }

    return valido;
}
