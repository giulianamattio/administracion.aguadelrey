function calcularRutaRepartos() {
    document.getElementById("divLoad").style.display = "inline-block";
    document.getElementById("btnCalcularRepartos").disabled = true;
    document.getElementById("tblPedidos").style.display = "table";
}
function volverHome(){
    window.location.href = "/index";
}

function volverAlListadoRutas(){
    window.location.href = "/pedidos/gestionarRutaRepartos";
}



$(function () {
    $("#tblPedidos").sortable({
        items: 'tr:not(tr:first-child)',
        cursor: 'pointer',
        axis: 'y',
        dropOnEmpty: false,
        start: function (e, ui) {
            ui.item.addClass("selected");
        },
        stop: function (e, ui) {
            ui.item.removeClass("selected");
            $(this).find("tr").each(function (index) {
                if (index > 0) {
                    $(this).find("td").eq(0).html(index);
                }
            });
        }
    });});