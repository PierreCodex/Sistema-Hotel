function init(){
    $("#mantenimiento_form").on("submit",function(e){
        guardaryeditar(e);
    });
}

function guardaryeditar(e){
    e.preventDefault();
    
    // Validaciones del lado del cliente
    var cat_nom = $("#cat_nom").val().trim();
    
    if(cat_nom === ""){
        swal.fire({
            title:'Error de Validación',
            text: 'El nombre de la categoría es obligatorio',
            icon: 'warning'
        });
        $("#cat_nom").focus();
        return false;
    }
    
    if(cat_nom.length < 2){
        swal.fire({
            title:'Error de Validación',
            text: 'El nombre de la categoría debe tener al menos 2 caracteres',
            icon: 'warning'
        });
        $("#cat_nom").focus();
        return false;
    }
    
    if(cat_nom.length > 50){
        swal.fire({
            title:'Error de Validación',
            text: 'El nombre de la categoría no puede exceder 50 caracteres',
            icon: 'warning'
        });
        $("#cat_nom").focus();
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
        url:"../../controller/categoria.php?op=guardaryeditar",
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
            url:"../../controller/categoria.php?op=listar",
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

function editar(cat_id){
    $.post("../../controller/categoria.php?op=mostrar", {cat_id : cat_id}, function (data) {
        data = JSON.parse(data);
        $('#cat_id').val(data.CAT_ID);
        $('#cat_nom').val(data.CAT_NOM);
        
        // Remover clases de validación
        $('#cat_nom').removeClass('is-invalid is-valid');
        
        // Validar el campo cargado
        if(data.CAT_NOM && data.CAT_NOM.trim().length >= 2 && data.CAT_NOM.trim().length <= 50){
            $('#cat_nom').addClass('is-valid');
        }
    });
    $('#lbltitulo').html('Editar Registro');
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo nombre después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#cat_nom').focus().select();
    });
}

function eliminar(cat_id){
    swal.fire({
        title:"Eliminar!",
        text:"Desea Eliminar el Registro?",
        icon: "error",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/categoria.php?op=eliminar",{cat_id:cat_id},function(data){
                console.log(data);
            });

            $('#table_data').DataTable().ajax.reload();

            swal.fire({
                title:'Categoria',
                text: 'Registro Eliminado',
                icon: 'success'
            });
        }
    });
}



$(document).on("click","#btnnuevo",function(){
    $('#cat_id').val('');
    $('#cat_nom').val('');
    $('#lbltitulo').html('Nuevo Registro');
    $("#mantenimiento_form")[0].reset();
    
    // Remover clases de validación
    $('#cat_nom').removeClass('is-invalid is-valid');
    
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo nombre después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#cat_nom').focus();
    });
});

// Validación en tiempo real del campo nombre
$(document).on('input', '#cat_nom', function(){
    var valor = $(this).val().trim();
    var campo = $(this);
    
    // Remover clases previas
    campo.removeClass('is-invalid is-valid');
    
    if(valor.length === 0){
        campo.addClass('is-invalid');
    } else if(valor.length < 2){
        campo.addClass('is-invalid');
    } else if(valor.length > 50){
        campo.addClass('is-invalid');
    } else {
        campo.addClass('is-valid');
    }
});



init();