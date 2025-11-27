/**
 * Reporte de Ventas - JavaScript
 * Maneja la carga de datos, gráficos y exportación
 */

// Variables globales
let graficoVentas = null;
let graficoProductos = null;
let dataTable = null;
let vistaGrafico = 'mensual';

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    inicializarFlatpickr();
    establecerFechasIniciales();
    inicializarDataTable();
    cargarReporte();
});

/**
 * Inicializa los selectores de fecha con Flatpickr
 */
function inicializarFlatpickr() {
    const configFlatpickr = {
        locale: 'es',
        dateFormat: 'Y-m-d',
        allowInput: true
    };
    
    flatpickr('#fecha_inicio', configFlatpickr);
    flatpickr('#fecha_fin', configFlatpickr);
    
    // Evento para período rápido
    document.getElementById('periodo_rapido').addEventListener('change', function() {
        const periodo = this.value;
        const hoy = new Date();
        let fechaInicio, fechaFin;
        
        switch(periodo) {
            case 'hoy':
                fechaInicio = fechaFin = formatearFecha(hoy);
                break;
            case 'semana':
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - hoy.getDay());
                fechaInicio = formatearFecha(inicioSemana);
                fechaFin = formatearFecha(hoy);
                break;
            case 'mes':
                fechaInicio = formatearFecha(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
                fechaFin = formatearFecha(hoy);
                break;
            case 'anio':
                fechaInicio = formatearFecha(new Date(hoy.getFullYear(), 0, 1));
                fechaFin = formatearFecha(hoy);
                break;
            default:
                return;
        }
        
        document.getElementById('fecha_inicio').value = fechaInicio;
        document.getElementById('fecha_fin').value = fechaFin;
        
        // Actualizar flatpickr
        document.getElementById('fecha_inicio')._flatpickr.setDate(fechaInicio);
        document.getElementById('fecha_fin')._flatpickr.setDate(fechaFin);
    });
}

/**
 * Establece las fechas iniciales (primer día del mes actual hasta hoy)
 */
function establecerFechasIniciales() {
    const hoy = new Date();
    const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    document.getElementById('fecha_inicio').value = formatearFecha(primerDiaMes);
    document.getElementById('fecha_fin').value = formatearFecha(hoy);
}

/**
 * Formatea una fecha a YYYY-MM-DD
 */
function formatearFecha(fecha) {
    const year = fecha.getFullYear();
    const month = String(fecha.getMonth() + 1).padStart(2, '0');
    const day = String(fecha.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Inicializa la DataTable
 */
function inicializarDataTable() {
    if ($.fn.DataTable.isDataTable('#tabla_ventas')) {
        $('#tabla_ventas').DataTable().destroy();
    }
    
    dataTable = $('#tabla_ventas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        order: [[1, 'desc']],
        dom: 'Bfrtip',
        buttons: []
    });
}

/**
 * Carga el reporte completo
 */
function cargarReporte() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const estado = document.getElementById('filtro_estado').value;
    
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas requeridas',
            text: 'Por favor seleccione un rango de fechas',
            confirmButtonColor: '#405189'
        });
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Cargando reporte...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Cargar datos
    $.ajax({
        url: '../../controller/reporte.php',
        type: 'POST',
        dataType: 'json',
        data: {
            operacion: 'reporte_ventas',
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
            estado: estado
        },
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                actualizarTarjetas(response.resumen);
                actualizarTablaVentas(response.ventas);
                actualizarGraficoVentas(response.grafico);
                actualizarGraficoProductos(response.top_productos);
                actualizarTablaTopProductos(response.top_productos);
                actualizarTablaEmpleados(response.ventas_empleado);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.mensaje || 'Error al cargar el reporte',
                    confirmButtonColor: '#405189'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor',
                confirmButtonColor: '#405189'
            });
        }
    });
}

/**
 * Actualiza las tarjetas de resumen
 */
