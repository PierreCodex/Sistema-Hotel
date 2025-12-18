$(document).ready(function () {
    // Cargar tabla de recepciones finalizadas del usuario
    cargarTablaRecepciones();
});

// Cargar tabla de recepciones finalizadas
function cargarTablaRecepciones() {
    $('#table_recepciones').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            url: "../../controller/recepcion.php?op=listar_recepciones_usuario_datatable",
            type: "post",
            dataSrc: function (json) {
                return json.aaData || [];
            }
        },
        "bDestroy": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "order": [[0, "desc"]],
        "scrollX": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No tiene recepciones finalizadas",
            "sEmptyTable": "No hay recepciones registradas",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });
}

// Ver productos vendidos en una recepción
function verProductos(rec_id, hab_num, cliente, fecha_salida) {
    // Actualizar información de la recepción en el modal
    $('#modal_habitacion').text('Habitación ' + hab_num);
    $('#modal_cliente').text(cliente);
    $('#modal_fecha_salida').text(fecha_salida);

    // Cargar productos vendidos
    $.post("../../controller/venta.php?op=listar_productos_por_recepcion", { rec_id: rec_id }, function (data) {

        try {
            // Intentar parsear si viene como string
            var response = (typeof data === 'string') ? JSON.parse(data) : data;

            if (response.error) {
                swal.fire('Error', response.error, 'error');
                return;
            }

            // Productos
            if (response.productos && response.productos.length > 0) {
                var html = '';
                var total = 0;

                response.productos.forEach(function (item) {
                    var subtotal = parseFloat(item.subtotal);
                    total += subtotal;

                    // Badge de estado de la venta
                    var estadoBadge = '';
                    switch (item.estado_venta) {
                        case 'ACTIVO':
                            estadoBadge = '<span class="badge bg-success">ACTIVO</span>';
                            break;
                        case 'PENDIENTE':
                            estadoBadge = '<span class="badge bg-warning">PENDIENTE</span>';
                            break;
                        case 'BORRADOR':
                            estadoBadge = '<span class="badge bg-secondary">BORRADOR</span>';
                            break;
                        case 'ANULADO':
                            estadoBadge = '<span class="badge bg-danger">ANULADO</span>';
                            break;
                        default:
                            estadoBadge = '<span class="badge bg-info">' + item.estado_venta + '</span>';
                    }

                    html += '<tr>';
                    html += '<td>' + item.producto + '</td>';
                    html += '<td class="text-center">' + item.cantidad + '</td>';
                    html += '<td class="text-end">S/ ' + parseFloat(item.precio_unitario).toFixed(2) + '</td>';
                    html += '<td class="text-end">S/ ' + subtotal.toFixed(2) + '</td>';
                    html += '<td class="text-center">' + estadoBadge + '</td>';
                    html += '</tr>';
                });

                $('#modal_productos_tbody').html(html);
                $('#modal_total').text('S/ ' + total.toFixed(2));
            } else {
                $('#modal_productos_tbody').html('<tr><td colspan="5" class="text-center">No hay productos vendidos</td></tr>');
                $('#modal_total').text('S/ 0.00');
            }

            // Mostrar modal
            $('#modalProductosVendidos').modal('show');

        } catch (e) {
            console.error("Error al parsear respuesta:", e);
            console.error("Data recibida:", data);
            swal.fire('Error', 'No se pudo cargar la información de productos. Revisa la consola para más detalles.', 'error');
        }
    }).fail(function (xhr, status, error) {
        console.error("Error AJAX:", status, error);
        console.error("Response:", xhr.responseText);
        swal.fire('Error', 'No se pudo cargar la información de productos. Error: ' + error, 'error');
    });
}
