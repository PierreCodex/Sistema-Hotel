/**
 * Vista de Habitaciones para Empleado
 * Solo lectura con acciones operativas (Reservar)
 */

var tabla;

$(document).ready(function(){
    cargarTabla();
    
    // Filtros por estado
    $(document).on('click', '.filtro-estado', function(){
        $('.filtro-estado').removeClass('active');
        $(this).addClass('active');
        
        var estado = $(this).data('estado');
        filtrarPorEstado(estado);
    });
    
    // Click en botón Reservar
    $(document).on('click', '.btn-reservar', function(){
        var habNum = $(this).data('numero');
        window.location.href = '../MntRecepcion/index.php?habitacion=' + encodeURIComponent(habNum);
    });
    
    // Click en botón Ver Tarifas
    $(document).on('click', '.btn-ver-tarifas', function(){
        var habId = $(this).data('id');
        var habNum = $(this).data('numero');
        mostrarTarifas(habId, habNum);
    });
});

function cargarTabla(){
    tabla = $('#table_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "ajax":{
            url: "../../controller/habitacion.php?op=listar_para_empleado",
            type: "post",
            dataSrc: function(json){
                return json.data || json;
            }
        },
        "columns": [
            { 
                "data": "HAB_NUM",
                "render": function(data, type, row){
                    return '<span class="fw-semibold fs-5">' + data + '</span>';
                }
            },
            { 
                "data": "CAT_NOM",
                "render": function(data, type, row){
                    var badgeClass = 'bg-secondary';
                    if(data && data.toUpperCase().includes('SUITE') || data && data.toUpperCase().includes('TEMÁTICA')){
                        badgeClass = 'bg-primary';
                    } else if(data && data.toUpperCase().includes('MATRIMONIAL')){
                        badgeClass = 'bg-info';
                    }
                    return '<span class="badge ' + badgeClass + '">' + (data || '-') + '</span>';
                }
            },
            { "data": "PISO_NOM" },
            { 
                "data": "HAB_DET",
                "render": function(data, type, row){
                    if(!data) return '-';
                    // Mostrar solo primeros 60 caracteres
                    var texto = data.length > 60 ? data.substr(0, 60) + '...' : data;
                    return '<small class="text-muted" title="' + escapeHtml(data) + '">' + escapeHtml(texto) + '</small>';
                }
            },
            { 
                "data": "HAB_ID",
                "orderable": false,
                "render": function(data, type, row){
                    return '<button type="button" class="btn btn-soft-info btn-sm btn-ver-tarifas" data-id="' + data + '" data-numero="' + row.HAB_NUM + '">' +
                           '<i class="ri-price-tag-3-line me-1"></i>Ver Tarifas</button>';
                }
            },
            { 
                "data": "ESTADO_NOM",
                "render": function(data, type, row){
                    var estado = (data || '').toUpperCase();
                    var badgeClass = 'bg-secondary';
                    var icon = 'ri-question-line';
                    
                    if(estado.includes('DISPONIBLE')){
                        badgeClass = 'bg-success';
                        icon = 'ri-checkbox-circle-line';
                    } else if(estado.includes('OCUPADO') || estado.includes('OCUPADA')){
                        badgeClass = 'bg-danger';
                        icon = 'ri-forbid-line';
                    } else if(estado.includes('LIMPIEZA')){
                        badgeClass = 'bg-warning';
                        icon = 'ri-brush-line';
                    } else if(estado.includes('MANTENIMIENTO')){
                        badgeClass = 'bg-dark';
                        icon = 'ri-tools-line';
                    }
                    
                    return '<span class="badge ' + badgeClass + '"><i class="' + icon + ' me-1"></i>' + data + '</span>';
                }
            },
            { 
                "data": null,
                "orderable": false,
                "className": "text-center",
                "render": function(data, type, row){
                    var estado = (row.ESTADO_NOM || '').toUpperCase();
                    
                    if(estado.includes('DISPONIBLE')){
                        return '<button type="button" class="btn btn-success btn-sm btn-reservar" data-id="' + row.HAB_ID + '" data-numero="' + row.HAB_NUM + '">' +
                               '<i class="ri-add-circle-line me-1"></i>Reservar</button>';
                    } else if(estado.includes('OCUPADO') || estado.includes('OCUPADA')){
                        return '<span class="text-danger"><i class="ri-time-line me-1"></i>En uso</span>';
                    } else if(estado.includes('LIMPIEZA')){
                        return '<span class="text-warning"><i class="ri-brush-line me-1"></i>Preparando</span>';
                    } else {
                        return '<span class="text-muted">No disponible</span>';
                    }
                }
            }
        ],
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 15,
        "order": [[ 0, "asc" ]],
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron habitaciones",
            "sEmptyTable":     "No hay habitaciones registradas",
            "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_ habitaciones",
            "sInfoEmpty":      "Sin habitaciones",
            "sInfoFiltered":   "(filtrado de _MAX_ total)",
            "sSearch":         "Buscar:",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });
}

function filtrarPorEstado(estado){
    if(estado === 'todos'){
        tabla.column(5).search('').draw();
    } else {
        tabla.column(5).search(estado, true, false).draw();
    }
}

function mostrarTarifas(habId, habNum){
    $.ajax({
        url: '../../controller/habitacion.php?op=listar_tarifas_asignadas',
        type: 'POST',
        data: { hab_id: habId },
        dataType: 'json',
        success: function(data){
            var html = '';
            
            if(data && data.length > 0){
                html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                html += '<thead class="table-light"><tr><th>Tipo de Tarifa</th><th class="text-end">Precio</th></tr></thead>';
                html += '<tbody>';
                
                data.forEach(function(tarifa){
                    html += '<tr>';
                    html += '<td><i class="ri-time-line me-1 text-primary"></i>' + (tarifa.Descripcion || tarifa.TipoTarifa || 'Tarifa') + '</td>';
                    html += '<td class="text-end fw-semibold text-success">S/ ' + parseFloat(tarifa.HAT_PRE || tarifa.Precio || 0).toFixed(2) + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
            } else {
                html = '<div class="alert alert-warning mb-0"><i class="ri-information-line me-2"></i>Esta habitación no tiene tarifas configuradas.</div>';
            }
            
            Swal.fire({
                title: '<i class="ri-price-tag-3-line me-2"></i>Tarifas - Habitación ' + habNum,
                html: html,
                width: 450,
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    title: 'fs-5'
                }
            });
        },
        error: function(){
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar las tarifas'
            });
        }
    });
}

function escapeHtml(text) {
    if(!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

