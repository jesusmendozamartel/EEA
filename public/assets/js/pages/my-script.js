/* -----------------------------------------------------------------------------------------------------------------*/
/**** Crea las opciones de Select: Creado by Fmendoza ****/
/* Detalles de la función
 *     
 * campo:       Campo que manda parámetros para evaluar la variable rcampo. 
 * rcampo:      Campo donde se escribirá los datos resultantes del proceso.
 * url:         Ruta al controlador que se utilizará para el proceso, ya cuenta con la variable base_url que fue declarada en el header.
 * data:        Concatenado de los datos que serán enviados por POST al controlador.
 * desactivar:  Array con los campos que serán desactivados si la variable campo se encuentra en blanco.
 * tipo:        0-> No se valida la variable campo, esta opción se usa cuando no se está anidando campos. 
 *              1-> Cuando se anidan campos y se analiza la variable campo para ver si se encuentra en blanco.
 * sel:         0-> No se muestra como primera opción de la lista <option>seleccione</option>.
 *              1-> Se muestra como primera opción de la lista <option>seleccione</option>.
 * */

function Load_Select(campo, rcampo, url, data, desactivar, tipo, sel) {
    var consulta;
    if (tipo === "1") {
        consulta = true;
    } else {
        if ($('#' + campo).val() !== '') {
            consulta = true;
        } else {
            consulta = false;
        }
    }
    if (consulta) {
        $.ajax({
            beforeSend: function () {
            },
            type: "POST",
            dataType: "html",
            url: base_url + url,
            data: data,
            success: function (response) {
                console.log(response);
                $("#" + rcampo).empty().attr('disabled', false);
                if (sel === "1") {
                    $("#" + rcampo).append('<option value="">Seleccione</option>');
                }
                $.each(jQuery.parseJSON(response), function (key, data) {
                    $("#" + rcampo).append('<option value="' + data['valor'] + '">' + data['descr'] + '</option>');
                });
            }
        });
    } else { //colocar un each para poder leer array
        $.each(desactivar, function (i, val) {
            $("#" + val).empty().attr("disabled", true);
            $("#" + val).append('<option value="">Seleccione</option>');
        });
    }
}
/* -----------------------------------------------------------------------------------------------------------------*/
/* 
 * campos -> array de campos a ser activados o desactivados.
 * accion -> true: Deshabilitar, false: Habilitar.
 */
function cambia_estado(campos, accion) {
    $.each(campos, function (i, val) {
        $("#" + val).attr("disabled", accion).val("");
    });
}

/* -----------------------------------------------------------------------------------------------------------------*/
/* 
 * Función para exportar a Excel.
 */
$(document).ready(function () {
    $('#ExpExcel').click(function () {
        // lets say you got ur data from your post method
        var data = "periodo=" + $('#periodo').val() + "&grupo=" + $('#grupo').val() + "&nivel=" + $('#nivel').val() + "&dnivel=" + $('#dnivel').val() + "&detalle=" + $('#detalle').val();
    });
});