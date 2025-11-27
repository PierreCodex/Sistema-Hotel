
// Tomar usuario desde header (IdUsuario). No es requerido por el backend actual.
var usu_id = $('#IdUsuario').val();

$(document).ready(function(){
    // Asegurar existencia del input oculto de vent_id
    if($('#vent_id').length === 0){
        $('body').append('<input type="hidden" id="vent_id" />');
    }

    // Registrar venta inicial ligada a la recepción activa (hab_id en sesión)
    $.post("../../controller/venta.php?op=registrar", function(data){
        try{
            data = (typeof data === 'string') ? JSON.parse(data) : data;
        }catch(e){
            console.error('Respuesta inválida en registrar:', e, data);
            return;
        }
        if(data && data.VENT_ID){
            $('#vent_id').val(data.VENT_ID);
            // Cargar tabla inicial vacía
            listar(data.VENT_ID);
        } else if (data && data.success === false) {
            swal.fire({
                title:'Venta',
                text: data.message || 'No se pudo iniciar la venta',
                icon: 'error'
            });
        }
    });


    $('#pro_id').select2();

 

  

    $.post("../../controller/producto.php?op=combo", function(data){
        $("#pro_id").html(data);
    });


    

    $("#pro_id").change(function(){
        $("#pro_id").each(function(){
            pro_id = $(this).val();

            $.post("../../controller/producto.php?op=mostrar", {pro_id: pro_id}, function(data){
                data = (typeof data === 'string') ? JSON.parse(data) : data;
                $('#prod_pventa').val(data.PRO_PRE);
                $('#prod_stock').val(data.PRO_CANT);
                // Ajustar límites de cantidad según stock disponible
                var stock = parseInt(data.PRO_CANT, 10) || 0;
                $('#detv_cant').attr('min', 1).attr('max', stock);
                // Si no hay stock, sugerir 0 y deshabilitar agregar
                if (stock <= 0) {
                    $('#detv_cant').val('');
                    $('#btnagregar').prop('disabled', true);
                } else {
                    $('#btnagregar').prop('disabled', false);
                }
                
            });

        });
    });

});

