function init(){
    $("#mantenimiento_form").on("submit",function(e){
        guardaryeditar(e);
    });
    
    // Cargar combos al inicializar
    combo_categoria();
    combo_piso();
    // combo_estado_habitacion(); // COMENTADO: El estado se asigna automáticamente como "Disponible"
}

function guardaryeditar(e){
    e.preventDefault();
    
    // Validaciones del lado del cliente
    var hab_num = $("#hab_num").val().trim();
    var hab_det = $("#hab_det").val().trim();
    var hab_pre = $("#hab_pre").val().trim();
    var hab_piso_id = $("#hab_piso_id").val();
    var hab_cat_id = $("#hab_cat_id").val();
    
    if(hab_num === ""){
        swal.fire({
            title:'Error de Validación',
            text: 'El número de habitación es obligatorio',
            icon: 'warning'
        });
        $("#hab_num").focus();
        return false;
    }
    
    if(hab_num.length > 10){
        swal.fire({
            title:'Error de Validación',
            text: 'El número de habitación no puede exceder 10 caracteres',
            icon: 'warning'
        });
        $("#hab_num").focus();
        return false;
    }
    
    if(hab_det === ""){
        swal.fire({
            title:'Error de Validación',
            text: 'La descripción de la habitación es obligatoria',
            icon: 'warning'
        });
        $("#hab_det").focus();
        return false;
    }
    
    if(hab_det.length > 100){
        swal.fire({
            title:'Error de Validación',
            text: 'La descripción no puede exceder 100 caracteres',
            icon: 'warning'
        });
        $("#hab_det").focus();
        return false;
    }
    
    if(hab_pre === "" || isNaN(hab_pre) || parseFloat(hab_pre) <= 0){
        swal.fire({
            title:'Error de Validación',
            text: 'El precio debe ser un número mayor a 0',
            icon: 'warning'
        });
        $("#hab_pre").focus();
        return false;
    }
    
    if(hab_piso_id === "" || hab_piso_id === null){
        swal.fire({
            title:'Error de Validación',
            text: 'Debe seleccionar un piso',
            icon: 'warning'
        });
        $("#hab_piso_id").focus();
        return false;
    }
    
    if(hab_cat_id === "" || hab_cat_id === null){
        swal.fire({
            title:'Error de Validación',
            text: 'Debe seleccionar una categoría',
            icon: 'warning'
        });
        $("#hab_cat_id").focus();
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
        url:"../../controller/habitacion.php?op=guardaryeditar",
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
            url:"../../controller/habitacion.php?op=listar",
            type:"post"
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo":true,
        "iDisplayLength": 10,
        "order": [[ 0, "asc" ]],
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

function editar(hab_id){
    $.post("../../controller/habitacion.php?op=mostrar", {hab_id : hab_id}, function (data) {
        data = JSON.parse(data);
        $('#hab_id').val(data.HAB_ID);
        $('#hab_num').val(data.HAB_NUM);
        $('#hab_det').val(data.HAB_DET);
        $('#hab_pre').val(data.HAB_PRE);
        $('#hab_piso_id').val(data.HAB_PISO_ID);
        $('#hab_cat_id').val(data.HAB_CAT_ID);
        $('#hab_est_id').val(data.HAB_EST_ID);
        
        // Remover clases de validación
        $('#hab_num, #hab_det, #hab_pre').removeClass('is-invalid is-valid');
        
        // Validar los campos cargados
        if(data.HAB_NUM && data.HAB_NUM.trim().length > 0 && data.HAB_NUM.trim().length <= 10){
            $('#hab_num').addClass('is-valid');
        }
        if(data.HAB_DET && data.HAB_DET.trim().length > 0 && data.HAB_DET.trim().length <= 100){
            $('#hab_det').addClass('is-valid');
        }
        if(data.HAB_PRE && !isNaN(data.HAB_PRE) && parseFloat(data.HAB_PRE) > 0){
            $('#hab_pre').addClass('is-valid');
        }
    });
    $('#lbltitulo').html('Editar Habitación');
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo número después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#hab_num').focus().select();
    });
}

function eliminar(hab_id){
    swal.fire({
        title:"Eliminar Habitación!",
        text:"¿Desea eliminar esta habitación?",
        icon: "error",
        confirmButtonText : "Sí",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/habitacion.php?op=eliminar",{hab_id:hab_id},function(data){
                console.log(data);
            });

            $('#table_data').DataTable().ajax.reload();

            swal.fire({
                title:'Habitación',
                text: 'Habitación eliminada correctamente',
                icon: 'success'
            });
        }
    });
}

