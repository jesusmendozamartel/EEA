"use strict"

var Genera_CuentaT = function () {

    $("#result_search").hide();
    $("#nivel").prop("disabled", true);
    $("#dnivel").prop("disabled", true);

    Load_Select('', 'periodo', 'index.php/common/getPeriodo', '', '', '1', '0');

    // ===============================
    // CAMBIO DE TIPO
    // ===============================
    $("#tipo").on("change", function () {

        var tipo = $("#tipo").val();

        $("#nivel").val("").prop("disabled", false);
        $("#dnivel").val("").empty().prop("disabled", true);

        if (tipo === "2") {
            $("#nivel").prop("disabled", false);
        } else if (tipo === "3") {
            $("#nivel").prop("disabled", false);
        } else {
            $("#nivel").prop("disabled", true);
            $("#dnivel").prop("disabled", true);
        }

        var data = "tipo=" + tipo;
        Load_Select('tipo', 'nivel', 'index.php/common/getNivel', data, '', '0', '1');

    });


    // ===============================
    // CAMBIO DE NIVEL
    // ===============================
    $("#nivel").on("change", function () {

        var tipo = $("#tipo").val();
        var nivel = $("#nivel").val();

        if (nivel === "4") {
            $("#dnivel").val("").empty().prop("disabled", true);
            return;
        }

        if (tipo === "2") {

            var data = "nivel=" + nivel;

            Load_Select('nivel', 'dnivel', 'index.php/common/getDnivel', data, '', '0', '0');

            $("#dnivel").prop("disabled", false);

        } else {
            $("#dnivel").val("").empty().prop("disabled", true);
        }

    });


    // ===============================
    // SUBMIT
    // ===============================
    $("#form").on("submit", function (e) {

        e.preventDefault();

        var tipo = $("#tipo").val();
        var nivel = $("#nivel").val();
        var tiponivel = 'Genera_CuentaT_ET';

        if (tipo === "2" && nivel !== "4") {
            tiponivel = 'Genera_CuentaT_AE';
        } else if (tipo === "3") {
            tiponivel = 'Genera_CuentaT_SI';
        }

        $("#form")
            .attr("action", base_url + "index.php/Genera/" + tiponivel)
            .attr("method", "POST");

        Swal.fire({
            title: 'Procesando...',
            text: 'Generando archivo Excel',
            type: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            onOpen: function () {

                Swal.showLoading();

                document.getElementById("form").submit();

                setTimeout(function () {
                    Swal.close();
                }, 1000);

            }
        });

    });


    return {
        init: function () {}
    };

}();

jQuery(document).ready(function () {
    Genera_CuentaT.init();
});