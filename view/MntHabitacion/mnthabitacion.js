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
    
    if(hab_det.length > 500){
        swal.fire({
            title:'Error de Validación',
            text: 'La descripción no puede exceder 500 caracteres',
            icon: 'warning'
        });
        $("#hab_det").focus();
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
        "columnDefs": [
            {
                "targets": 1,
                "className": "descripcion-cell",
                "render": function(data, type, row) {
                    if (type === 'display' && data && data.length > 80) {
                        return '<span title="' + data + '">' + data.substr(0, 80) + '...</span>';
                    }
                    return data;
                }
            }
        ],
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
        $('#hab_piso_id').val(data.HAB_PISO_ID);
        $('#hab_cat_id').val(data.HAB_CAT_ID);
        $('#hab_est_id').val(data.HAB_EST_ID);
        
        // Remover clases de validación
        $('#hab_num, #hab_det').removeClass('is-invalid is-valid');
        
        // Validar los campos cargados
        if(data.HAB_NUM && data.HAB_NUM.trim().length > 0 && data.HAB_NUM.trim().length <= 10){
            $('#hab_num').addClass('is-valid');
        }
        if(data.HAB_DET && data.HAB_DET.trim().length > 0 && data.HAB_DET.trim().length <= 500){
            $('#hab_det').addClass('is-valid');
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
    $('#hab_piso_id').val('');
    $('#hab_cat_id').val('');
    $('#hab_est_id').val('');
    $('#lbltitulo').html('Nueva Habitación');
    $("#mantenimiento_form")[0].reset();
    
    // Remover clases de validación
    $('#hab_num, #hab_det').removeClass('is-invalid is-valid');
    
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
    } else if(valor.length > 500){
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

// Función para filtrar habitaciones por piso

//function filtrarPorPiso(piso_id){
  //  if(piso_id === ""){
 ////       $('#table_data').DataTable().ajax.reload();
  //      return;
  //  }
    
  //  $.post("../../controller/habitacion.php?op=filtrar_por_piso", {piso_id: piso_id}, function(data){
 //       var habitaciones = JSON.parse(data);
 //       // Aquí puedes implementar la lógica para mostrar los resultados filtrados
       // //  });
//}

// Función para filtrar habitaciones por categoría


// Función para filtrar habitaciones por estado
//function filtrarPorEstado(est_id){
   // if(est_id === ""){
   //     $('#table_data').DataTable().ajax.reload();
   //     return;
   // }
    
 //   $.post("../../controller/habitacion.php?op=filtrar_por_estado", {est_id: est_id}, function(data){
      //  var habitaciones = JSON.parse(data);
        // Aquí puedes implementar la lógica para mostrar los resultados filtrados
     //   //    });
//}

init();


// ===== Asignación de tarifas =====
let tarifaAsignaciones = {}; // Map por IdTarifa -> asignación {id_habitacion_tarifa, fecha_inicio, fecha_fin}
let tarifaHabId = null;

function abrirModalTarifa(hab_id){
    tarifaHabId = hab_id;
    // Obtener contexto de la habitación
    $.post("../../controller/habitacion.php?op=mostrar", {hab_id : hab_id}, function (data) {
        try {
            data = JSON.parse(data);
            const ctx = `Habitación ${data.HAB_NUM} - ${data.HAB_DET}`;
            $('#tarifa_hab_context').text(ctx);
        } catch(e){
            $('#tarifa_hab_context').text('');
        }
    });

    // Cargar asignadas primero
    $.post("../../controller/tarifa.php?op=listar_asignadas", {hab_id: hab_id}, function(resp){
        try{
            const asignadas = JSON.parse(resp);
            tarifaAsignaciones = {};
            asignadas.forEach(a => {
                tarifaAsignaciones[a.id_tarifa] = a;
            });
        }catch(e){
            tarifaAsignaciones = {};
        }
        // Luego cargar catálogo de tarifas y pintar tabla
        $.post("../../controller/tarifa.php?op=listar-activas", function(r){
            try{
                const tarifas = JSON.parse(r);
                pintarTablaTarifas(tarifas);
            }catch(err){
                pintarTablaTarifas([]);
            }
            $('#modaltarifa').modal('show');
        });
    });
}

function formatoDatetimeLocal(dt){
    if(!dt) return '';
    // dt esperado: "YYYY-MM-DD HH:MM:SS"
    const parts = dt.replace('T',' ').split(/[- :]/);
    if(parts.length < 5) return '';
    const y = parts[0], m = parts[1].padStart(2, '0'), d = parts[2].padStart(2, '0');
    const hh = parts[3].padStart(2, '0'), mm = parts[4].padStart(2, '0');
    return `${y}-${m}-${d}T${hh}:${mm}`;
}

function pintarTablaTarifas(tarifas){
    const tbody = $('#tabla_tarifas tbody');
    tbody.empty();
    tarifas.forEach(t => {
        const asign = tarifaAsignaciones[t.IdTarifa] || null;
        const checked = asign ? 'checked' : '';
        const fi = asign ? formatoDatetimeLocal(asign.fecha_inicio) : '';
        const ff = asign ? formatoDatetimeLocal(asign.fecha_fin) : '';
        const row = `
            <tr data-tarifa-id="${t.IdTarifa}" data-asignacion-id="${asign ? asign.id_habitacion_tarifa : ''}">
                <td>${t.Descripcion}</td>
                <td>${parseFloat(t.Precio).toFixed(2)}</td>
                <td><input type="datetime-local" class="form-control form-control-sm vigencia-inicio" value="${fi}" /></td>
                <td><input type="datetime-local" class="form-control form-control-sm vigencia-fin" value="${ff}" /></td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input asignado-switch" type="checkbox" ${checked} />
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary btn-guardar-vigencia" ${asign ? '' : 'disabled'}>Guardar vigencia</button>
                </td>
            </tr>`;
        tbody.append(row);
    });

    // Eventos
    tbody.off('change', '.asignado-switch').on('change', '.asignado-switch', function(){
        const tr = $(this).closest('tr');
        const tarifaId = tr.data('tarifa-id');
        const inicio = tr.find('.vigencia-inicio').val();
        const fin = tr.find('.vigencia-fin').val();
        const asignado = $(this).is(':checked');
        const asignId = tr.data('asignacion-id');

        if(asignado){
            if(!inicio){
                swal.fire({ title:'Vigencia requerida', text:'Ingrese fecha/hora de inicio', icon:'warning' });
                $(this).prop('checked', false);
                return;
            }
            $.post('../../controller/tarifa.php?op=asignar', {
                hab_id: tarifaHabId,
                tarifa_id: tarifaId,
                fecha_inicio: inicio,
                fecha_fin: fin
            }, function(resp){
                try{
                    const r = JSON.parse(resp);
                    if(r.status === 'success'){
                        // Recargar asignaciones para actualizar id
                        abrirModalTarifa(tarifaHabId);
                    }else{
                        swal.fire({ title:'Error', text:r.message||'No se pudo asignar', icon:'error' });
                        tr.find('.asignado-switch').prop('checked', false);
                    }
                }catch(e){
                    swal.fire({ title:'Error', text:'Respuesta inválida', icon:'error' });
                    tr.find('.asignado-switch').prop('checked', false);
                }
            });
        }else{
            if(!asignId){
                return; // Nada que eliminar
            }
            $.post('../../controller/tarifa.php?op=eliminar_asignada', {
                habitacion_tarifa_id: asignId
            }, function(resp){
                try{
                    const r = JSON.parse(resp);
                    if(r.status === 'success'){
                        abrirModalTarifa(tarifaHabId);
                    }else{
                        swal.fire({ title:'Error', text:r.message||'No se pudo eliminar', icon:'error' });
                        tr.find('.asignado-switch').prop('checked', true);
                    }
                }catch(e){
                    swal.fire({ title:'Error', text:'Respuesta inválida', icon:'error' });
                    tr.find('.asignado-switch').prop('checked', true);
                }
            });
        }
    });

    tbody.off('click', '.btn-guardar-vigencia').on('click', '.btn-guardar-vigencia', function(){
        const tr = $(this).closest('tr');
        const asignId = tr.data('asignacion-id');
        const inicio = tr.find('.vigencia-inicio').val();
        const fin = tr.find('.vigencia-fin').val();
        if(!asignId){
            swal.fire({ title:'No asignado', text:'Primero asigne la tarifa', icon:'info' });
            return;
        }
        if(!inicio){
            swal.fire({ title:'Vigencia requerida', text:'Ingrese fecha/hora de inicio', icon:'warning' });
            return;
        }
        $.post('../../controller/tarifa.php?op=actualizar_vigencia', {
            habitacion_tarifa_id: asignId,
            fecha_inicio: inicio,
            fecha_fin: fin
        }, function(resp){
            try{
                const r = JSON.parse(resp);
                if(r.status === 'success'){
                    swal.fire({ title:'Actualizado', text:'Vigencia guardada', icon:'success', timer:1200, showConfirmButton:false });
                }else{
                    swal.fire({ title:'Error', text:r.message||'No se pudo actualizar', icon:'error' });
                }
            }catch(e){
                swal.fire({ title:'Error', text:'Respuesta inválida', icon:'error' });
            }
        });
    });
}