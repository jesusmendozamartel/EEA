// Class definition
"use strict"
var Reporte_Ventas = function () {
    // oculta el div de resultados al inicio
    $("#result_search").hide();
    /**************************************************************************************/
    Load_Select('', 'periodo', 'index.php/common/getPeriodo', '', '', '1', '1');
    /*************************************************************************************/
    $("#periodo").change(function () {
        if ($('#periodo').val() == "") {
            cambia_estado(["nivel", "cboNivMoneda", "modo"], true)
        } else {
            cambia_estado(["nivel", "cboNivMoneda", "modo"], false)
        }
    });
    /*************************************************************************************/
    var Reporte_FormSubmit = function () {
        $("#form_search").validate({
            // define validation rules
            rules: {
                periodo: {
                    required: true
                },
                nivel: {
                    required: true
                },
                modo: {
                    required: true
                },
                cboNivMoneda: {
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
                    url: base_url + 'index.php/pdt/getReporte_Venta/' + $('#periodo').val() + '/' + $('#nivel').val() + '/' + $('#cboNivMoneda').val() + '/' + $('#modo').val()+ '/0',
                    success: function (response) {
                        console.log(response);
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
        if ($('#nivel').val() == '') {
            alert('Seleccione Nivel');
            return false;
        }
        if ($('#modo').val() == '') {
            alert('Seleccione Modo');
            return false;
        }
        if ($('#cboNivMoneda').val() == '') {
            alert('Seleccione Moneda');
            return false;
        }

        window.location.href = base_url + 'index.php/pdt/getReporte_Venta/' + $('#periodo').val() + '/' + $('#nivel').val() + '/' + $('#cboNivMoneda').val() + '/' + $('#modo').val()+ '/1' ;

    });
    /*************************************************************************************/
    return {
        // public functions
        init: function () {
            Reporte_FormSubmit();
        }
    };
}();

jQuery(document).ready(function () {
    Reporte_Ventas.init();
});


