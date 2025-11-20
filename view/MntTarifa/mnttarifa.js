function init(){
    $("#mantenimiento_form").on("submit",function(e){
        guardaryeditar(e);
    });

    // Cargar combos al inicializar
    combo_tarifa();
}

function guardaryeditar(e){
    e.preventDefault();
    
    // Validaciones del lado del cliente
    var tar_desc = $("#tar_desc").val().trim();
    var tar_precio_val = $("#tar_precio").val();
    var tar_precio = tar_precio_val !== '' ? parseFloat(tar_precio_val) : NaN;
    
    if(tar_desc === ""){ 
        swal.fire({
            title:'Error de Validación',
            text: 'La descripción de la tarifa es obligatoria',
            icon: 'warning'
        });
        $("#tar_desc").focus();
        return false;
    }
    
    if(tar_desc.length > 100){
        swal.fire({
            title:'Error de Validación',
            text: 'La descripción no puede exceder 100 caracteres',     
            icon: 'warning'
        });
        $("#tar_desc").focus();      
        return false;
    }

    if(isNaN(tar_precio)){
        swal.fire({
            title:'Error de Validación',
            text: 'El precio es obligatorio y debe ser numérico',
            icon: 'warning'
        });
        $("#tar_precio").focus();
        return false;
    }

    if(tar_precio < 0){
        swal.fire({
            title:'Error de Validación',
            text: 'El precio no puede ser negativo',
            icon: 'warning'
        });
        $("#tar_precio").focus();
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
        url:"../../controller/tarifa.php?op=guardaryeditar",
        type:"POST",
        data:formData,
        contentType:false,
        processData:false,
        success:function(data){
            swal.close(); // Cerrar indicador de carga
            
            try {
                var response = JSON.parse(data);
                
                if(response.status === 'success'){
                    $('#table_data').DataTable().ajax.reload();
                    $('#modalmantenimiento').modal('hide');
                    
                    swal.fire({
                        title:'Éxito',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if(response.status === 'error'){
                    swal.fire({
                        title:'Error',
                        text: response.message,
                        icon: 'error'
                    });
                }
            } catch(e) {
                // Si no es JSON válido, mostrar error
                swal.fire({
                    title:'Error',
                    text: 'Respuesta del servidor no válida',
                    icon: 'error'
                });
            }
        },
        error:function(xhr, status, error){
            swal.close(); // Cerrar indicador de carga
            swal.fire({
                title:'Error de Conexión',
                text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                icon: 'error'
            });
        }
    });
}

$(document).ready(function(){

    $('#table_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
        ],
        "ajax":{
            url:"../../controller/tarifa.php?op=listar",
            type:"post"
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo":true,
        "iDisplayLength": 10,
        "order": [[ 0, "desc" ]],
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
    });

});

function editar(tar_id){
    $.post("../../controller/tarifa.php?op=mostrar", {tar_id : tar_id}, function (data) {
        data = JSON.parse(data);
        $('#tar_id').val(data.TAR_ID);
        $('#tar_desc').val(data.TAR_DESC);
        $('#tar_precio').val(data.TAR_PRECIO);
        
        // Remover clases de validación
        $('#tar_desc').removeClass('is-invalid is-valid');
        $('#tar_precio').removeClass('is-invalid is-valid');
        
        // Validación rápida sobre datos cargados
        var d = (data.TAR_DESC || '').trim();
        if(d.length > 0 && d.length <= 100){
            $('#tar_desc').addClass('is-valid');
        }
        var p = parseFloat(data.TAR_PRECIO);
        if(!isNaN(p) && p >= 0){
            $('#tar_precio').addClass('is-valid');
        }
    });
    $('#lbltitulo').html('Editar Registro');
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo descripción después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#tar_desc').focus().select();
    });
}





function eliminar(tar_id){
    swal.fire({
        title:"Eliminar!",
        text:"¿Desea eliminar la tarifa?",
        icon: "error",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/tarifa.php?op=eliminar",{tar_id:tar_id},function(data){
                try{
                    var response = JSON.parse(data);
                    if(response.status === 'success'){
                        $('#table_data').DataTable().ajax.reload();
                        swal.fire({
                            title:'Tarifa',
                            text: response.message || 'Registro Eliminado',
                            icon: 'success'
                        });
                    } else {
                        swal.fire({
                            title:'Error',
                            text: response.message || 'No se pudo eliminar',
                            icon: 'error'
                        });
                    }
                } catch(e){
                    swal.fire({
                        title:'Error',
                        text:'Respuesta del servidor no válida',
                        icon:'error'
                    });
                }
            });
        }
    });
}

// Función para cambiar el estado de la Tarifa via checkbox
function cambiarEstado(tar_id, estado) {
    var accion = estado ? 'activar' : 'desactivar';
    var titulo = estado ? 'Activar Tarifa' : 'Desactivar Tarifa';
    var texto = '¿Está seguro que desea ' + accion + ' esta Tarifa?';
    
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
            $.post("../../controller/tarifa.php?op=cambiar_estado", {
                tar_id: tar_id,
                estado: estado
            }, function(data) {
                var response = JSON.parse(data);
                if(response.status === 'success') {
                    $('#table_data').DataTable().ajax.reload();
                    swal.fire({
                        title: 'Tarifa',         
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            }).fail(function() {
                swal.fire({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado',
                    icon: 'error'
                });
                // Revertir el checkbox en caso de error
                $('#switch' + tar_id).prop('checked', !estado);
            });
        } else {
            // Si el usuario cancela, revertir el checkbox
            $('#switch' + tar_id).prop('checked', !estado);
        }
    });
}

$(document).on("click","#btnnuevo",function(){
    $('#tar_id').val('');
    $('#tar_desc').val('');
    $('#tar_precio').val('');
    $('#lbltitulo').html('Nuevo Registro');
    $("#mantenimiento_form")[0].reset();
    
    // Remover clases de validación
    $('#tar_desc').removeClass('is-invalid is-valid');
    $('#tar_precio').removeClass('is-invalid is-valid');
    
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo nombre después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#tar_desc').focus();
    });
});

// Validación en tiempo real del campo nombre
$(document).on('input', '#tar_desc', function(){        
    var valor = $(this).val().trim();
    var campo = $(this);
    
    // Remover clases previas
    campo.removeClass('is-invalid is-valid');
    
    if(valor.length === 0){
        campo.addClass('is-invalid');
    } else if(valor.length > 100){
        campo.addClass('is-invalid');
    } else {
        campo.addClass('is-valid');
    }
});

// Validación en tiempo real del campo precio
$(document).on('input', '#tar_precio', function(){
    var valor = $(this).val();
    var campo = $(this);
    var num = valor !== '' ? parseFloat(valor) : NaN;

    campo.removeClass('is-invalid is-valid');

    if(isNaN(num)){
        campo.addClass('is-invalid');
    } else if(num < 0){
        campo.addClass('is-invalid');
    } else {
        campo.addClass('is-valid');
    }
});



init();