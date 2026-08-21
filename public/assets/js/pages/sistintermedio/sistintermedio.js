// Class definition
"use strict"
var SistIntermedio = function () {
  /********************************************* Mostrar opciones de Carga_AExSI ****************************************************************************************************************************/
    
  $("#cboSI").change(function () {
    $("#cboItems").html("");

    var data = "si=" + $("#cboSI").val();

    $.ajax({
            beforeSend: function () {
                swal.fire({
                    title: 'Procesando...',
                    text: 'Espere mientras se procesan los datos',
                    type: "info",
                    onOpen: function () {
                        swal.showLoading()
                    }
                });
            },
            type: "POST",
            cache: false,
            url: base_url + "index.php/sistintermedio/Carga_AExSI",
            data: data,
            success: function (response) {
                console.log(response);
                    $("#cboItems").html("");
                // Remove options
                //$("#cboItems").find('option').not(':first').remove();
                //console.log(abcd);
                $.each(jQuery.parseJSON(response), function (key, data) {
                    //console.log(data['brand_name']);
                    $("#cboItems").append('<option value="' + data['cod'] + '">' + data['des'] + '</option>');
                });
                $(".swal2-container").hide(); //oculta div de procesamiento.
            },
            error: function (e) {
                $("#cboItems").html("Error: " + eval(e));
            },
            dataType: 'html'
        });
    });
    /********************************************* Mostrar opciones de Items (AE o SI)****************************************************************************************************************************/
    $("#cboNivel1").change(function () {
        $("#cboItems").html("");

		if ($("#cboNivel1").val() == 'ae') {
			$('#SectorSI').hide("fast");
		} else {
			$('#SectorSI').show("fast");
		}

        var data = "nivel=" + $("#cboNivel1").val();

        $.ajax({
            beforeSend: function () {
                swal.fire({
                    title: 'Procesando...',
                    text: 'Espere mientras se procesan los datos',
                    type: "info",
                    onOpen: function () {
                        swal.showLoading()
                    }
                });
            },
            type: "POST",
            cache: false,
            url: base_url + "index.php/sistintermedio/Carga_Item",
            data: data,
            success: function (response) {
                console.log(response);
                if ($("#cboNivel1").val() == 'ae') {
                    $("#cboItems").html("");
                } else {
                    $("#cboSI").html("");
                }
                // Remove options
                //$("#cboItems").find('option').not(':first').remove();
                //console.log(abcd);
                $.each(jQuery.parseJSON(response), function (key, data) {
                    //console.log(data['brand_name']);
                    if ($("#cboNivel1").val() == 'ae') {
                        $("#cboItems").append('<option value="' + data['cod'] + '">' + data['des'] + '</option>');
                    } else {
                        $("#cboSI").append('<option value="' + data['cod'] + '">' + data['des'] + '</option>');
                    }
                });
                $(".swal2-container").hide(); //oculta div de procesamiento.
            },
            error: function (e) {
                $("#cboItems").html("Error: " + eval(e));
            },
            dataType: 'html'
        });
    });

	/********************************** Envío de datos a la plantilla de Excel ***************************************************/
    var SistIntermedioFormSubmit = function () {
        $("#form").validate({
            // define validation rules
            rules: {
                cboPedido1: {
                    required: true
                },
                cboNivel1: {
                    required: true
                },
				cboAnnio1: {
                    required: true
                },
                cboItems: {
                    required: true
                }
            },
            //display error alert on form submit  
            invalidHandler: function (event, validator) {
            },
            submitHandler: function (form) {

                var cboItems = [];
                $('#cboItems option:selected').each(function () {
                    cboItems.push($(this).val());
                });


                var data = "cbonivel=" + $('#cboNivel1').val() + "&cboAnio=" + $('#cboAnnio1').val() + "&cboItems=" + cboItems + "&cboPedido=" + $('#cboPedido1').val();

                var tiponivel = '';
                if ($("#cboNivel1").val() === 'ae') {
                    if ($('#cboAnnio1').val() ==='G6A' || $('#cboAnnio1').val() ==='G6B'){
                        tiponivel = 'create_xls_paso1_PANELES';
                    }else{
                        tiponivel = 'create_xls_paso1_AE';     
                    }                        
                } else {
                    tiponivel = 'create_xls_paso1_SI';
                    var data =data+"&cboSI=" + $('#cboSI').val();                           
                }
                
                var dt = new Date();
                var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();

                //alert(data);return false;
                $.ajax({
                    beforeSend: function () {
                        swal.fire({
                            title: 'Procesando actividad(es)...' + cboItems,
                            text: 'Espere mientras se procesan los datos, este proceso demora de 1 a 5 minutos por Actividad Económica. Hora de Inicio: ' + time,
                            type: "info",
                            allowOutsideClick: false,
                            onOpen: function () {
                                swal.showLoading()
                            }
                        });
                    },
                    type: "POST",
                    dataType: "html",
                    url: base_url + 'index.php/sistintermedio/' + tiponivel,
                    data: data,
                    success: function (response) {
						//alert(response)
                        //console.log(response);
                        window.location = base_url + 'Generated/' + response;
                        swal.fire("¡Archivo Generado exitosamente!", "Presione OK para continuar", "success");
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        //alert(xhr.status);
                        //alert(thrownError);
                        swal.fire(xhr.status + ' ' + thrownError, "¡Ha ocurrido un error, consulte con el administrador!", "error");
                    }
                }); 
            }
        });
    }
    return {
        // public functions
        init: function () {
            SistIntermedioFormSubmit();
        }
    };
}();

jQuery(document).ready(function () {
    SistIntermedio.init();
});



