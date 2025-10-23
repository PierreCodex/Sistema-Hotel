function init(){
    $("#mantenimiento_form").on("submit",function(e){
        guardaryeditar(e);
    });
}

function guardaryeditar(e){
    e.preventDefault();
    
    // Validaciones del lado del cliente
    var rol_nom = $('#rol_nom').val().trim();
    
    // Limpiar clases de validación previas
    $('#rol_nom').removeClass('is-invalid is-valid');
    
    // Validar campo vacío
    if(rol_nom === ''){
        $('#rol_nom').addClass('is-invalid');
        swal.fire({
            title: 'Error de Validación',
            text: 'El nombre del rol es obligatorio',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        $('#rol_nom').focus();
        return false;
    }
    
    // Validar longitud mínima
    if(rol_nom.length < 2){
        $('#rol_nom').addClass('is-invalid');
        swal.fire({
            title: 'Error de Validación',
            text: 'El nombre del rol debe tener al menos 2 caracteres',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        $('#rol_nom').focus();
        return false;
    }
    
    // Validar longitud máxima
    if(rol_nom.length > 50){
        $('#rol_nom').addClass('is-invalid');
        swal.fire({
            title: 'Error de Validación',
            text: 'El nombre del rol no puede exceder 50 caracteres',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        $('#rol_nom').focus();
        return false;
    }
    
    // Si pasa todas las validaciones, marcar como válido
    $('#rol_nom').addClass('is-valid');
    
    // Mostrar indicador de carga
    swal.fire({
        title: 'Procesando...',
        text: 'Guardando información del rol',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            swal.showLoading();
        }
    });
    
    var formData = new FormData($("#mantenimiento_form")[0]);
    $.ajax({
        url:"../../controller/rol.php?op=guardaryeditar",
        type:"POST",
        data:formData,
        contentType:false,
        processData:false,
        success:function(data){
            try {
                var response = JSON.parse(data);
                
                if(response.status === 'success'){
                    $('#table_data').DataTable().ajax.reload();
                    $('#modalmantenimiento').modal('hide');
                    
                    swal.fire({
                        title: 'Éxito',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                } else {
                    swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            } catch (e) {
                swal.fire({
                    title: 'Error',
                    text: 'Error al procesar la respuesta del servidor',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        },
        error: function(xhr, status, error) {
            swal.fire({
                title: 'Error de Conexión',
                text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                icon: 'error',
                confirmButtonText: 'Entendido'
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
            url:"../../controller/rol.php?op=listar",
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

function editar(rol_id){
    $.post("../../controller/rol.php?op=mostrar",{rol_id:rol_id},function(data){
        data=JSON.parse(data);
        $('#rol_id').val(data.ROL_ID);
        $('#rol_nom').val(data.ROL_NOM);
        
        // Limpiar clases de validación
        $('#rol_nom').removeClass('is-invalid is-valid');
        
        // Validar el valor cargado
        validarRolNombre();
    });
    $('#lbltitulo').html('Editar Registro');
    $('#modalmantenimiento').modal('show');
    
    // Enfocar en el campo de nombre cuando se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#rol_nom').focus();
    });
}

function eliminar(rol_id){
    swal.fire({
        title:"Eliminar!",
        text:"Desea Eliminar el Registro?",
        icon: "error",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/rol.php?op=eliminar",{rol_id:rol_id},function(data){
                console.log(data);
            });

            $('#table_data').DataTable().ajax.reload();

            swal.fire({
                title:'Rol',
                text: 'Registro Eliminado',
                icon: 'success'
            });
        }
    });
}

function activar(rol_id){
    swal.fire({
        title:"Activar!",
        text:"Desea Activar el Registro?",
        icon: "question",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/rol.php?op=activar",{rol_id:rol_id},function(data){
                console.log(data);
            });

            $('#table_data').DataTable().ajax.reload();

            swal.fire({
                title:'Rol',
                text: 'Registro Activado',
                icon: 'success'
            });
        }
    });
}

// Función para validar el nombre del rol en tiempo real
function validarRolNombre(){
    var rol_nom = $('#rol_nom').val().trim();
    
    // Limpiar clases previas
    $('#rol_nom').removeClass('is-invalid is-valid');
    
    if(rol_nom === ''){
        $('#rol_nom').addClass('is-invalid');
        return false;
    } else if(rol_nom.length < 2){
        $('#rol_nom').addClass('is-invalid');
        return false;
    } else if(rol_nom.length > 50){
        $('#rol_nom').addClass('is-invalid');
        return false;
    } else {
        $('#rol_nom').addClass('is-valid');
        return true;
    }
}

$(document).on("click","#btnnuevo",function(){
    $('#rol_id').val('');
    $('#rol_nom').val('');
    $('#lbltitulo').html('Nuevo Registro');
    $("#mantenimiento_form")[0].reset();
    
    // Limpiar clases de validación
    $('#rol_nom').removeClass('is-invalid is-valid');
    
    $('#modalmantenimiento').modal('show');
    
    // Enfocar en el campo de nombre cuando se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#rol_nom').focus();
    });
});

// Validación en tiempo real mientras el usuario escribe
$(document).on('input', '#rol_nom', function(){
    validarRolNombre();
});

init();