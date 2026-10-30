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

                var xhr = new XMLHttpRequest();
                xhr.open("POST", base_url + "index.php/genera/sistema-intermedio");
                xhr.responseType = "blob";
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

                xhr.onloadstart = function () {
                    Swal.fire({
                        title: "Procesando...",
                        text: "Generando el archivo Excel seleccionado",
                        type: "info",
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        onOpen: function () { Swal.showLoading(); }
                    });
                };

                xhr.onload = function () {
                    if (xhr.status < 200 || xhr.status >= 300) {
                        var mensajeError = "No se pudo generar el archivo. Revise los datos e inténtelo nuevamente.";
                        if (xhr.response && xhr.response.text) {
                            xhr.response.text().then(function (texto) {
                                if (texto.trim() === "No se encontró información") {
                                    Swal.fire("Sin información", texto.trim(), "info");
                                } else {
                                    Swal.fire("Error " + xhr.status, mensajeError, "error");
                                }
                            });
                        } else {
                            Swal.fire("Error " + xhr.status, mensajeError, "error");
                        }
                        return;
                    }

                    if (!xhr.response || xhr.response.size < 4) {
                        Swal.fire("Archivo vacío", "El servidor no devolvió un Excel válido.", "error");
                        return;
                    }

                    var signatureReader = new FileReader();
                    signatureReader.onload = function () {
                        var signature = new Uint8Array(signatureReader.result);
                        if (signature[0] !== 0x50 || signature[1] !== 0x4B) {
                            Swal.fire("No se recibió un Excel", "Revise su sesión e inténtelo nuevamente.", "error");
                            return;
                        }

                    var disposition = xhr.getResponseHeader("Content-Disposition") || "";
                    var filenameMatch = disposition.match(/filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i);
                    var anio = $("#anio").val();
                    var formato = $("#formato").val();
                    var dnivel = ($("#dnivel").val() || "").replace(/[^A-Za-z0-9_-]/g, "_");
                    var nombreAlternativo = anio === "2021" && (formato === "G" || formato === "P")
                        ? "V_CENEC_2021_" + formato + "_Plantilla_AE_N104_" + dnivel + ".xlsx"
                        : "EEA_" + anio + "_" + formato + "_Plantilla_AE_N104_" + dnivel + ".xlsx";
                    var filename = filenameMatch
                        ? decodeURIComponent(filenameMatch[1] || filenameMatch[2])
                        : nombreAlternativo;
                    var tiempoGeneracion = xhr.getResponseHeader("X-Tiempo-Generacion");
                    var segundosTotales = Number(tiempoGeneracion);
                    var textoTiempo = "Tiempo de generación no disponible";
                    if (tiempoGeneracion && Number.isFinite(segundosTotales) && segundosTotales >= 0) {
                        var totalSegundos = Math.round(segundosTotales);
                        var minutos = Math.floor(totalSegundos / 60);
                        var segundos = totalSegundos % 60;
                        textoTiempo = minutos > 0
                            ? "Generado en " + minutos + " min " + segundos + " s"
                            : "Generado en " + segundos + " segundos";
                    }
                    var downloadUrl = URL.createObjectURL(xhr.response);
                    Swal.fire({
                        title: 'Archivo "' + filename + '" listo',
                        html: '<p>' + textoTiempo + '</p><a id="downloadGeneratedExcel" class="btn btn-success" href="#">Descargar Excel</a>',
                        showConfirmButton: false,
                        showCloseButton: true,
                        type: "success",
                        onOpen: function () {
                            var link = document.getElementById("downloadGeneratedExcel");
                            link.href = downloadUrl;
                            link.download = filename;
                            link.addEventListener("click", function () {
                                window.setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 60000);
                            }, { once: true });
                        },
                        onClose: function () {
                            window.setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 60000);
                        }
                    });
                    };
                    signatureReader.readAsArrayBuffer(xhr.response.slice(0, 4));
                };

                xhr.onerror = function () {
                    Swal.fire("Error de conexión", "No se pudo completar la solicitud.", "error");
                };

                xhr.send(new FormData(form));
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
