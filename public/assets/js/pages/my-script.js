/* -----------------------------------------------------------------------------------------------------------------*/
/**** Crea las opciones de Select: Creado by Fmendoza ****/
/* Detalles de la función
 *
 * campo:       Campo que manda parámetros para evaluar la variable rcampo.
 * rcampo:      Campo donde se escribirá los datos resultantes del proceso.
 * url:         Ruta al controlador que se utilizará para el proceso.
 * data:        Datos que serán enviados por POST al controlador.
 * desactivar:  Array con los campos que serán desactivados si campo se encuentra en blanco.
 * tipo:        0 -> Se valida la variable campo.
 *              1 -> No se valida la variable campo.
 * sel:         0 -> No muestra "Seleccione".
 *              1 -> Muestra "Seleccione".
 * */

function Load_Select(campo, rcampo, url, data, desactivar, tipo, sel) {

    var consulta;

    if (tipo === "1") {
        consulta = true;
    } else {
        consulta = $('#' + campo).val() !== '';
    }

    if (consulta) {

        $.ajax({
            type: "POST",
            dataType: "json",
            url: base_url + url,
            data: data,

            success: function (response) {

                console.log(response);

                $("#" + rcampo).empty().attr('disabled', false);

                if (sel === "1") {
                    $("#" + rcampo).append('<option value="">Seleccione</option>');
                }

                $.each(response, function (key, data) {
                    $("#" + rcampo).append(
                        '<option value="' + data.valor + '">' +
                        data.descr +
                        '</option>'
                    );
                });
            },

            error: function (xhr, status, error) {
                console.error("Error Load_Select:", error);
                console.error(xhr.responseText);
            }
        });

    } else {

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