$(document).on("click","#btnagregar",function(){
    var vent_id = $("#vent_id").val();
    var prod_id = $("#pro_id").val();
    var prod_pventa = $("#prod_pventa").val();
    var detv_cant = $("#detv_cant").val();
    var stock = parseInt($('#prod_stock').val(), 10) || 0;

    if($("#pro_id").val()=='' || $("#prod_pventa").val()=='' || $("#detv_cant").val()=='' ){

        swal.fire({
            title:'Venta',
            text: 'Error Campos Vacios',
            icon: 'error'
        });

    }else{
        // Validación preventiva en frontend contra stock
        var cant = parseInt(detv_cant, 10) || 0;
        if (stock <= 0) {
            swal.fire({
                title:'Venta',
                text: 'Stock agotado para el producto seleccionado',
                icon: 'error'
            });
            return;
        }
        if (cant <= 0) {
            swal.fire({
                title:'Venta',
                text: 'Ingrese una cantidad válida mayor a cero',
                icon: 'error'
            });
            return;
        }
        if (cant > stock) {
            swal.fire({
                title:'Venta',
                text: 'La cantidad ingresada ('+cant+') supera el stock disponible ('+stock+').',
                icon: 'error'
            });
            return;
        }

        $.post("../../controller/venta.php?op=guardardetalle", {
            vent_id: vent_id,
            prod_id: prod_id,
            prod_pventa: prod_pventa,
            detv_cant: detv_cant
        }, function(data){
            try{ data = (typeof data === 'string') ? JSON.parse(data) : data; }catch(e){ data = { success:false, message:'Respuesta inválida' }; }
            if (data && data.success) {
                // Stock se descuenta inmediatamente al agregar el producto
                $.post("../../controller/venta.php?op=calculo", {vent_id: vent_id}, function(calc){
                    calc = (typeof calc === 'string') ? JSON.parse(calc) : calc;
                    $('#txtsubtotal').html(calc.VENT_SUBTOTAL);
                    $('#txtigv').html(calc.VENT_IGV);
                    $('#txttotal').html(calc.VENT_TOTAL);
                });

                $("#prod_pventa").val('');
                $("#detv_cant").val('');
                
                // Actualizar información de stock en pantalla
                $("#pro_id").trigger('change'); // Recargar stock actual
                
                listar(vent_id);
                
                // Notificar que el stock se descontó
                swal.fire({
                    title:'Producto Agregado',
                    text: 'El stock se ha descontado inmediatamente del inventario',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                swal.fire({
                    title:'Venta',
                    text: (data && data.message) ? data.message : 'No se pudo guardar el detalle',
                    icon: 'error'
                });
            }
        });

    }

});

function eliminar(detv_id,vent_id){

    swal.fire({
        title:"Eliminar!",
        text:"Desea Eliminar el Registro?",
        icon: "error",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            // Esperar respuesta del servidor antes de actualizar la interfaz
            $.post("../../controller/venta.php?op=eliminardetalle", {detv_id: detv_id}, function(data){
                try { data = (typeof data === 'string') ? JSON.parse(data) : data; } catch(e) { data = {success: false}; }
                
                if(data && data.success){
                    // Recalcular totales
                    $.post("../../controller/venta.php?op=calculo", {vent_id: vent_id}, function(calcData){
                        calcData = (typeof calcData === 'string') ? JSON.parse(calcData) : calcData;
                        $('#txtsubtotal').html(calcData.VENT_SUBTOTAL);
                        $('#txtigv').html(calcData.VENT_IGV);
                        $('#txttotal').html(calcData.VENT_TOTAL);
                    });

                    // Refrescar tabla de detalles
                    listar(vent_id);
                    
                    // Actualizar stock en tiempo real del producto seleccionado actualmente
                    var pro_id_actual = $("#pro_id").val();
                    if(pro_id_actual && pro_id_actual !== ''){
                        $.post("../../controller/producto.php?op=mostrar", {pro_id: pro_id_actual}, function(prodData){
                            prodData = (typeof prodData === 'string') ? JSON.parse(prodData) : prodData;
                            $('#prod_stock').val(prodData.PRO_CANT);
                            // Actualizar límites de cantidad
                            var stock = parseInt(prodData.PRO_CANT, 10) || 0;
                            $('#detv_cant').attr('max', stock);
                            if (stock <= 0) {
                                $('#btnagregar').prop('disabled', true);
                            } else {
                                $('#btnagregar').prop('disabled', false);
                            }
                        });
                    }

                    swal.fire({
                        title:'Producto Eliminado',
                        text: 'El stock ha sido restaurado al inventario',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    swal.fire({
                        title:'Error',
                        text: (data && data.message) ? data.message : 'No se pudo eliminar el detalle',
                        icon: 'error'
                    });
                }
            });
        }
    });

}

function listar(vent_id){
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
            url:"../../controller/venta.php?op=listardetalle",
            type:"post",
            data:{vent_id:vent_id},
            dataSrc: function(json){ return json.aaData || []; }
        },
        "destroy": true,
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
}

$(document).on("click","#btnguardar",function(){
    var vent_id = $("#vent_id").val();
    var vent_estado = $("#vent_estado").val();
    
    if(!vent_estado){
        swal.fire({
            title:'Venta',
            text: 'Seleccione el estado de la venta',
            icon: 'error'
        });

    }else{
        $.post("../../controller/venta.php?op=calculo",{vent_id:vent_id},function(data){
            try {
                data = (typeof data === 'string') ? JSON.parse(data) : data;
            } catch (e) {
                swal.fire({
                    title:'Venta',
                    text: 'No se pudo calcular el total. Intente nuevamente.',
                    icon: 'error'
                });
                return;
            }
            if (data.VENT_TOTAL==null){
                /* TODO:Validacion de Detalle */
                swal.fire({
                    title:'Venta',
                    text: 'Error No Existe Detalle',
                    icon: 'error'
                });

            }else{
                $.post("../../controller/venta.php?op=guardar",{
                    vent_id:vent_id,
                    vent_estado: vent_estado
                },function(data){
                    try{ data = (typeof data === 'string') ? JSON.parse(data) : data; }catch(e){ data = { success:false, message:'Respuesta inválida' }; }
                    if (data && data.success){
                        var msg = 'Venta registrada Correctamente con Nro: V-'+vent_id;
                        if (data.estado === 'PENDIENTE') {
                            msg = 'Venta guardada como Pendiente. Se cobrará en salida.';
                        } else if (data.estado === 'PAGADO') {
                            msg = 'Venta pagada ahora. No se cobrará en salida.';
                        }
                        swal.fire({
                            title:'Venta',
                            text: msg,
                            icon: 'success',
                            footer: "<a href='../ViewVenta/?v="+vent_id+"' target='_blank'>Desea ver el Documento?</a>   |   <a href='../../assets/pdf/venta/v-"+vent_id+".pdf' target='_blank'>Descargar PDF?</a>"
                        });
     // Generar PDF solo después de guardar exitosamente
                        $.get("../../controller/pdfprint.php?op=pdfventa",{vent_id:vent_id},function(pdfResp){ /* opcional */ });
                   
                    } else {
                        swal.fire({
                            title:'Venta',
                            text: (data && data.message) ? data.message : 'No se pudo guardar la venta',
                            icon: 'error'
                        });
                    }
                });

            }

        });

    }

});

$(document).on("click","#btnlimpiar",function(){
    var vent_id = $("#vent_id").val();
    
    // Si hay una venta borrador, preguntar si desea cancelarla
    if(vent_id && vent_id != ''){
        swal.fire({
            title: "¿Cancelar Venta?",
            text: "Esto restaurará el stock de todos los productos agregados. ¿Está seguro?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, Cancelar",
            cancelButtonText: "No, Continuar"
        }).then((result) => {
            if (result.isConfirmed) {
                // Cancelar venta borrador y restaurar stock
                $.post("../../controller/venta.php?op=cancelar_borrador", {vent_id: vent_id}, function(data){
                    try { data = (typeof data === 'string') ? JSON.parse(data) : data; } catch(e) { data = {success: false}; }
                    if(data && data.success){
                        swal.fire({
                            title: 'Venta Cancelada',
                            text: 'El stock ha sido restaurado al inventario',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        swal.fire({
                            title: 'Aviso',
                            text: data.message || 'No se pudo cancelar la venta',
                            icon: 'warning'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    } else {
        location.reload();
    }
});

// Advertir al usuario si intenta salir con venta borrador sin guardar
window.addEventListener('beforeunload', function(e) {
    var vent_id = $("#vent_id").val();
    var hasItems = $('#table_data tbody tr').length > 0 && !$('#table_data tbody').text().includes('No se encontraron');
    
    if(vent_id && hasItems) {
        e.preventDefault();
        e.returnValue = 'Tiene productos agregados sin guardar. El stock ya fue descontado. ¿Seguro que desea salir?';
        return e.returnValue;
    }
});