function actualizarTarjetas(resumen) {
    animarNumero('total_ventas', parseFloat(resumen.total_ventas || 0).toFixed(2));
    animarNumero('cantidad_ventas', parseInt(resumen.cantidad_ventas || 0));
    animarNumero('productos_vendidos', parseInt(resumen.productos_vendidos || 0));
    animarNumero('ticket_promedio', parseFloat(resumen.ticket_promedio || 0).toFixed(2));
    
    // Variación (comparado con período anterior)
    const variacion = resumen.variacion || 0;
    const elementoVariacion = document.getElementById('variacion_ventas');
    if (variacion >= 0) {
        elementoVariacion.className = 'text-success fs-14 mb-0';
        elementoVariacion.innerHTML = `<i class="ri-arrow-right-up-line fs-13 align-middle"></i> +${variacion.toFixed(1)}%`;
    } else {
        elementoVariacion.className = 'text-danger fs-14 mb-0';
        elementoVariacion.innerHTML = `<i class="ri-arrow-right-down-line fs-13 align-middle"></i> ${variacion.toFixed(1)}%`;
    }
}

/**
 * Anima el contador de números
 */
function animarNumero(elementId, targetValue) {
    const elemento = document.getElementById(elementId);
    const duration = 1000;
    const startValue = 0;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentValue = startValue + (parseFloat(targetValue) - startValue) * progress;
        
        if (typeof targetValue === 'string' && targetValue.includes('.')) {
            elemento.textContent = currentValue.toFixed(2);
        } else {
            elemento.textContent = Math.floor(currentValue);
        }
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            elemento.textContent = targetValue;
        }
    }
    
    requestAnimationFrame(update);
}

/**
 * Actualiza la tabla de ventas
 */
function actualizarTablaVentas(ventas) {
    dataTable.clear();
    
    let totalGeneral = 0;
    
    ventas.forEach(function(venta) {
        const estadoBadge = obtenerBadgeEstado(venta.Estado);
        totalGeneral += parseFloat(venta.Total || 0);
        
        dataTable.row.add([
            venta.IdVenta,
            formatearFechaVisual(venta.FechaVenta),
            venta.NumeroHabitacion || 'N/A',
            venta.NombreCliente || 'Sin cliente',
            venta.CantidadProductos || 0,
            `S/ ${parseFloat(venta.Total || 0).toFixed(2)}`,
            estadoBadge,
            venta.NombreEmpleado || 'No registrado'
        ]);
    });
    
    dataTable.draw();
    
    // Actualizar footer
    document.getElementById('footer_total').textContent = `S/ ${totalGeneral.toFixed(2)}`;
}

/**
 * Obtiene el badge de estado con colores
 */
function obtenerBadgeEstado(estado) {
    switch(estado) {
        case 'PAGADO':
            return '<span class="badge bg-success">Pagado</span>';
        case 'PENDIENTE':
            return '<span class="badge bg-warning text-dark">Pendiente</span>';
        case 'ANULADO':
            return '<span class="badge bg-danger">Anulado</span>';
        default:
            return `<span class="badge bg-secondary">${estado}</span>`;
    }
}

/**
 * Formatea fecha para visualización
 */
