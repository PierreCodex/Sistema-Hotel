/**
 * Historial de Comprobantes - JavaScript
 * Hotel Las Palmeras
 */

// Variables globales
let dataTable = null;

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarFlatpickr();
    inicializarDataTable();
    
    // Establecer período del mes actual
    establecerPeriodo('mes');
    cargarComprobantes();
});

// Inicializar Flatpickr para las fechas
function inicializarFlatpickr() {
    flatpickr("#fecha_inicio", {
        locale: "es",
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange: function() {
            document.getElementById('periodo_rapido').value = '';
        }
    });
    
    flatpickr("#fecha_fin", {
        locale: "es",
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange: function() {
            document.getElementById('periodo_rapido').value = '';
        }
    });
    
    // Evento para período rápido
    document.getElementById('periodo_rapido').addEventListener('change', function() {
        if (this.value) {
            establecerPeriodo(this.value);
        }
    });
}

// Establecer período según selección rápida
function establecerPeriodo(periodo) {
    const hoy = new Date();
    let inicio, fin;
    
    switch(periodo) {
        case 'hoy':
            inicio = fin = formatearFecha(hoy);
            break;
        case 'semana':
            const primerDiaSemana = new Date(hoy);
            primerDiaSemana.setDate(hoy.getDate() - hoy.getDay() + 1);
            inicio = formatearFecha(primerDiaSemana);
            fin = formatearFecha(new Date());
            break;
        case 'mes':
            inicio = formatearFecha(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            fin = formatearFecha(new Date());
            break;
        case 'anio':
            inicio = formatearFecha(new Date(hoy.getFullYear(), 0, 1));
            fin = formatearFecha(new Date());
            break;
    }
    
    document.getElementById('fecha_inicio').value = inicio;
    document.getElementById('fecha_fin').value = fin;
}

// Formatear fecha a YYYY-MM-DD
function formatearFecha(fecha) {
    return fecha.toISOString().split('T')[0];
}

// Inicializar DataTable
function inicializarDataTable() {
    dataTable = $('#tabla_comprobantes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 15,
        order: [[2, 'desc']],
        dom: 'Bfrtip',
        buttons: []
    });
}

// Cargar comprobantes
function cargarComprobantes() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const tipo = document.getElementById('filtro_tipo').value;
    const estado = document.getElementById('filtro_estado').value;
    
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debe seleccionar un rango de fechas'
        });
        return;
    }
    
    // Mostrar loader
    Swal.fire({
        title: 'Cargando...',
        text: 'Obteniendo comprobantes',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = new FormData();
    formData.append('operacion', 'listar_comprobantes');
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    formData.append('tipo', tipo);
    formData.append('estado', estado);
    
    fetch('../../controller/comprobante.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.status) {
            actualizarTarjetas(data.resumen);
            actualizarTabla(data.lista);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al cargar los comprobantes'
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión al servidor'
        });
    });
}

// Actualizar tarjetas de resumen
function actualizarTarjetas(resumen) {
    document.getElementById('total_emitidos').textContent = resumen.total_emitidos || 0;
    document.getElementById('total_boletas').textContent = resumen.total_boletas || 0;
    document.getElementById('monto_boletas').textContent = formatearMonto(resumen.monto_boletas || 0);
    document.getElementById('total_facturas').textContent = resumen.total_facturas || 0;
    document.getElementById('monto_facturas').textContent = formatearMonto(resumen.monto_facturas || 0);
    document.getElementById('total_facturado').textContent = formatearMonto(resumen.total_facturado || 0);
}