// Función para cambiar el estado de la habitación via checkbox
function cambiarEstado(hab_id, estado) {
    var accion = estado ? 'activar' : 'desactivar';
    var titulo = estado ? 'Activar Habitación' : 'Desactivar Habitación';
    var texto = '¿Está seguro que desea ' + accion + ' esta habitación?';
    
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
            $.post("../../controller/habitacion.php?op=cambiar_estado", {
                hab_id: hab_id,
                estado: estado
            }, function(data) {
                var response = JSON.parse(data);
                if(response.status === 'success') {
                    $('#table_data').DataTable().ajax.reload();
                    swal.fire({
                        title: 'Habitación',         
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
                $('#switch' + hab_id).prop('checked', !estado);
            });
        } else {
            // Si el usuario cancela, revertir el checkbox
            $('#switch' + hab_id).prop('checked', !estado);
        }
    });
}

$(document).on("click","#btnnuevo",function(){
    $('#hab_id').val('');
    $('#hab_num').val('');
    $('#hab_det').val('');
    $('#hab_pre').val('');
    $('#hab_piso_id').val('');
    $('#hab_cat_id').val('');
    $('#hab_est_id').val('');
    $('#lbltitulo').html('Nueva Habitación');
    $("#mantenimiento_form")[0].reset();
    
    // Remover clases de validación
    $('#hab_num, #hab_det, #hab_pre').removeClass('is-invalid is-valid');
    
    // Recargar combos
    combo_categoria();
    combo_piso();
    combo_estado_habitacion();
    
    $('#modalmantenimiento').modal('show');
    
    // Enfocar el campo número después de que se muestre el modal
    $('#modalmantenimiento').on('shown.bs.modal', function () {
        $('#hab_num').focus();
    });
});

// Validación en tiempo real del campo número
$(document).on('input', '#hab_num', function(){
    var valor = $(this).val().trim();
    var campo = $(this);
    
    // Remover clases previas
    campo.removeClass('is-invalid is-valid');
    
    if(valor.length === 0){
        campo.addClass('is-invalid');
    } else if(valor.length > 10){
        campo.addClass('is-invalid');
    } else {
        campo.addClass('is-valid');
    }
});

// Validación en tiempo real del campo descripción
$(document).on('input', '#hab_det', function(){
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
$(document).on('input', '#hab_pre', function(){
    var valor = $(this).val().trim();
    var campo = $(this);
    
    // Remover clases previas
    campo.removeClass('is-invalid is-valid');
    
    if(valor.length === 0 || isNaN(valor) || parseFloat(valor) <= 0){
        campo.addClass('is-invalid');
    } else {
        campo.addClass('is-valid');
    }
});

// Función para cargar combo de categorías
function combo_categoria(){
    $.post("../../controller/habitacion.php?op=combo_categoria", function(data){
        $('#hab_cat_id').html(data);
    });
}

// Función para cargar combo de pisos
function combo_piso(){
    $.post("../../controller/habitacion.php?op=combo_piso", function(data){
        $('#hab_piso_id').html(data);
    });
}

// Función para cargar combo de estados de habitación
function combo_estado_habitacion(){
    $.post("../../controller/habitacion.php?op=combo_estado_habitacion", function(data){
        $('#hab_est_id').html(data);
    });
}

// Formatear precio mientras se escribe
$(document).on('input', '#hab_pre', function(){
    var valor = $(this).val();
    // Permitir solo números y punto decimal
    valor = valor.replace(/[^0-9.]/g, '');
    // Evitar múltiples puntos decimales
    var partes = valor.split('.');
    if(partes.length > 2){
        valor = partes[0] + '.' + partes.slice(1).join('');
    }
    $(this).val(valor);
});

// Función para filtrar habitaciones por piso
function filtrarPorPiso(piso_id){
    if(piso_id === ""){
        $('#table_data').DataTable().ajax.reload();
        return;
    }
    
    $.post("../../controller/habitacion.php?op=filtrar_por_piso", {piso_id: piso_id}, function(data){
        var habitaciones = JSON.parse(data);
        // Aquí puedes implementar la lógica para mostrar los resultados filtrados
        console.log(habitaciones);
    });
}

// Función para filtrar habitaciones por categoría
function filtrarPorCategoria(cat_id){
    if(cat_id === ""){
        $('#table_data').DataTable().ajax.reload();
        return;
    }
    
    $.post("../../controller/habitacion.php?op=filtrar_por_categoria", {cat_id: cat_id}, function(data){
        var habitaciones = JSON.parse(data);
        // Aquí puedes implementar la lógica para mostrar los resultados filtrados
        console.log(habitaciones);
    });
}

// Función para filtrar habitaciones por estado
function filtrarPorEstado(est_id){
    if(est_id === ""){
        $('#table_data').DataTable().ajax.reload();
        return;
    }
    
    $.post("../../controller/habitacion.php?op=filtrar_por_estado", {est_id: est_id}, function(data){
        var habitaciones = JSON.parse(data);
        // Aquí puedes implementar la lógica para mostrar los resultados filtrados
        console.log(habitaciones);
    });
}

init();