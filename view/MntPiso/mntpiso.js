function init() {
    $("#mantenimiento_form").on("submit", function (e) {
        guardaryeditar(e);
    });
}

function guardaryeditar(e) {
    e.preventDefault();

    // Validaciones del lado del cliente
    var piso_nom = $("#piso_nom").val().trim();

    if (piso_nom === "") {
        swal.fire({
            title: 'Error de Validación',
            text: 'El nombre del piso es obligatorio',
            icon: 'warning'
        });
        $("#piso_nom").focus();
        return false;
    }

    if (piso_nom.length > 50) {
        swal.fire({
            title: 'Error de Validación',
            text: 'El nombre del piso no puede exceder 50 caracteres',
            icon: 'warning'
        });
        $("#piso_nom").focus();
        return false;
    }

    // Mostrar indicador de carga
    swal.fire({
        title: 'Procesando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            swal.showLoading();
        }
    });

    var formData = new FormData($("#mantenimiento_form")[0]);
    $.ajax({
        url: "../../controller/piso.php?op=guardaryeditar",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            swal.close(); // Cerrar indicador de carga

            try {
                var response = JSON.parse(data);

                if (response.status === 'success') {
                    $('#table_data').DataTable().ajax.reload();
                    $('#modalmantenimiento').modal('hide');

                    swal.fire({
                        title: 'Éxito',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (response.status === 'error') {
                    swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error'
                    });
                }
            } catch (e) {
                // Si no es JSON válido, mostrar error
                swal.fire({
                    title: 'Error',
                    text: 'Respuesta del servidor no válida',
                    icon: 'error'
                });
            }
        },
        error: function (xhr, status, error) {
            swal.close(); // Cerrar indicador de carga
            swal.fire({
                title: 'Error de Conexión',
                text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                icon: 'error'
            });
        }
    });
}

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
            url: "../../controller/piso.php?op=listar",
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

function editar(piso_id) {
    $.post("../../controller/piso.php?op=mostrar", { piso_id: piso_id }, function (data) {
        data = JSON.parse(data);
        $('#piso_id').val(data.PISO_ID);
        $('#piso_nom').val(data.PISO_NOM);

        // Remover clases de validación
        $('#piso_nom').removeClass('is-invalid is-valid');

        // Validar el campo cargado
        if (data.PISO_NOM && data.PISO_NOM.trim().length > 0 && data.PISO_NOM.trim().length <= 50) {
            $('#piso_nom').addClass('is-valid');
        }
    });
    $('#lbltitulo').html('Editar Registro');
    $('#modalmantenimiento').modal('show');

    // Enfocar el campo nombre después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#piso_nom').focus().select();
    });
}




function eliminar(piso_id) {
    swal.fire({
        title: "Eliminar Piso",
        text: "¿Está seguro que desea eliminar este piso?",
        icon: "warning",
        confirmButtonText: "Sí, eliminar",
        confirmButtonColor: '#d33',
        showCancelButton: true,
        cancelButtonText: "Cancelar",
        cancelButtonColor: '#3085d6',
    }).then((result) => {
        if (result.value) {
            // Mostrar indicador de carga
            swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    swal.showLoading();
                }
            });

            $.post("../../controller/piso.php?op=eliminar", { piso_id: piso_id }, function (data) {
                swal.close(); // Cerrar indicador de carga

                try {
                    var response = JSON.parse(data);

                    if (response.status === 'success') {
                        $('#table_data').DataTable().ajax.reload();

                        swal.fire({
                            title: 'Éxito',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else if (response.status === 'error') {
                        swal.fire({
                            title: 'No se puede eliminar',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }
                } catch (e) {
                    // Si no es JSON válido, mostrar error
                    swal.fire({
                        title: 'Error',
                        text: 'Respuesta del servidor no válida',
                        icon: 'error'
                    });
                }
            }).fail(function () {
                swal.close();
                swal.fire({
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                    icon: 'error'
                });
            });
        }
    });
}


// Función para cambiar el estado del Piso via checkbox
function cambiarEstado(piso_id, estado) {
    var accion = estado ? 'activar' : 'desactivar';
    var titulo = estado ? 'Activar Piso' : 'Desactivar Piso';
    var texto = '¿Está seguro que desea ' + accion + ' este Piso?';

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
            $.post("../../controller/piso.php?op=cambiar_estado", {
                piso_id: piso_id,
                estado: estado
            }, function (data) {
                var response = JSON.parse(data);
                if (response.status === 'success') {
                    $('#table_data').DataTable().ajax.reload();
                    swal.fire({
                        title: 'Piso',
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
                // Revertir el checkbox en caso de error
                $('#switch' + piso_id).prop('checked', !estado);
            });
        } else {
            // Si el usuario cancela, revertir el checkbox
            $('#switch' + piso_id).prop('checked', !estado);
        }
    });
}

$(document).on("click", "#btnnuevo", function () {
    $('#piso_id').val('');
    $('#piso_nom').val('');
    $('#lbltitulo').html('Nuevo Registro');
    $("#mantenimiento_form")[0].reset();

    // Remover clases de validación
    $('#piso_nom').removeClass('is-invalid is-valid');

    $('#modalmantenimiento').modal('show');

    // Enfocar el campo nombre después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#piso_nom').focus();
    });
});

// Validación en tiempo real del campo nombre
$(document).on('input', '#piso_nom', function () {
    var valor = $(this).val().trim();
    var campo = $(this);

    // Remover clases previas
    campo.removeClass('is-invalid is-valid');

    if (valor.length === 0) {
        campo.addClass('is-invalid');
    } else if (valor.length > 50) {
        campo.addClass('is-invalid');
    } else {
        campo.addClass('is-valid');
    }
});



init();