// Actualizar tabla de comprobantes
function actualizarTabla(lista) {
    dataTable.clear();
    
    let totalSubtotal = 0;
    let totalIgv = 0;
    let totalGeneral = 0;
    
    lista.forEach(item => {
        // Tipo de documento
        const tipoBadge = item.bol_tipo == '03' 
            ? '<span class="badge bg-info">Boleta</span>'
            : '<span class="badge bg-success">Factura</span>';
        
        // Estado
        let estadoBadge = '';
        switch(item.bol_estado) {
            case 'ACEPTADA':
                estadoBadge = '<span class="badge bg-success"><i class="ri-check-line me-1"></i>Aceptada</span>';
                break;
            case 'EMITIDA':
                estadoBadge = '<span class="badge bg-warning"><i class="ri-time-line me-1"></i>Emitida</span>';
                break;
            case 'RECHAZADA':
                estadoBadge = '<span class="badge bg-danger"><i class="ri-close-line me-1"></i>Rechazada</span>';
                break;
            case 'ANULADA':
                estadoBadge = '<span class="badge bg-secondary"><i class="ri-forbid-line me-1"></i>Anulada</span>';
                break;
            default:
                estadoBadge = '<span class="badge bg-light text-dark">' + item.bol_estado + '</span>';
        }
        
        // Botones de acción
        const acciones = `
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-soft-primary" onclick="verDetalle(${item.bol_id})" title="Ver detalle">
                    <i class="ri-eye-line"></i>
                </button>
                <button type="button" class="btn btn-soft-danger" onclick="descargarPDF(${item.bol_id})" title="Descargar PDF">
                    <i class="ri-file-pdf-line"></i>
                </button>
                <button type="button" class="btn btn-soft-success" onclick="descargarXML(${item.bol_id})" title="Descargar XML">
                    <i class="ri-code-s-slash-line"></i>
                </button>
            </div>
        `;
        
        totalSubtotal += parseFloat(item.bol_subtotal) || 0;
        totalIgv += parseFloat(item.bol_igv) || 0;
        totalGeneral += parseFloat(item.bol_total) || 0;
        
        dataTable.row.add([
            tipoBadge,
            `<strong>${item.bol_serie}-${item.bol_correlativo}</strong>`,
            formatearFechaHora(item.bol_fecha_emision),
            item.bol_cliente_razon_social || 'Cliente General',
            item.bol_cliente_num_doc || '-',
            'S/ ' + formatearMonto(item.bol_subtotal),
            'S/ ' + formatearMonto(item.bol_igv),
            '<strong>S/ ' + formatearMonto(item.bol_total) + '</strong>',
            estadoBadge,
            acciones
        ]);
    });
    
    dataTable.draw();
    
    // Actualizar totales del footer
    document.getElementById('footer_subtotal').textContent = 'S/ ' + formatearMonto(totalSubtotal);
    document.getElementById('footer_igv').textContent = 'S/ ' + formatearMonto(totalIgv);
    document.getElementById('footer_total').textContent = 'S/ ' + formatearMonto(totalGeneral);
}

// Ver detalle del comprobante
function verDetalle(bol_id) {
    const formData = new FormData();
    formData.append('operacion', 'obtener_detalle');
    formData.append('bol_id', bol_id);
    
    fetch('../../controller/comprobante.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            mostrarModalDetalle(data.comprobante, data.detalles);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo cargar el detalle'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión'
        });
    });
}

