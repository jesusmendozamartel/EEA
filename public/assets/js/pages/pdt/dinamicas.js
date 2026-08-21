// Class definition
"use strict"
var Report_Dinamic = function () {
    // oculta el div de resultados al inicio
    //$("#result_search").hide();
    /**************************************************************************************/
    Load_Select('', 'periodo', 'index.php/pdt/getPeriodoG', '', '', '1', '1');
    /*************************************************************************************/
    var Report_DinamicFormSubmit = function () {
        $("#form_search").validate({
            // define validation rules
            rules: {
                periodo: {
                    required: true
                },
                info: {
                    required: true
                }
            },
            //display error alert on form submit  
            invalidHandler: function (event, validator) {
            },
            submitHandler: function (form) {
                /*var data = "periodo=" + $('#periodo').val() + "&info=" + $('#info').val();
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
                 url: base_url + 'index.php/pdt/reporte_eeff',
                 data: data,
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
                 });*/
                $('.kt-portlet__body').css("display", "none");
                $('#tool_search').addClass("kt-portlet--collapse");
                //$("#result_search").show();
                //$("#view_report").show();

                $(function () {
                    var tpl = $.pivotUtilities.aggregatorTemplates;
                    var derivers = $.pivotUtilities.derivers;
                    var renderers = $.extend($.pivotUtilities.renderers, $.pivotUtilities.plotly_renderers);

                    $.getJSON(base_url+"index.php/common/pivotdata", function (mps) {
                        $("#view_report").pivotUI(mps, {
                            renderers: renderers,
                            hiddenAttributes: ["TOTAL"],
                            aggregators: {
                                "Total":
                                        function () {
                                            return tpl.sum()(["VALOR"])
                                        }
                            },
                            rows: ["AE", "AE_DESCRIPCION"], cols: ["CUENTA"],
                        }, true, "es");
                    });
                });

               /* $(document).ready(function () {
                    $(".pvtTotalLabel.pvtTotalColSortable").addClass('oculto');
                    $(".pvtTotal.colTotal").addClass('oculto');
                });

                $("#btnExport").click(function () {
                    $("#pvtTable").excelexportjs({
                        containerid: "pvtTable",
                        datatype: 'table'
                    });
                });*/

            }
        });
    }
    return {
        // public functions
        init: function () {
            Report_DinamicFormSubmit();
        }
    };
}();

jQuery(document).ready(function () {
    Report_Dinamic.init();
});