function formatearFechaVisual(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Actualiza el gráfico de ventas por período
 */
function actualizarGraficoVentas(datosGrafico) {
    if (graficoVentas) {
        graficoVentas.destroy();
    }
    
    // Si no hay datos, mostrar mensaje
    if (!datosGrafico || datosGrafico.length === 0) {
        document.getElementById('grafico_ventas').innerHTML = '<div class="text-center text-muted py-5"><i class="ri-bar-chart-box-line fs-1"></i><p>No hay datos para mostrar en el período seleccionado</p></div>';
        return;
    }
    
    const categorias = datosGrafico.map(item => item.periodo);
    const valores = datosGrafico.map(item => parseFloat(item.total));
    const cantidades = datosGrafico.map(item => parseInt(item.cantidad));
    
    const opciones = {
        series: [{
            name: 'Ingresos (S/)',
            type: 'area',
            data: valores
        }, {
            name: 'Cantidad Ventas',
            type: 'line',
            data: cantidades
        }],
        chart: {
            height: 350,
            type: 'line',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        stroke: {
            width: [2, 3],
            curve: 'smooth'
        },
        fill: {
            type: ['gradient', 'solid'],
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        colors: ['#0ab39c', '#405189'],
        xaxis: {
            categories: categorias,
            labels: {
                style: {
                    colors: '#8c8c8c'
                }
            }
        },
        yaxis: [{
            title: {
                text: 'Ingresos (S/)'
            },
            labels: {
                formatter: function(val) {
                    return 'S/ ' + val.toFixed(2);
                }
            }
        }, {
            opposite: true,
            title: {
                text: 'Cantidad'
            }
        }],
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(val, { seriesIndex }) {
                    if (seriesIndex === 0) {
                        return 'S/ ' + val.toFixed(2);
                    }
                    return val + ' ventas';
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        },
        grid: {
            borderColor: '#e0e0e0',
            strokeDashArray: 5
        }
    };
    
    graficoVentas = new ApexCharts(document.getElementById('grafico_ventas'), opciones);
    graficoVentas.render();
}

/**
 * Actualiza el gráfico de productos más vendidos
 */
function actualizarGraficoProductos(productos) {
    if (graficoProductos) {
        graficoProductos.destroy();
    }
    
    // Si no hay datos, mostrar mensaje
    if (!productos || productos.length === 0) {
        document.getElementById('grafico_productos').innerHTML = '<div class="text-center text-muted py-5"><i class="ri-pie-chart-line fs-1"></i><p>Sin productos vendidos</p></div>';
        return;
    }
    
    const top5 = productos.slice(0, 5);
    const nombres = top5.map(item => item.NombreProducto);
    const cantidades = top5.map(item => parseInt(item.CantidadTotal));
    
    const opciones = {
        series: cantidades,
        chart: {
            type: 'donut',
            height: 300
        },
        labels: nombres,
        colors: ['#405189', '#0ab39c', '#f7b84b', '#f06548', '#299cdb'],
        legend: {
            position: 'bottom',
            fontSize: '12px'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' unid.';
                            }
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };
    
    graficoProductos = new ApexCharts(document.getElementById('grafico_productos'), opciones);
    graficoProductos.render();
}

/**
 * Actualiza la tabla de top productos
 */
function actualizarTablaTopProductos(productos) {
    const tbody = document.getElementById('tbody_top_productos');
    tbody.innerHTML = '';
    
    productos.slice(0, 10).forEach((producto, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="badge bg-primary-subtle text-primary">${index + 1}</span></td>
            <td>${producto.NombreProducto}</td>
            <td><strong>${producto.CantidadTotal}</strong></td>
            <td>S/ ${parseFloat(producto.TotalVendido).toFixed(2)}</td>
        `;
        tbody.appendChild(tr);
    });
    
    if (productos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>';
    }
}

/**
 * Actualiza la tabla de ventas por empleado
 */
function actualizarTablaEmpleados(empleados) {
    const tbody = document.getElementById('tbody_ventas_empleado');
    tbody.innerHTML = '';
    
    empleados.forEach((empleado, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="badge bg-info-subtle text-info">${index + 1}</span></td>
            <td>${empleado.NombreEmpleado || 'No registrado'}</td>
            <td><strong>${empleado.CantidadVentas}</strong></td>
            <td>S/ ${parseFloat(empleado.TotalVentas).toFixed(2)}</td>
        `;
        tbody.appendChild(tr);
    });
    
    if (empleados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>';
    }
}

/**
 * Cambia la vista del gráfico de ventas
 */
function cambiarVistaGrafico(vista) {
    vistaGrafico = vista;
    
    // Actualizar botones activos
    document.querySelectorAll('[onclick^="cambiarVistaGrafico"]').forEach(btn => {
        btn.classList.remove('btn-soft-primary');
        btn.classList.add('btn-soft-secondary');
    });
    event.target.classList.remove('btn-soft-secondary');
    event.target.classList.add('btn-soft-primary');
    
    // Recargar datos con nueva vista
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const estado = document.getElementById('filtro_estado').value;
    
    $.ajax({
        url: '../../controller/reporte.php',
        type: 'POST',
        dataType: 'json',
        data: {
            operacion: 'grafico_ventas',
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
            estado: estado,
            vista: vista
        },
        success: function(response) {
            if (response.success) {
                actualizarGraficoVentas(response.grafico);
            }
        }
    });
}

/**
 * Exporta a Excel
 */
function exportarExcel() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const estado = document.getElementById('filtro_estado').value;
    
    window.location.href = `../../controller/reporte.php?operacion=exportar_excel&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&estado=${estado}`;
}

/**
 * Exporta a PDF
 */
function exportarPDF() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const estado = document.getElementById('filtro_estado').value;
    
    window.location.href = `../../controller/reporte.php?operacion=exportar_pdf&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&estado=${estado}`;
}
