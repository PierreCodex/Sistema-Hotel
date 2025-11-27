


/**
 * Vista de Productos para Empleado
 * Solo lectura - muestra productos disponibles para venta
 */

var tabla;

$(document).ready(function(){
    cargarTabla();
    
    // Filtros por stock
    $(document).on('click', '.filtro-stock', function(){
        $('.filtro-stock').removeClass('active');
        $(this).addClass('active');
        
        var filtro = $(this).data('filtro');
        filtrarPorStock(filtro);
    });
});

function cargarTabla(){
    tabla = $('#table_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "ajax":{
            url: "../../controller/producto.php?op=listar_para_empleado",
            type: "post",
            dataSrc: function(json){
                return json.data || json;
            }
        },
        "columns": [
            { 
                "data": "PRO_NOM",
                "render": function(data, type, row){
                    return '<span class="fw-semibold">' + escapeHtml(data) + '</span>';
                }
            },
            { 
                "data": "PRO_DET",
                "render": function(data, type, row){
                    if(!data) return '-';
                    var texto = data.length > 50 ? data.substr(0, 50) + '...' : data;
                    return '<small class="text-muted" title="' + escapeHtml(data) + '">' + escapeHtml(texto) + '</small>';
                }
            },
            { 
                "data": "PRO_PRE",
                "className": "text-end",
                "render": function(data, type, row){
                    return '<span class="text-dark fw-semibold">S/ ' + parseFloat(data || 0).toFixed(2) + '</span>';
                }
            },
            { 
                "data": "PRO_CANT",
                "className": "text-center",
                "render": function(data, type, row){
                    var stock = parseInt(data) || 0;
                    var badgeClass = 'bg-success';
                    
                    if(stock <= 0){
                        badgeClass = 'bg-danger';
                    } else if(stock <= 5){
                        badgeClass = 'bg-warning';
                    }
                    
                    return '<span class="badge ' + badgeClass + '">' + stock + '</span>';
                }
            },
            { 
                "data": "PRO_CANT",
                "className": "text-center",
                "render": function(data, type, row){
                    var stock = parseInt(data) || 0;
                    
                    if(stock > 5){
                        return '<span class="text-success"><i class="ri-checkbox-circle-fill me-1"></i>Disponible</span>';
                    } else if(stock > 0){
                        return '<span class="text-warning"><i class="ri-error-warning-fill me-1"></i>Pocas unidades</span>';
                    } else {
                        return '<span class="text-danger"><i class="ri-close-circle-fill me-1"></i>Agotado</span>';
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
            "sZeroRecords":    "No se encontraron productos",
            "sEmptyTable":     "No hay productos registrados",
            "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_ productos",
            "sInfoEmpty":      "Sin productos",
            "sInfoFiltered":   "(filtrado de _MAX_ total)",
            "sSearch":         "Buscar producto:",
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

function filtrarPorStock(filtro){
    if(filtro === 'todos'){
        tabla.column(4).search('').draw();
    } else if(filtro === 'disponible'){
        tabla.column(4).search('Disponible|Pocas', true, false).draw();
    } else if(filtro === 'agotado'){
        tabla.column(4).search('Agotado', true, false).draw();
    }
}

function escapeHtml(text) {
    if(!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
