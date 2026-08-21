// Class definition
"use strict"
var PDT_Anual = function () {
    // oculta el div de resultados al inicio
    $("#result_search").hide();
    /**************************************************************************************/
    Load_Select('', 'periodo', 'index.php/common/getPeriodo', '', '', '1', '0');
    /*************************************************************************************/
    $("#tipo").change(function () {
        var data = "periodo=" + $('#periodo').val() + "&panel=" + $('#tipo').val();
        var desactivar = ["grupo", "nivel", "dnivel"];
        Load_Select('tipo', 'grupo', 'index.php/common/getGrupo', data, desactivar, '0', '0')
    });
    /*************************************************************************************/
    $("#grupo").change(function () {
        var data = "periodo=" + $('#periodo').val() + "&grupo=" + $('#grupo').val();
        var desactivar = ["nivel", "dnivel"];
        Load_Select('grupo', 'nivel', 'index.php/common/getNivel', data, desactivar, '0', '0')
    });
    /*************************************************************************************/
    $("#nivel").change(function () {
        var data = "periodo=" + $('#periodo').val() + "&grupo=" + $('#grupo').val() + "&nivel=" + $('#nivel').val();
        var desactivar = ["dnivel"];
        Load_Select('nivel', 'dnivel', 'index.php/common/getCodigo', data, desactivar, '0', '0')
    });
    /*************************************************************************************/
    var PDTAnualFormSubmit = function () {
        $("#form_search").validate({
            // define validation rules
            rules: {
                periodo: {
                    required: true
                },
                tipo: {
                    required: true
                },
                grupo: {
                    required: true
                },
                nivel: {
                    required: true
                },
                dnivel: {
                    required: true
                }
            },
            //display error alert on form submit  
            invalidHandler: function (event, validator) {
            },
            submitHandler: function (form) {
                $.ajax({
                    beforeSend: function () {
                        swal.fire({
                            title: 'Procesando...',
                            text: 'Espere mientras se procesan los datos',
                            type: "info",
                            timer: 1000,
                            onOpen: function () {
                                swal.showLoading()
                            }
                        });
                    },
                    type: "POST",
                    dataType: "html",
                    url: base_url + 'index.php/pdt/reporte_eeff/' + $('#periodo').val() + '/' + $('#grupo').val() + '/' + $('#nivel').val() + '/' + $('#dnivel').val() + '/' + $('#detalle').val() + '/0'+ '/' + $('#redondeo').val(),
                    success: function (response) {
                        //console.log(response);
                        $('.kt-portlet__body').css("display", "none");
                        $('#tool_search').addClass("kt-portlet--collapse");
                        $("#result_search").show();
                        $("#view_report").html(response);
                        $("#view_report").show();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert(xhr.status);
                        alert(thrownError);
                    }
                });
            }
        });
    }

    /*************************************************************************************/
    $("#ExpExcel").click(function () {
        if ($('#tipo').val() == '') {
            alert('Seleccione Tipo');
            return false;
        }
        if ($('#grupo').val() == '') {
            alert('Seleccione Grupo');
            return false;
        }
        if ($('#nivel').val() == '') {
            alert('Seleccione Nivel');
            return false;
        }
        if ($('#dnivel').val() == '') {
            alert('Seleccione Código');
            return false;
        }
        if ($('#detalle').val() == '') {
            alert('Seleccione Detalle');
            return false;
        }

        window.location.href = base_url + 'index.php/pdt/reporte_eeff/' + $('#periodo').val() + '/' + $('#grupo').val() + '/' + $('#nivel').val() + '/' + $('#dnivel').val() + '/' + $('#detalle').val() + '/1'+ '/' + $('#redondeo').val();

    });
    /*************************************************************************************/
    return {
        // public functions
        init: function () {
            PDTAnualFormSubmit();
            cargaCodigos();
        }
    };
}();

jQuery(document).ready(function () {
    PDT_Anual.init();
});


