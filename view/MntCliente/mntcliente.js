function init() {


}

// Función para listar todos los clientes
$(document).ready(function () {

    $('#table_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
        ],
        "ajax": {
            url: "../../controller/cliente.php?op=listar_todos", // CAMBIO: listar_todos en vez de listar
            type: "post"
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "order": [[0, "desc"]],
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
    });

});

// ==========================================
// VALIDACIONES DEL FORMULARIO DE CLIENTE
// ==========================================

// Evento para guardar el cliente desde el modal
$(document).on("submit", "#mantenimiento_form", function (e) {
    e.preventDefault();

    var $modal = $('#modalmantenimiento');
    var tipoDocSel = $modal.find('#cli_tipo_doc').val();
    var $docInput = $modal.find('#cli_doc');
    var docVal = ($docInput.val() || '').trim();
    var $docFeedback = $modal.find('#cli_doc_feedback');
    var cliId = $modal.find('#cli_id').val() || '';

    // Validar que se haya seleccionado un tipo de documento
    if (!tipoDocSel || tipoDocSel === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Tipo de documento requerido',
            text: 'Debe seleccionar un tipo de documento (DNI o RUC)',
            confirmButtonText: 'Entendido'
        });
        $modal.find('#cli_tipo_doc').focus();
        return;
    }

    // Normalizar a solo dígitos
    docVal = docVal.replace(/\D/g, '');
    $docInput.val(docVal);

    // Validar que se haya ingresado un número de documento
    if (!docVal || docVal === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Número de documento requerido',
            text: 'Debe ingresar el número de documento',
            confirmButtonText: 'Entendido'
        });
        $docInput.focus();
        return;
    }

    // Reglas de validación según tipo
    var dniOk = /^\d{8}$/.test(docVal);
    var rucOk = /^\d{11}$/.test(docVal);

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
    }

    // Validar nombre/apellido o razón social según tipo de documento
    var $cliNom = $modal.find('#cli_nom');
    var $cliApe = $modal.find('#cli_ape');
    var $cliRazonSocial = $modal.find('#cli_razon_social');

    if (tipoDocSel === 'DNI') {
        var nombre = ($cliNom.val() || '').trim();
        var apellido = ($cliApe.val() || '').trim();

        if (!nombre) {
            Swal.fire({
                icon: 'warning',
                title: 'Nombre requerido',
                text: 'Debe ingresar el nombre del cliente',
                confirmButtonText: 'Entendido'
            });
            $cliNom.focus();
            return;
        }

        if (!apellido) {
            Swal.fire({
                icon: 'warning',
                title: 'Apellido requerido',
                text: 'Debe ingresar el apellido del cliente',
                confirmButtonText: 'Entendido'
            });
            $cliApe.focus();
            return;
        }
    } else if (tipoDocSel === 'RUC') {
        var razonSocial = ($cliRazonSocial.val() || '').trim();

        if (!razonSocial) {
            Swal.fire({
                icon: 'warning',
                title: 'Razón Social requerida',
                text: 'Debe ingresar la razón social de la empresa',
                confirmButtonText: 'Entendido'
            });
            $cliRazonSocial.focus();
            return;
        }

        // Para RUC, copiar razón social a nombre y apellido para compatibilidad con BD
        $cliNom.val(razonSocial);
        $cliApe.val('-');
    }

    // Verificar si el documento ya existe antes de guardar
    $.ajax({
        url: "../../controller/cliente.php?op=verificar_documento",
        type: "POST",
        data: { cli_doc: docVal, cli_id: cliId },
        dataType: 'json',
        success: function (resp) {
            if (resp.success && resp.data && resp.data.existe) {
                var clienteExistente = resp.data.cliente;
                var nombreCompleto = (clienteExistente.Nombre || '') + ' ' + (clienteExistente.Apellido || '');

                Swal.fire({
                    icon: 'error',
                    title: 'Documento ya registrado',
                    html: '<p>El <strong>' + tipoDocSel + '</strong> <strong>' + docVal + '</strong> ya está registrado.</p>' +
                        '<p>Cliente: <strong>' + nombreCompleto.trim() + '</strong></p>' +
                        '<p>Por favor, seleccione el cliente existente o use otro documento.</p>',
                    confirmButtonText: 'Entendido'
                });

                $docInput.removeClass('is-valid').addClass('is-invalid');
                if ($docFeedback.length) {
                    $docFeedback.text('Este documento ya está registrado');
                }
                return;
            }

            // Si no existe duplicado, proceder a guardar
            guardarClienteForm($modal);
        },
        error: function () {
            // En caso de error en la verificación, intentar guardar
            guardarClienteForm($modal);
        }
    });
});

