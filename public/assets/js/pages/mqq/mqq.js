"use strict"

var Genera_MQQ = function () {

    $("#result_search").hide();

    Load_Select('', 'periodo', 'index.php/common/getPeriodo', '', '', '1', '0');
    Load_Select('', 'transac', 'index.php/common/getTransac', '', '', '1', '0');

    $("#form").validate({
        rules: {
            periodo: {
                required: true
            },
            transac: {
                required: true
            }
        },

        submitHandler: function (form) {

            // Configurar action dinámicamente
            $("#form")
                .attr("action", base_url + "index.php/Genera/Genera_MQQ")
                .attr("method", "POST");

            Swal.fire({
                title: 'Procesando...',
                text: 'Generando archivo Excel',
                type: 'info', // compatible con tu versión
                allowOutsideClick: false,
                showConfirmButton: false,
                onOpen: function () {

                    Swal.showLoading();

                    // Enviar formulario (descarga real)
                    document.getElementById("form").submit();

                    // Cerrar automáticamente después de 1 segundos
                    setTimeout(function () {
                        Swal.close();
                    }, 1000);

                }
            });
        }
    });

    return {
        init: function () {
            // ya inicializado
        }
    };

}();

jQuery(document).ready(function () {
    Genera_MQQ.init();
});