// Mostrar modal con detalle
function mostrarModalDetalle(comprobante, detalles) {
    const tipoDoc = comprobante.bol_tipo == '03' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'FACTURA ELECTRÓNICA';
    const estadoClass = comprobante.bol_estado == 'ACEPTADA' ? 'success' : 
                        comprobante.bol_estado == 'RECHAZADA' ? 'danger' : 'warning';
    
    let detallesHTML = '';
    detalles.forEach((det, index) => {
        detallesHTML += `
            <tr>
                <td>${index + 1}</td>
                <td>${det.bol_det_codigo}</td>
                <td>${det.bol_det_descripcion}</td>
                <td class="text-center">${det.bol_det_cantidad}</td>
                <td class="text-end">S/ ${formatearMonto(det.bol_det_precio_unitario)}</td>
                <td class="text-end">S/ ${formatearMonto(det.bol_det_total)}</td>
            </tr>
        `;
    });
    
    const html = `
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Comprobante</h6>
                <h5 class="mb-1">${tipoDoc}</h5>
                <p class="mb-0"><strong>${comprobante.bol_serie}-${comprobante.bol_correlativo}</strong></p>
                <small class="text-muted">Emitido: ${formatearFechaHora(comprobante.bol_fecha_emision)}</small>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge bg-${estadoClass} fs-6">${comprobante.bol_estado}</span>
                <p class="mb-0 mt-2"><small class="text-muted">Método de pago:</small></p>
                <p class="mb-0"><strong>${comprobante.bol_metodo_pago || 'EFECTIVO'}</strong></p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-light border-0">
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Cliente:</small>
                                <p class="mb-0 fw-semibold">${comprobante.bol_cliente_razon_social || 'CLIENTE GENERAL'}</p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Documento:</small>
                                <p class="mb-0 fw-semibold">${comprobante.bol_cliente_num_doc || '-'}</p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Dirección:</small>
                                <p class="mb-0">${comprobante.bol_cliente_direccion || '-'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">P. Unit.</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${detallesHTML}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end"><strong>Sub Total:</strong></td>
                        <td class="text-end">S/ ${formatearMonto(comprobante.bol_subtotal)}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end"><strong>IGV (18%):</strong></td>
                        <td class="text-end">S/ ${formatearMonto(comprobante.bol_igv)}</td>
                    </tr>
                    <tr class="table-primary">
                        <td colspan="5" class="text-end"><strong>TOTAL:</strong></td>
                        <td class="text-end"><strong>S/ ${formatearMonto(comprobante.bol_total)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        ${comprobante.bol_observaciones ? `
        <div class="alert alert-info mt-3">
            <i class="ri-information-line me-2"></i>
            <strong>Observaciones SUNAT:</strong> ${comprobante.bol_observaciones}
        </div>
        ` : ''}
    `;
    
    document.getElementById('contenido_detalle').innerHTML = html;
    document.getElementById('modalDetalleLabel').innerHTML = `
        <i class="ri-file-list-3-line me-2"></i>${tipoDoc} ${comprobante.bol_serie}-${comprobante.bol_correlativo}
    `;
    
    new bootstrap.Modal(document.getElementById('modalDetalle')).show();
}

// Descargar PDF
function descargarPDF(bol_id) {
    window.open(`../../controller/comprobante.php?operacion=descargar_pdf&bol_id=${bol_id}`, '_blank');
}

// Descargar XML
function descargarXML(bol_id) {
    window.location.href = `../../controller/comprobante.php?operacion=descargar_xml&bol_id=${bol_id}`;
}

// Exportar a Excel
function exportarExcel() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const tipo = document.getElementById('filtro_tipo').value;
    const estado = document.getElementById('filtro_estado').value;
    
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debe seleccionar un rango de fechas'
        });
        return;
    }
    
    const params = new URLSearchParams({
        operacion: 'exportar_excel',
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        tipo: tipo,
        estado: estado
    });
    
    window.location.href = `../../controller/comprobante.php?${params.toString()}`;
}

// Exportar a PDF
function exportarPDF() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const tipo = document.getElementById('filtro_tipo').value;
    const estado = document.getElementById('filtro_estado').value;
    
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debe seleccionar un rango de fechas'
        });
        return;
    }
    
    const params = new URLSearchParams({
        operacion: 'exportar_pdf_reporte',
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        tipo: tipo,
        estado: estado
    });
    
    window.open(`../../controller/comprobante.php?${params.toString()}`, '_blank');
}

// Formatear monto
function formatearMonto(valor) {
    return parseFloat(valor || 0).toFixed(2);
}

// Formatear fecha y hora
function formatearFechaHora(fechaStr) {
    if (!fechaStr) return '-';
    const fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-PE') + ' ' + fecha.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'});
}
