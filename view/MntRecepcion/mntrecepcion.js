
function init(){
    $("#mantenimiento_form").on("submit",function(e){
        guardaryeditar(e);
    });
    
   
}

$(document).ready(function(){
    var habitacion_num = getUrlParameter('habitacion');

    // Mostrar el número de habitación si está disponible
    if (habitacion_num) {
        $('#txtnombre').html('Habitación ' + habitacion_num);
        
        // Obtener los datos de la habitación por su número
        $.post("../../controller/habitacion.php?op=obtener_por_numero",{hab_num:habitacion_num},function(data){
            data=JSON.parse(data);
            if (!data.error) {
                $('#txtnombre').html((data.CAT_NOM || 'Habitación') + ' ' + (data.HAB_NUM || habitacion_num || ''));
                $('#txtdetalle').html(data.HAB_DET || 'Sin detalles');
                $('#txtcategoria').html(data.CAT_NOM || 'Sin categoría');
                var basePre = parseFloat(data.HAB_PRE || '0') || 0;
                $('#txtprecio').html('S/ ' + (basePre ? basePre.toFixed(2) : '0.00'));
                $('#txtestado').html(data.ESTADO_NOM || 'Sin estado');
                // Setear hab_id oculto y precargar precio inicial y base de cálculo (3h)
                $('#hab_id').val(data.HAB_ID || '');
                // Con hab_id listo, cargar tarifas asignadas a esta habitación
                try { loadTarifas(); } catch(e) {}
                if ($('#precio_inicial').length) {
                    if (basePre > 0) {
                        $('#precio_inicial').val(basePre.toFixed(2));
                        // Guardar precio por noche como data attribute
                        $('#precio_inicial')
                            .attr('data-noche', basePre)
                            .data('noche', basePre);
                    }
                }
            } else {
                $('#txtnombre').html('Habitación ' + habitacion_num + ' (No encontrada)');
            }
        }).fail(function() {
            $('#txtnombre').html('Habitación ' + habitacion_num + ' (Error al cargar)');
        });
    } else {
        $('#txtnombre').html('Nueva Reservación');
    }
   $('#cli_id').select2();

    $.post("../../controller/cliente.php?op=combo",function(data){
        $("#cli_id").html(data);
    });

    $("#cli_id").change(function(){
        
        $("#cli_id").each(function(){
            cli_id = $(this).val();
            $.post("../../controller/cliente.php?op=mostrar",{cli_id:cli_id},function(data){
                data=JSON.parse(data);
                $('#cli_tipo_doc').val(data.CLI_TIPO_DOC || 'No especificado');
                $('#cli_doc').val(data.CLI_DOC || 'No especificado');
                $('#cli_direcc').val(data.CLI_DIR || 'No especificada');
            });
        });
    });

    // Cargar combo de tarifas según hab_id ya seteado
    function loadTarifas() {
        var idNum = parseInt($('#hab_id').val() || '0', 10);
        if (!$('#tar_id').data('select2')) {
            $('#tar_id').select2({ width: '100%' });
        }
        // Placeholder mientras no haya habitación
        if (!idNum || idNum <= 0) {
            $("#tar_id").html("<option value='0' selected>Seleccione</option>");
            return;
        }
        $.post("../../controller/tarifa.php?op=combo", { hab_id: idNum }, function(html) {
            $("#tar_id").html(html);
            // Notificar a Select2 del cambio de opciones
            $('#tar_id').trigger('change');
        }).fail(function(){
            $("#tar_id").html("<option value='0' selected>Error al cargar</option>");
        });
    }

    // Llamada inicial (placeholder) y luego se relanza cuando se obtenga hab_id
    loadTarifas();



});