// Función para guardar el cliente
function guardarClienteForm($modal) {
    var formData = new FormData(document.getElementById("mantenimiento_form"));

    $.ajax({
        url: "../../controller/cliente.php?op=guardaryeditar",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
            // Cerrar el modal
            $modal.modal('hide');

            // Recargar combo de clientes si existe (en recepción)
            if ($('#cli_id').length) {
                $.post("../../controller/cliente.php?op=combo", function (data) {
                    $('#cli_id').html(data);
                    if (response && response.cli_id) {
                        $('#cli_id').val(response.cli_id).trigger('change');
                    }
                });
            }

            // Recargar tabla si existe (en mantenimiento de clientes)
            if ($('#table_data').length && $.fn.DataTable.isDataTable('#table_data')) {
                $('#table_data').DataTable().ajax.reload();
            }

            Swal.fire({
                title: 'Correcto!',
                text: response.cli_id ? 'Cliente registrado correctamente' : 'Cliente actualizado correctamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        },
        error: function (xhr) {
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
}

// Validación en tiempo real del documento (al salir del campo)
$(document).on('blur', '#modalmantenimiento #cli_doc', function () {
    var $modal = $('#modalmantenimiento');
    var tipoDocSel = $modal.find('#cli_tipo_doc').val();
    var $docInput = $(this);
    var docVal = ($docInput.val() || '').trim().replace(/\D/g, '');
    var $docFeedback = $modal.find('#cli_doc_feedback');
    var cliId = $modal.find('#cli_id').val() || '';

    // Validar longitud según tipo
    if (tipoDocSel === 'DNI' && docVal.length !== 8) return;
    if (tipoDocSel === 'RUC' && docVal.length !== 11) return;
    if (!docVal) return;

    // Verificar si el documento ya existe
    $.ajax({
        url: "../../controller/cliente.php?op=verificar_documento",
        type: "POST",
        data: { cli_doc: docVal, cli_id: cliId },
        dataType: 'json',
        success: function (resp) {
            if (resp.success && resp.data && resp.data.existe) {
                var clienteExistente = resp.data.cliente;
                var nombreCompleto = (clienteExistente.Nombre || '') + ' ' + (clienteExistente.Apellido || '');

                $docInput.removeClass('is-valid').addClass('is-invalid');
                if ($docFeedback.length) {
                    $docFeedback.text('Ya registrado: ' + nombreCompleto.trim());
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Cliente ya existe',
                    html: '<p>El <strong>' + tipoDocSel + '</strong> <strong>' + docVal + '</strong> ya está registrado.</p>' +
                        '<p>Cliente: <strong>' + nombreCompleto.trim() + '</strong></p>' +
                        '<p>Puede seleccionar este cliente desde el combo o usar otro documento.</p>',
                    confirmButtonText: 'Entendido'
                });
            } else {
                $docInput.removeClass('is-invalid').addClass('is-valid');
                if ($docFeedback.length) {
                    $docFeedback.text('');
                }
            }
        }
    });
});

// Validación en tiempo real del número de documento (al escribir)
$(document).on('input', '#modalmantenimiento #cli_doc', function () {
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
        $docInput.removeClass('is-valid is-invalid');
        if ($docFeedback.length) $docFeedback.text('');
    }
});

// Actualizar texto del botón y restricciones al cambiar tipo de documento
$(document).on('change', '#modalmantenimiento #cli_tipo_doc', function () {
    actualizarTextoBotonBuscar();
    aplicarRestriccionesDocumento(true);
    alternarCamposSegunTipoDoc();
});

// Función para actualizar el texto del botón Buscar según el tipo de documento
function actualizarTextoBotonBuscar() {
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
}

// Alternar campos Nombre/Apellido vs Razón Social según tipo de documento
function alternarCamposSegunTipoDoc() {
    var $modal = $('#modalmantenimiento');
    var tipoDoc = $modal.find('#cli_tipo_doc').val();

    if (tipoDoc === 'RUC') {
        $modal.find('#row_nombre_apellido').hide();
        $modal.find('#row_razon_social').show();
        $modal.find('#cli_nom').val('');
        $modal.find('#cli_ape').val('');
    } else {
        $modal.find('#row_nombre_apellido').show();
        $modal.find('#row_razon_social').hide();
        $modal.find('#cli_razon_social').val('');
    }
}

// Aplicar restricciones de longitud según tipo de documento
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
        $docInput.attr('placeholder', '8 dígitos');
    } else if (tipoDoc === 'RUC') {
        $docInput.attr('maxlength', '11');
        $docInput.attr('placeholder', '11 dígitos');
    } else {
        $docInput.removeAttr('maxlength');
        $docInput.attr('placeholder', '');
    }
}

// Actualizar cuando se abre el modal
$(document).on('shown.bs.modal', '#modalmantenimiento', function () {
    actualizarTextoBotonBuscar();
    aplicarRestriccionesDocumento();
    alternarCamposSegunTipoDoc();
});

// Evento click del botón buscar (RENIEC/SUNAT)
$(document).on('click', '#btnBuscarDoc', function (e) {
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
            success: function (resp) {
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
            error: function (xhr) {
                var msg = 'Error al consultar RENIEC';
                try { var j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch (e) { }
                Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Aceptar' });
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
        return;
    }

    // Búsqueda en SUNAT (RUC)
    if (tipoDoc === 'RUC') {
        var ruc = (numDoc || '').replace(/\D/g, '');
        if (!/^\d{11}$/.test(ruc)) {
            Swal.fire('Validación', 'El RUC debe tener exactamente 11 dígitos', 'error');
            return;
        }

        var $btn = $modal.find('#btnBuscarDoc');
        $btn.prop('disabled', true);

        Swal.fire({
            title: 'Consultando SUNAT…',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "../../controller/cliente.php?op=consultar_ruc",
            type: "GET",
            dataType: "json",
            data: { numero: ruc },
            success: function (resp) {
                if (resp && resp.success) {
                    $modal.find('#cli_razon_social').val(resp.razon_social || '');
                    $modal.find('#cli_nom').val(resp.razon_social || '');
                    $modal.find('#cli_ape').val('');
                    $modal.find('#cli_direcc').val(resp.direccion || '');

                    var estadoInfo = '';
                    if (resp.estado) estadoInfo += 'Estado: ' + resp.estado;
                    if (resp.condicion) estadoInfo += ' | Condición: ' + resp.condicion;

                    Swal.fire({
                        title: 'Datos encontrados',
                        html: '<strong>' + (resp.razon_social || '') + '</strong><br>' +
                            '<small>' + estadoInfo + '</small><br>' +
                            '<small>' + (resp.direccion || '') + '</small>',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                } else {
                    var msg = (resp && resp.message) ? resp.message : 'No se encontraron datos en SUNAT';
                    Swal.fire({ title: 'Sin resultados', text: msg, icon: 'warning', confirmButtonText: 'Aceptar' });
                }
            },
            error: function (xhr) {
                var msg = 'Error al consultar SUNAT';
                try { var j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch (e) { }
                Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Aceptar' });
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
        return;
    }
});

// ==========================================
// FUNCIONES NUEVAS PARA MANTENIMIENTO (US050 y US051)
// ==========================================

// Función para ver detalles del cliente
function verDetalles(cli_id) {
    $.ajax({
        url: "../../controller/cliente.php?op=obtener_detalles",
        type: "POST",
        data: { cli_id: cli_id },
        dataType: 'json',
        success: function (data) {
            if (!data || data.error) {
                Swal.fire('Error', data.error || 'No se pudo obtener los detalles', 'error');
                return;
            }

            // Información Personal
            var tipoDoc = data.TipoDocumento === 'DNI' ? 'DNI' : 'RUC';
            var nombreCompleto = (data.Nombre || '') + ' ' + (data.Apellido || '');
            $('#det_nombre_completo').text(nombreCompleto.trim() || '-');
            $('#det_tipo_doc').text(tipoDoc);
            $('#det_documento').text(data.Documento || '-');
            $('#det_nombre').text(data.Nombre || '-');
            $('#det_apellido').text(data.Apellido || '-');
            $('#det_direccion').text(data.Direccion || 'No registrada');

            // Estado
            var estadoActivo = data.Estado == 1;
            if (estadoActivo) {
                $('#det_estado_badge').removeClass('bg-danger').addClass('bg-success').text('ACTIVO');
            } else {
                $('#det_estado_badge').removeClass('bg-success').addClass('bg-danger').text('INACTIVO');
            }

            // Estadísticas
            $('#det_total_visitas').text(data.TotalVisitas || '0');
            $('#det_ultima_visita').text(data.UltimaVisita ? formatearFecha(data.UltimaVisita) : 'Sin visitas');

            // Auditoría
            $('#det_fecha_creacion').text(data.FechaCreacion ? formatearFechaHora(data.FechaCreacion) : 'No registrada');
            $('#det_usuario_creacion').text(data.UsuarioCreacion || 'Sistema');
            $('#det_fecha_modificacion').text(data.FechaModificacion ? formatearFechaHora(data.FechaModificacion) : 'Sin modificaciones');
            $('#det_usuario_modificacion').text(data.UsuarioModificacion || 'N/A');

            // Abrir modal
            $('#modalDetalles').modal('show');
        },
        error: function (xhr) {
            Swal.fire('Error', 'No se pudo cargar los detalles del cliente', 'error');
        }
    });
}

// Función para formatear fecha
function formatearFecha(fechaStr) {
    if (!fechaStr) return '-';
    var fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Función para formatear fecha con hora
function formatearFechaHora(fechaStr) {
    if (!fechaStr) return '-';
    var fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Función para editar cliente (US050)
function editar(cli_id) {
    $.post("../../controller/cliente.php?op=mostrar", { cli_id: cli_id }, function (data) {
        data = JSON.parse(data);

        // Cargar datos en el modal
        $('#cli_id').val(data.CLI_ID);
        $('#cli_tipo_doc').val(data.CLI_TIPO_DOC);
        $('#cli_doc').val(data.CLI_DOC);
        $('#cli_direcc').val(data.CLI_DIR);

        // Si es RUC, cargar la razón social desde el campo Nombre
        if (data.CLI_TIPO_DOC === 'RUC') {
            $('#cli_razon_social').val(data.CLI_NOM);
            $('#cli_nom').val(data.CLI_NOM); // Mantener para compatibilidad
            $('#cli_ape').val(data.CLI_APE);
        } else {
            // Si es DNI, cargar nombre y apellido normalmente
            $('#cli_nom').val(data.CLI_NOM);
            $('#cli_ape').val(data.CLI_APE);
        }

        // Actualizar campos según tipo de documento
        alternarCamposSegunTipoDoc();
        aplicarRestriccionesDocumento(false);

        // Validar campos cargados
        $('#cli_doc').removeClass('is-invalid').addClass('is-valid');
        if (data.CLI_NOM && data.CLI_NOM.trim().length > 0) {
            if (data.CLI_TIPO_DOC === 'RUC') {
                $('#cli_razon_social').removeClass('is-invalid').addClass('is-valid');
            } else {
                $('#cli_nom').removeClass('is-invalid').addClass('is-valid');
            }
        }
        if (data.CLI_APE && data.CLI_APE.trim().length > 0 && data.CLI_TIPO_DOC !== 'RUC') {
            $('#cli_ape').removeClass('is-invalid').addClass('is-valid');
        }
    });

    $('#lbltitulo').html('Editar Cliente');
    $('#modalmantenimiento').modal('show');

    // Enfocar el campo correcto según tipo de documento
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        var tipoDoc = $('#cli_tipo_doc').val();
        if (tipoDoc === 'RUC') {
            $('#cli_razon_social').focus().select();
        } else {
            $('#cli_nom').focus().select();
        }
    });
}

// Función para cambiar estado (US051)
function cambiarEstado(cli_id, estado) {
    // Si está intentando DESACTIVAR (estado = false)
    if (!estado) {
        // Primero verificar si tiene recepciones activas
        $.post("../../controller/cliente.php?op=verificar_recepciones_activas", {
            cli_id: cli_id
        }, function (data) {
            var response = JSON.parse(data);

            if (response.tiene_recepciones_activas) {
                // NO PERMITIR desactivar
                swal.fire({
                    title: 'No se puede desactivar',
                    html: '<p>El cliente tiene <strong>' + response.cantidad + ' recepción(es) activa(s)</strong>.</p>' +
                        '<p>Debe finalizar todas las recepciones antes de desactivar el cliente.</p>',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });

                // Revertir el switch
                $('#switch' + cli_id).prop('checked', true);
                return;
            }

            // Si NO tiene recepciones activas, proceder a desactivar
            confirmarCambioEstado(cli_id, estado);
        }).fail(function () {
            swal.fire({
                title: 'Error',
                text: 'No se pudo verificar las recepciones del cliente',
                icon: 'error'
            });
            $('#switch' + cli_id).prop('checked', true);
        });
    } else {
        // Si está ACTIVANDO, no hay problema
        confirmarCambioEstado(cli_id, estado);
    }
}

// Función auxiliar para confirmar el cambio de estado
function confirmarCambioEstado(cli_id, estado) {
    var accion = estado ? 'activar' : 'desactivar';
    var titulo = estado ? 'Activar Cliente' : 'Desactivar Cliente';
    var texto = '¿Está seguro que desea ' + accion + ' este cliente?';

    swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../../controller/cliente.php?op=cambiar_estado", {
                cli_id: cli_id,
                estado: estado
            }, function (data) {
                var response = JSON.parse(data);
                if (response.status === 'success') {
                    $('#table_data').DataTable().ajax.reload();
                    swal.fire({
                        title: 'Cliente',
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            }).fail(function () {
                swal.fire({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado',
                    icon: 'error'
                });
                $('#switch' + cli_id).prop('checked', !estado);
            });
        } else {
            $('#switch' + cli_id).prop('checked', !estado);
        }
    });
}

// Botón nuevo cliente
$(document).on("click", "#btnnuevo", function () {
    $('#cli_id').val('');
    $('#cli_tipo_doc').val('');
    $('#cli_doc').val('');
    $('#cli_nom').val('');
    $('#cli_ape').val('');
    $('#cli_direcc').val('');
    $('#cli_razon_social').val('');
    $('#lbltitulo').html('Nuevo Cliente');
    $("#mantenimiento_form")[0].reset();

    // Remover clases de validación
    $('#cli_doc').removeClass('is-invalid is-valid');
    $('#cli_nom').removeClass('is-invalid is-valid');
    $('#cli_ape').removeClass('is-invalid is-valid');

    $('#modalmantenimiento').modal('show');

    // Enfocar el campo tipo documento
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#cli_tipo_doc').focus();
    });
});

// Inicializar cuando el documento esté listo
$(document).ready(function () {
    init();
});