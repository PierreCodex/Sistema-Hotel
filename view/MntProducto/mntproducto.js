


function init(){
    $("#mantenimiento_form").on("submit",function(e){
        guardaryeditar(e);
    });
}

function guardaryeditar(e){
    e.preventDefault();
    
    // Validaciones del lado del cliente
    var prod_nom = $("#pro_nom").val().trim();
    
    if(prod_nom === ""){
        Swal.fire({
            title:'Error de Validación',
            text: 'El nombre del producto es obligatorio',
            icon: 'warning'
        });
        $("#pro_nom").focus();
        return false;
    }
    
    if(prod_nom.length > 50){       
        Swal.fire({
            title:'Error de Validación',
            text: 'El nombre del producto no puede exceder 50 caracteres',
            icon: 'warning'
        });
        $("#pro_nom").focus();     
        return false;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Procesando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    var formData = new FormData($("#mantenimiento_form")[0]);
    $.ajax({
        url:"../../controller/producto.php?op=guardaryeditar",
        type:"POST",
        data:formData,
        contentType:false,
        processData:false,
        success:function(data){
            Swal.close(); // Cerrar indicador de carga
            
            try {
                var response = JSON.parse(data);
                
                if(response.status === 'success'){
                    $('#table_data').DataTable().ajax.reload();
                    $('#modalmantenimiento').modal('hide');
                    
                    Swal.fire({
                        title:'Éxito',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if(response.status === 'error'){
                    Swal.fire({
                        title:'Error',
                        text: response.message,
                        icon: 'error'
                    });
                }
            } catch(e) {
                // Si no es JSON válido, mostrar error
                Swal.fire({
                    title:'Error',
                    text: 'Respuesta del servidor no válida',
                    icon: 'error'
                });
            }
        },
        error:function(xhr, status, error){
            Swal.close(); // Cerrar indicador de carga
            Swal.fire({
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
            url:"../../controller/producto.php?op=listar",
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



function editar(pro_id){
    $.post("../../controller/producto.php?op=mostrar", {pro_id : pro_id}, function (data) {
        data = JSON.parse(data);
        $('#pro_id').val(data.PRO_ID);
        $('#pro_nom').val(data.PRO_NOM);
        $('#pro_det').val(data.PRO_DET);
        $('#pro_pre').val(data.PRO_PRE);
        $('#pro_cant').val(data.PRO_CANT);
        
        // Remover clases de validación
        $('#pro_nom').removeClass('is-invalid is-valid');
        
        // Validar el campo cargado
        if(data.PRO_NOM && data.PRO_NOM.trim().length > 0 && data.PRO_NOM.trim().length <= 50){
            $('#pro_nom').addClass('is-valid');
        }
    });
    $('#lbltitulo').html('Editar Registro');
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo nombre después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#pro_nom').focus().select();     
    });
}
function eliminar(pro_id){
    swal.fire({
        title:"Eliminar!",
        text:"Desea Eliminar el Registro?",
        icon: "error",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/producto.php?op=eliminar",{pro_id:pro_id},function(data){
            });

            $('#table_data').DataTable().ajax.reload();

            swal.fire({
                title:'Producto',
                text: 'Registro Eliminado',
                icon: 'success'
            });
        }
    });
}

// Función para cambiar el estado del Producto via checkbox
function cambiarEstado(pro_id, estado) {
    var accion = estado ? 'activar' : 'desactivar';
    var titulo = estado ? 'Activar Producto' : 'Desactivar Producto';
    var texto = '¿Está seguro que desea ' + accion + ' este Producto?';
    
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
            $.post("../../controller/producto.php?op=cambiar_estado", {
                pro_id: pro_id,
                estado: estado
            }, function(data) {
                var response = JSON.parse(data);
                if(response.status === 'success') {
                    $('#table_data').DataTable().ajax.reload();
                    swal.fire({
                        title: 'Producto',          
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
                $('#switch' + pro_id).prop('checked', !estado);
            });
        } else {
            // Si el usuario cancela, revertir el checkbox
            $('#switch' + pro_id).prop('checked', !estado);
        }
    });
}

$(document).on("click","#btnnuevo",function(){
    $('#pro_id').val('');
    $('#pro_nom').val('');
    $('#lbltitulo').html('Nuevo Registro');
    $("#mantenimiento_form")[0].reset();
    
    // Remover clases de validación
    $('#pro_nom').removeClass('is-invalid is-valid');
    
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo nombre después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#pro_nom').focus();
    });
});



// Validación en tiempo real del campo nombre
$(document).on('input', '#pro_nom', function(){
    var valor = $(this).val().trim();
    var campo = $(this);
    
    // Remover clases previas
    campo.removeClass('is-invalid is-valid');
    
    if(valor.length === 0){
        campo.addClass('is-invalid');
    } else if(valor.length > 50){
        campo.addClass('is-invalid');
    } else {
        campo.addClass('is-valid');
    }
});
init();