// Al seleccionar una tarifa, reflejar el precio en el campo y encabezado
$(document).on('change', '#tar_id', function() {
    var $opt = $(this).find('option:selected');
    var raw = ($opt.data('precio') !== undefined) ? $opt.data('precio').toString() : '';
    var precio = parseFloat((raw || '').replace(',', '.'));
    if (!isNaN(precio) && precio > 0) {
        // Campo de precio inicial
        $('#precio_inicial').val(precio.toFixed(2));
        $('#precio_inicial').attr('data-noche', precio).data('noche', precio);
        // Texto visible del precio en la cabecera
        if ($('#txtprecio').length) {
            $('#txtprecio').html('S/ ' + precio.toFixed(2));
        }
        // Recalcular totales y fecha de salida
        try { updateTotals(); } catch(e) {}
    }
});

// Inicializar fechas con flatpickr: entrada = hoy (solo mostrar), salida = seleccionable con hora
function initFechasRecepcion() {
    try {
        if (typeof flatpickr !== 'undefined') {
            var $fechaEntrada = document.getElementById('fecha_entrada');
            if ($fechaEntrada) {
                var fpE = flatpickr($fechaEntrada, {
                    dateFormat: 'd M, Y',
                    defaultDate: new Date(),
                    clickOpens: false,
                    allowInput: false,
                    disableMobile: true
                });
                window.fpFechaEntrada = fpE;
                $fechaEntrada.readOnly = true;
            }

            var $fechaSalida = document.getElementById('fecha_salida');
            if ($fechaSalida) {
                var fpS = flatpickr($fechaSalida, {
                    // Valor enviado al backend
                    dateFormat: 'Y-m-d H:i',
                    // Visualización amigable para el usuario
                    altInput: true,
                    altFormat: 'd M, Y H:i',
                    enableTime: true,
                    time_24hr: true,
                    minuteIncrement: 1,
                    minDate: new Date(),
                    allowInput: true,
                    disableMobile: true,
                    onChange: function(selectedDates) {
                        try { handleFechaSalidaChange(selectedDates); } catch(e) {}
                    }
                });
                window.fpFechaSalida = fpS;
            }
        }
    } catch (e) {
        // Si flatpickr no está disponible, al menos setear fecha actual manualmente
        var fe = document.getElementById('fecha_entrada');
        if (fe) {
            var d = new Date();
            var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            var txt = ('0' + d.getDate()).slice(-2) + ' ' + meses[d.getMonth()] + ', ' + d.getFullYear();
            fe.value = txt;
            fe.readOnly = true;
        }
    }
}

// Ejecutar inicialización al cargar
document.addEventListener('DOMContentLoaded', initFechasRecepcion);

// --- Cálculo de duración, fecha de salida y precio ---
function formatDateYmdHi(d) {
    var yyyy = d.getFullYear();
    var mm = ('0' + (d.getMonth() + 1)).slice(-2);
    var dd = ('0' + d.getDate()).slice(-2);
    var hh = ('0' + d.getHours()).slice(-2);
    var mi = ('0' + d.getMinutes()).slice(-2);
    return yyyy + '-' + mm + '-' + dd + ' ' + hh + ':' + mi;
}



// Enlazar eventos de duración
$(document).on('change', '#duracion_tipo', function() {
    // Forzar horas
    $('#duracion_tipo').val('horas');
    recalcularSalidaYPrecio();
});

$(document).on('input', '#duracion_valor', function() {
    var v = ($(this).val() || '').toString().replace(/,/g, '.');
    var num = parseFloat(v || '0');
    if (isNaN(num)) num = 3;
    num = Math.min(Math.max(1, num), 3);
    $(this).val(num);
    recalcularSalidaYPrecio();
});

// Establecer valores por defecto y cálculo inicial
document.addEventListener('DOMContentLoaded', function() {
    var $tipo = $('#duracion_tipo');
    var $valor = $('#duracion_valor');
    if ($tipo.length) { $tipo.val('horas'); }
    if ($valor.length && !(parseFloat($valor.val() || '0') > 0)) { $valor.val('3'); }
    // Calcular con valores iniciales
    try { recalcularSalidaYPrecio(); } catch(e) {}
});
/* TODO: Obtener parametro de URL */
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

