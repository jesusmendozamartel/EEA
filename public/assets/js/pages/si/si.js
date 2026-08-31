"use strict"

var Genera_SI = function () {
    
    $("#nivel").prop("disabled", true);
    $("#dnivel").prop("disabled", true);

    Load_Select('', 'anio', 'index.php/common/getAnio', '', '', '1', '0');

    // CAMBIO DE PLANTILLA
    $("#plantilla").on("change", function () {

        var plantilla = $("#plantilla").val();

        $("#nivel").val("").prop("disabled", plantilla === "");
        $("#dnivel").val("").empty().prop("disabled", true);
        
        var data = "plantilla=" + plantilla;
        Load_Select('plantilla', 'nivel', 'index.php/common/getNivel', data, '', '1', '1');

    });

    // CAMBIO DE NIVEL
    $("#nivel").on("change", function () {

        var nivel = $("#nivel").val();

        $("#dnivel").val("").empty().prop("disabled", true);

        if (nivel !== "") {
            var data = "nivel=" + nivel;
            Load_Select('nivel', 'dnivel', 'index.php/common/getDnivel', data, '', '0', '1');
            $("#dnivel").prop("disabled", false);
        }

    });

    // VALIDACIÓN Y ENVÍO
    var GeneraSIFormSubmit = function () {

    $("#form").validate({

        rules: {
                anio: {
                required: true
                },
                formato: {
                    required: true
                },
                plantilla: {
                    required: true
                },
                nivel: {
                    required: true
            }
        },

            invalidHandler: function (event, validator) {
            },

        submitHandler: function (form) {

                var data = "anio=" + $('#anio').val()
                    + "&formato=" + $('#formato').val()
                    + "&plantilla=" + $('#plantilla').val()
                    + "&nivel=" + $('#nivel').val()
                    + "&dnivel=" + $('#dnivel').val();

                $.ajax({
                    beforeSend: function () {

            Swal.fire({
                title: 'Procesando...',
                            text: 'Generando archivo Excel Seleccionado',
                            type: "info",
                allowOutsideClick: false,
                showConfirmButton: false,
                onOpen: function () {
                    Swal.showLoading();
                            }
                        });

                    },

                    type: "POST",
                    dataType: "html",
                    url: base_url + 'index.php/Genera/Genera_SI',
                    data: data,

                    success: function (response) {
                        console.log(response);

                        Swal.fire(
                            "¡Archivo Generado exitosamente!",
                            "Presione OK para continuar",
                            "success"
                        );
                    },

                    error: function (xhr, ajaxOptions, thrownError) {
                        Swal.fire(
                            xhr.status + ' ' + thrownError,
                            "¡Ha ocurrido un error, consulte con el administrador!",
                            "error"
                        );
                }
            });
        }
    });
    };

    GeneraSIFormSubmit();

    return {
        init: function () {}
    };

}();

jQuery(document).ready(function () {
    Genera_SI.init();
});