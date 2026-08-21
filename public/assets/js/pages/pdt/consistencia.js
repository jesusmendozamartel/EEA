// Class definition
"use strict"
var Reporte_Consistencia = function () {
    // oculta el div de resultados al inicio
    $("#result_search").hide();
    /**************************************************************************************/
    Load_Select('', 'periodo', 'index.php/common/getPeriodo', '', '', '1', '1');
    /*************************************************************************************/
    var Reporte_CFormSubmit = function () {
        $("#form_search").validate({
            // define validation rules
            rules: {
                periodo: {
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
                    url: base_url + 'index.php/pdt/getReporte_Consistencia/'+$('#periodo').val()+ '/0'+ '/' + $('#redondeo').val(),
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
        if ($('#periodo').val() == '') {
            alert('Seleccione Periodo');
            return false;
        }
        window.location.href = base_url + 'index.php/pdt/getReporte_Consistencia/'+$('#periodo').val()+ '/1'+ '/' + $('#redondeo').val();

    });
    /*************************************************************************************/
    return {
        // public functions
        init: function () {
            Reporte_CFormSubmit();
        }
    };
}();

jQuery(document).ready(function () {
    Reporte_Consistencia.init();
});