// Evento para guardar el cliente desde el modal
$(document).on("submit","#mantenimiento_form",function(e){
    e.preventDefault();
    
    // Validación de DNI/RUC antes de enviar
    var $modal = $('#modalmantenimiento');
    var tipoDocSel = $modal.find('#cli_tipo_doc').val();
    var $docInput = $modal.find('#cli_doc');
    var docVal = ($docInput.val() || '').trim();
    var $docFeedback = $modal.find('#cli_doc_feedback');
    
    // Normalizar a solo dígitos
    docVal = docVal.replace(/\D/g, '');
    $docInput.val(docVal);
    
    // Reglas de validación
    var dniOk = /^\d{8}$/.test(docVal);
    var rucOk = /^\d{11}$/.test(docVal);
    
    // Solo validar longitudes si el tipo seleccionado es DNI o RUC
    if (tipoDocSel === 'DNI') {
        if (!dniOk) {
            $docInput.removeClass('is-valid').addClass('is-invalid');
            if ($docFeedback.length) {
                $docFeedback.text('DNI debe ser exactamente 8 dígitos');
            }
            Swal.fire('Validación', 'El DNI debe tener exactamente 8 dígitos', 'error');
            return;
        }
        $docInput.removeClass('is-invalid').addClass('is-valid');
    } else if (tipoDocSel === 'RUC') {
        if (!rucOk) {
            $docInput.removeClass('is-valid').addClass('is-invalid');
            if ($docFeedback.length) {
                $docFeedback.text('RUC debe ser exactamente 11 dígitos');
            }
            Swal.fire('Validación', 'El RUC debe tener exactamente 11 dígitos', 'error');
            return;
        }
        $docInput.removeClass('is-invalid').addClass('is-valid');
    } else {
        // Si no hay tipo seleccionado, no bloquear el envío
        if ($docFeedback.length) {
            $docFeedback.text('');
        }
        $docInput.removeClass('is-invalid is-valid');
    }
    
    var formData = new FormData($("#mantenimiento_form")[0]);
    
    $.ajax({
        url: "../../controller/cliente.php?op=guardaryeditar",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            // Cerrar el modal
            $("#modalmantenimiento").modal('hide');

            // Recargar el combo de clientes
            $.post("../../controller/cliente.php?op=combo", function(data) {
                $('#cli_id').html(data);

                // Si es un nuevo cliente, seleccionarlo automáticamente
                if (response && response.cli_id) {
                    $('#cli_id').val(response.cli_id).trigger('change');
                }
            });

            // Mostrar mensaje de éxito
            Swal.fire({
                title: 'Correcto!',
                text: 'Cliente registrado correctamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        },
        error: function(xhr, status, error) {
            var msg = 'No se pudo guardar el cliente';
            if (xhr && xhr.responseText) {
                msg += '\n' + xhr.responseText.substring(0, 200);
            }
            Swal.fire({
                title: 'Error!',
                text: msg,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    });
});

$(document).on("click","#btnnuevo",function(e){
    e.preventDefault();
    
    // Limpiar el formulario
    $("#cli_id").val("");
    $("#cli_nom").val("");
    $("#cli_ape").val("");
    $("#cli_tipo_doc").val("");
    $("#cli_doc").val("");
    $("#cli_direcc").val("");
    
    // Cambiar el título del modal
    $('#lbltitulo').html('Nuevo Cliente');
    
    // Limpiar el formulario completo
    $("#mantenimiento_form")[0].reset();
    
    // Abrir el modal
    $("#modalmantenimiento").modal('show');
    
    // Actualizar el texto del botón después de abrir el modal
    setTimeout(function() {
        actualizarTextoBotonBuscar();
        aplicarRestriccionesDocumento();
    }, 200);
});

// Función para actualizar el texto del botón Buscar según el tipo de documento
function actualizarTextoBotonBuscar() {
    // Usar setTimeout para asegurar que el DOM esté actualizado
    setTimeout(function() {
        var $modal = $('#modalmantenimiento');
        var tipoDoc = $modal.find('#cli_tipo_doc').val();
        var boton = $modal.find('#btnBuscarDoc');
        
        if (tipoDoc === 'DNI') {
            boton.text('RENIEC');
        } else if (tipoDoc === 'RUC') {
            boton.text('SUNAT');
        } else {
            boton.text('Buscar');
        }
    }, 100);
}

// Evento cuando cambia el tipo de documento en el modal
$(document).on('change', '#modalmantenimiento #cli_tipo_doc', function() {
    actualizarTextoBotonBuscar();
    aplicarRestriccionesDocumento(true);
});

// Actualizar el texto cuando se abre el modal
$(document).on('shown.bs.modal', '#modalmantenimiento', function () {
    actualizarTextoBotonBuscar();
    aplicarRestriccionesDocumento();
});

// Evento click del botón buscar
$(document).on('click', '#btnBuscarDoc', function(e) {
    e.preventDefault();
    
    var $modal = $('#modalmantenimiento');
    var tipoDoc = $modal.find('#cli_tipo_doc').val();
    var numDoc = $modal.find('#cli_doc').val();
    
    if (!tipoDoc) {
        Swal.fire('Atención!', 'Por favor seleccione un tipo de documento', 'warning');
        return;
    }
    
    if (!numDoc) {
        Swal.fire('Atención!', 'Por favor ingrese un número de documento', 'warning');
        return;
    }
    
    // Búsqueda en RENIEC (DNI)
    if (tipoDoc === 'DNI') {
        var dni = (numDoc || '').replace(/\D/g, '');
        if (!/^\d{8}$/.test(dni)) {
            Swal.fire('Validación', 'El DNI debe tener exactamente 8 dígitos', 'error');
            return;
        }

        var $btn = $modal.find('#btnBuscarDoc');
        $btn.prop('disabled', true);

        Swal.fire({
            title: 'Consultando RENIEC…',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "../../controller/cliente.php?op=consultar_reniec",
            type: "GET",
            dataType: "json",
            data: { numero: dni },
            success: function(resp) {
                if (resp && resp.success) {
                    var nombres = resp.first_name || '';
                    var apellidos = [resp.first_last_name || '', resp.second_last_name || ''].filter(Boolean).join(' ').trim();
                    if (resp.document_number && resp.document_number !== dni) {
                        $modal.find('#cli_doc').val(resp.document_number);
                    }
                    $modal.find('#cli_nom').val(nombres);
                    $modal.find('#cli_ape').val(apellidos);

                    Swal.fire({
                        title: 'Datos encontrados',
                        text: resp.full_name ? resp.full_name : 'Se llenaron nombres y apellidos',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                } else {
                    var msg = (resp && resp.message) ? resp.message : 'No se encontraron datos en RENIEC';
                    Swal.fire({ title: 'Sin resultados', text: msg, icon: 'warning', confirmButtonText: 'Aceptar' });
                }
            },
            error: function(xhr) {
                var msg = 'Error al consultar RENIEC';
                try { var j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch(e) {}
                Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Aceptar' });
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
        return;
    }

    // Búsqueda en SUNAT (RUC) - pendiente de integración
    if (tipoDoc === 'RUC') {
        Swal.fire('Información', 'Integración con SUNAT pendiente', 'info');
        return;
    }

    Swal.fire('Información', 'Buscando…', 'info');
});

// Aplicar restricciones de longitud y formato según tipo de documento
function aplicarRestriccionesDocumento(clearValue) {
    var $modal = $('#modalmantenimiento');
    var tipoDoc = $modal.find('#cli_tipo_doc').val();
    var $docInput = $modal.find('#cli_doc');
    var $docFeedback = $modal.find('#cli_doc_feedback');
    
    if (clearValue) {
        $docInput.val('');
        $docInput.removeClass('is-valid is-invalid');
        if ($docFeedback.length) $docFeedback.text('');
    }
    
    if (tipoDoc === 'DNI') {
        $docInput.attr('maxlength', '8');
        $docInput.attr('pattern', '^\\d{8}$');
        $docInput.attr('placeholder', '8 dígitos');
    } else if (tipoDoc === 'RUC') {
        $docInput.attr('maxlength', '11');
        $docInput.attr('pattern', '^\\d{11}$');
        $docInput.attr('placeholder', '11 dígitos');
    } else {
        $docInput.removeAttr('maxlength');
        $docInput.removeAttr('pattern');
        $docInput.attr('placeholder', '');
    }
}

// Validación en tiempo real del número de documento
$(document).on('input', '#modalmantenimiento #cli_doc', function() {
    var $modal = $('#modalmantenimiento');
    var tipoDoc = $modal.find('#cli_tipo_doc').val();
    var $docInput = $(this);
    var $docFeedback = $modal.find('#cli_doc_feedback');
    
    // Forzar solo dígitos
    var digits = ($docInput.val() || '').replace(/\D/g, '');
    $docInput.val(digits);
    
    if (tipoDoc === 'DNI') {
        if (/^\d{8}$/.test(digits)) {
            $docInput.removeClass('is-invalid').addClass('is-valid');
            if ($docFeedback.length) $docFeedback.text('');
        } else {
            $docInput.removeClass('is-valid').addClass('is-invalid');
            if ($docFeedback.length) $docFeedback.text('DNI debe ser exactamente 8 dígitos');
        }
    } else if (tipoDoc === 'RUC') {
        if (/^\d{11}$/.test(digits)) {
            $docInput.removeClass('is-invalid').addClass('is-valid');
            if ($docFeedback.length) $docFeedback.text('');
        } else {
            $docInput.removeClass('is-valid').addClass('is-invalid');
            if ($docFeedback.length) $docFeedback.text('RUC debe ser exactamente 11 dígitos');
        }
    } else {
        // Sin tipo seleccionado, no marcar inválido y limpiar feedback
        $docInput.removeClass('is-valid is-invalid');
        if ($docFeedback.length) $docFeedback.text('');
    }
});

// Guardar recepción (inserción)
$(document).on('submit', '#recepcion_form', function(e) {
    e.preventDefault();

    var cliId = parseInt($('#cli_id').val() || '0', 10);
    var habId = parseInt($('#hab_id').val() || '0', 10);
    // Usar el total calculado (no el precio por noche)
    var precioInicialStr = ($('#total_pagar').val() || '').toString().replace(/[^\d.]/g, '');
    var adelantoStr = ($('#adelanto').val() || '').toString().replace(/[^\d.]/g, '');
    var observacion = ($('#observacion').val() || '').trim();

    if (!cliId || cliId <= 0) {
        Swal.fire('Validación', 'Seleccione un cliente', 'warning');
        return;
    }
    if (!habId || habId <= 0) {
        Swal.fire('Validación', 'No se identificó la habitación. Regrese desde el listado.', 'warning');
        return;
    }
    var precioNum = parseFloat(precioInicialStr || '0');
    if (!precioNum || precioNum <= 0) {
        Swal.fire('Validación', 'Ingrese un precio inicial válido', 'warning');
        return;
    }
    var adelantoNum = parseFloat(adelantoStr || '0');
    if (adelantoNum < 0) {
        Swal.fire('Validación', 'El adelanto no puede ser negativo', 'warning');
        return;
    }
    if (adelantoNum > precioNum) {
        Swal.fire('Validación', 'El adelanto no puede ser mayor al total a pagar', 'warning');
        return;
    }

    var fd = new FormData(document.getElementById('recepcion_form'));
    fd.set('cli_id', cliId);
    fd.set('hab_id', habId);
    fd.set('precio_inicial', precioNum);
    fd.set('adelanto', adelantoNum);
    fd.set('observacion', observacion);

    // Fecha de salida: si existe el campo, enviar en formato Y-m-d H:i
    var fechaSalidaVal = ($('#fecha_salida').val() || '').trim();
    if (fechaSalidaVal) {
        fd.set('fecha_salida', fechaSalidaVal);
    }

    $.ajax({
        url: "../../controller/recepcion.php?op=guardaryeditar",
        type: "POST",
        data: fd,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(resp) {
            if (resp && resp.success) {
                Swal.fire({ 
                    title: 'Correcto', 
                    text: 'Recepción registrada. Redirigiendo al Panel…', 
                    icon: 'success',
                    timer: 1600,
                    showConfirmButton: false
                });
                // Redirigir al panel de recepción (listado)
                setTimeout(function(){
                    window.location.href = '../ListRecepcion/';
                }, 1500);
            } else {
                var msg = (resp && resp.message) ? resp.message : 'No se pudo registrar';
                Swal.fire({ title: 'Atención', text: msg, icon: 'warning' });
            }
        },
        error: function(xhr) {
            var msg = 'Error al guardar recepción';
            if (xhr && xhr.responseText) { msg += '\n' + xhr.responseText.substring(0, 200); }
            Swal.fire({ title: 'Error', text: msg, icon: 'error' });
        }
    });
});

// --- Cálculo de totales (precio x noches) y fecha de salida ---
function updateTotals() {
    // Precio por noche
    var precioNoche = parseFloat(($('#precio_inicial').val() || '').toString().replace(/[^\d.]/g, ''));
    if (isNaN(precioNoche) || precioNoche <= 0) {
        precioNoche = parseFloat(($('#tar_id option:selected').data('precio') || '0').toString().replace(',', '.'));
    }
    // Modo recepción por 3 horas
    var threeHours = $('#recepcion_3h').is(':checked');
    var total = 0;
    var noches = 1;
    if (threeHours) {
        total = precioNoche; // precio fijo sin multiplicación
    } else {
        // Cantidad de noches (escopado al formulario y mínimo 1)
        var $nochesInput = $('#recepcion_form .product-quantity').first();
        var nochesRaw = $nochesInput.length ? $nochesInput.val() : $('.product-quantity').val();
        noches = parseInt(nochesRaw || '1', 10);
        if (isNaN(noches) || noches < 1) {
            noches = 1;
            if ($nochesInput.length) { $nochesInput.val('1'); } else { $('.product-quantity').val('1'); }
        }
        total = precioNoche * noches;
    }

    if (!isNaN(total)) {
        $('#total_pagar').val(total.toFixed(2));
    } else {
        $('#total_pagar').val('0.00');
    }

    // Actualizar fecha de salida = fecha de entrada + noches (días)
    var baseDate = (window.fpFechaEntrada && window.fpFechaEntrada.selectedDates && window.fpFechaEntrada.selectedDates[0])
        ? new Date(window.fpFechaEntrada.selectedDates[0])
        : new Date();
    var salidaDate = new Date(baseDate.getTime());
    if (threeHours) {
        // sumar 3 horas
        salidaDate.setHours(salidaDate.getHours() + 3);
    } else {
        // sumar noches como días
        salidaDate.setDate(salidaDate.getDate() + (isNaN(noches) ? 0 : noches));
    }

    // Ajustar con flatpickr si existe, sino setear valor texto
    if (window.fpFechaSalida && typeof window.fpFechaSalida.setDate === 'function') {
        window.fpFechaSalida.setDate(salidaDate, true); // trigger change
    } else {
        $('#fecha_salida').val(formatDateYmdHi(salidaDate));
    }

    // Validación de adelanto en tiempo real frente al total
    var adelantoNum = parseFloat(($('#adelanto').val() || '').toString().replace(/[^\d.]/g, ''));
    if (!isNaN(adelantoNum) && !isNaN(total) && adelantoNum > total) {
        $('#adelanto').val(total.toFixed(2));
        Swal.fire('Validación', 'El adelanto no puede ser mayor al total a pagar', 'warning');
    }
}

// Exponer nombre usado previamente en el código legado
function recalcularSalidaYPrecio() {
    updateTotals();
}

// Eventos para los botones + y - de cantidad de noches
$(document).on('click', '.input-step .plus', function() {
    // En modo 3 horas no se modifica noches
    if ($('#recepcion_3h').is(':checked')) { return; }
    var $qty = $(this).siblings('.product-quantity');
    var v = parseInt($qty.val() || '0', 10);
    var max = parseInt($qty.attr('max') || '100', 10);
    if (isNaN(v)) v = 0;
    if (isNaN(max)) max = 100;
    v = Math.min(v + 1, max);
    $qty.val(v);
    updateTotals();
});

$(document).on('click', '.input-step .minus', function() {
    // En modo 3 horas no se modifica noches
    if ($('#recepcion_3h').is(':checked')) { return; }
    var $qty = $(this).siblings('.product-quantity');
    var v = parseInt($qty.val() || '0', 10);
    var min = parseInt($qty.attr('min') || '0', 10);
    if (isNaN(v)) v = 0;
    if (isNaN(min)) min = 0;
    v = Math.max(v - 1, min);
    $qty.val(v);
    updateTotals();
});

// Validación adicional: al escribir adelanto, impedir exceder total
$(document).on('input', '#adelanto', function() {
    var total = parseFloat(($('#total_pagar').val() || '').toString().replace(/[^\d.]/g, ''));
    var val = parseFloat(($(this).val() || '').toString().replace(/[^\d.]/g, ''));
    if (isNaN(total)) total = 0;
    if (isNaN(val)) val = 0;
    if (val > total) {
        $(this).val(total.toFixed(2));
        Swal.fire('Validación', 'El adelanto no puede ser mayor al total a pagar', 'warning');
    }
});

// Normalizar botones +/− para asegurar incremento/decremento en pasos de 1 y evitar dobles handlers
function normalizeStepButtons() {
    try {
        // Forzar step=1 y mínimo 1
        var $qty = $('.product-quantity');
        $qty.attr('step', '1');
        $qty.attr('min', '1');

        // Quitar posibles manejadores inline y agregar uno en captura
        document.querySelectorAll('.input-step .plus, .input-step .minus').forEach(function(btn) {
            try { btn.onclick = null; } catch (e) {}

            btn.addEventListener('click', function(e) {
                // Interceptar para evitar ejecución de otros listeners que causan saltos
                e.preventDefault();
                e.stopPropagation();
                if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

                // En modo 3 horas no se modifica noches
                if ($('#recepcion_3h').is(':checked')) {
                    updateTotals();
                    return;
                }

                var qtyInput = this.parentElement.querySelector('.product-quantity');
                var v = parseInt((qtyInput && qtyInput.value) || '1', 10);
                if (isNaN(v) || v < 1) v = 1;

                if (this.classList.contains('plus')) {
                    v += 1;
                } else {
                    v = Math.max(1, v - 1);
                }

                if (qtyInput) {
                    qtyInput.value = String(v);
                    // Disparar cambio para recalcular totales y fecha
                    $(qtyInput).trigger('change');
                    // Fallback directo en caso algún handler externo bloquee el evento
                    updateTotals();
                }
            }, true); // useCapture=true
        });

        // Eliminar posibles delegados jQuery globales sobre .plus/.minus que dupliquen la acción
        $(document).off('click', '.plus');
        $(document).off('click', '.minus');
    } catch (e) {}
}

// Ejecutar normalización al cargar
document.addEventListener('DOMContentLoaded', normalizeStepButtons);

// UI: mostrar/ocultar bloque noches según modo 3 horas
function applyRecepcionModeUI() {
    var threeHours = $('#recepcion_3h').is(':checked');
    var $block = $('#noches_block');
    if ($block.length) {
        if (threeHours) {
            $block.addClass('d-none');
        } else {
            $block.removeClass('d-none');
        }
    }
}

// Enlazar checkbox y estado inicial
$(document).on('change', '#recepcion_3h', function() {
    applyRecepcionModeUI();
    updateTotals();
});

document.addEventListener('DOMContentLoaded', function() {
    applyRecepcionModeUI();
    updateTotals();
});

