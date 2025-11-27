/**
 * Reporte de Recepciones - JavaScript
 * Hotel Las Palmeras
 */

// Variables globales
let graficoOcupacion = null;
let graficoPisos = null;
let dataTable = null;
let vistaGraficoActual = 'mensual';

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarFlatpickr();
    inicializarDataTable();
    
    // Cargar datos del mes actual por defecto
    document.getElementById('periodo_rapido').value = 'mes';
    establecerPeriodo('mes');
    cargarReporte();
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
            const primerDiaSemana = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 1));
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
    dataTable = $('#tabla_recepciones').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        dom: 'Bfrtip',
        buttons: []
    });
}

// Cargar reporte
function cargarReporte() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
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
        text: 'Obteniendo datos del reporte',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = new FormData();
    formData.append('operacion', 'obtener_reporte_recepciones');
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    formData.append('estado', estado);
    formData.append('vista_grafico', vistaGraficoActual);
    
    fetch('../../controller/reporte.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.status) {
            actualizarTarjetas(data.resumen);
            actualizarGraficoOcupacion(data.grafico_ocupacion);
            actualizarGraficoPisos(data.grafico_pisos);
            actualizarTablaRecepciones(data.lista);
            actualizarHabitacionesTop(data.habitaciones_top);
            actualizarTarifas(data.tarifas);
            actualizarClientesFrecuentes(data.clientes_frecuentes);
            actualizarPisos(data.pisos);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al cargar el reporte'
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
    document.getElementById('total_recepciones').textContent = resumen.total_recepciones || 0;
    document.getElementById('recepciones_activas').textContent = resumen.recepciones_activas || 0;
    document.getElementById('ingresos_hospedaje').textContent = formatearMonto(resumen.ingresos_hospedaje || 0);
    document.getElementById('estancia_promedio').textContent = resumen.estancia_promedio || 0;
    
    // Variación (si se proporciona)
    const variacion = resumen.variacion || 0;
    const elementoVariacion = document.getElementById('variacion_recepciones');
    if (variacion >= 0) {
        elementoVariacion.className = 'text-success fs-14 mb-0';
        elementoVariacion.innerHTML = `<i class="ri-arrow-right-up-line fs-13 align-middle"></i> ${variacion}%`;
    } else {
        elementoVariacion.className = 'text-danger fs-14 mb-0';
        elementoVariacion.innerHTML = `<i class="ri-arrow-right-down-line fs-13 align-middle"></i> ${Math.abs(variacion)}%`;
    }
}

// Actualizar gráfico de ocupación
function actualizarGraficoOcupacion(datos) {
    if (graficoOcupacion) {
        graficoOcupacion.destroy();
    }
    
    const opciones = {
        series: [{
            name: 'Recepciones',
            type: 'bar',
            data: datos.recepciones || []
        }, {
            name: 'Ingresos (S/)',
            type: 'line',
            data: datos.ingresos || []
        }],
        chart: {
            height: 350,
            type: 'line',
            toolbar: {
                show: false
            }
        },
        colors: ['#405189', '#0ab39c'],
        stroke: {
            width: [0, 3]
        },
        plotOptions: {
            bar: {
                columnWidth: '50%'
            }
        },
        xaxis: {
            categories: datos.etiquetas || []
        },
        yaxis: [{
            title: {
                text: 'Recepciones'
            }
        }, {
            opposite: true,
            title: {
                text: 'Ingresos (S/)'
            }
        }],
        tooltip: {
            shared: true,
            intersect: false
        },
        legend: {
            position: 'top'
        }
    };
    
    graficoOcupacion = new ApexCharts(document.querySelector("#grafico_ocupacion"), opciones);
    graficoOcupacion.render();
}

// Actualizar gráfico de pisos
function actualizarGraficoPisos(datos) {
    if (graficoPisos) {
        graficoPisos.destroy();
    }
    
    const opciones = {
        series: datos.valores || [],
        chart: {
            height: 300,
            type: 'donut',
            toolbar: {
                show: false
            }
        },
        labels: datos.etiquetas || [],
        colors: ['#405189', '#0ab39c', '#f7b84b', '#f06548', '#299cdb', '#6c757d'],
        legend: {
            position: 'bottom'
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
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
    
    graficoPisos = new ApexCharts(document.querySelector("#grafico_pisos"), opciones);
    graficoPisos.render();
}

// Cambiar vista del gráfico
function cambiarVistaGrafico(vista) {
    vistaGraficoActual = vista;
    
    // Actualizar botones
    document.querySelectorAll('.card-header .btn-soft-secondary, .card-header .btn-soft-primary').forEach(btn => {
        btn.classList.remove('btn-soft-primary');
        btn.classList.add('btn-soft-secondary');
    });
    event.target.classList.remove('btn-soft-secondary');
    event.target.classList.add('btn-soft-primary');
    
    cargarReporte();
}

// Actualizar tabla de recepciones
function actualizarTablaRecepciones(lista) {
    dataTable.clear();
    
    let totalAcumulado = 0;
    
    lista.forEach(item => {
        const estadoBadge = item.Estado == 1 
            ? '<span class="badge badge-soft-warning">En Curso</span>'
            : '<span class="badge badge-soft-success">Finalizada</span>';
        
        const checkOut = item.FechaSalidaConfirmacion || item.FechaSalida || '-';
        
        totalAcumulado += parseFloat(item.TotalPagado) || 0;
        
        dataTable.row.add([
            item.IdRecepcion,
            item.NombreCliente,
            item.NumeroHabitacion,
            item.NombrePiso,
            formatearFechaHora(item.FechaEntrada),
            formatearFechaHora(checkOut),
            item.NombreTarifa || 'Sin tarifa',
            'S/ ' + formatearMonto(item.TotalPagado),
            estadoBadge
        ]);
    });
    
    dataTable.draw();
    document.getElementById('footer_total').textContent = 'S/ ' + formatearMonto(totalAcumulado);
}

// Actualizar habitaciones top
function actualizarHabitacionesTop(datos) {
    const tbody = document.getElementById('tbody_habitaciones_top');
    tbody.innerHTML = '';
    
    datos.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td><span class="badge bg-primary-subtle text-primary">${item.NumeroHabitacion}</span></td>
            <td>${item.Categoria}</td>
            <td><span class="badge bg-info">${item.TotalRecepciones}</span></td>
            <td><strong>S/ ${formatearMonto(item.Ingresos)}</strong></td>
        `;
        tbody.appendChild(tr);
    });
    
    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos disponibles</td></tr>';
    }
}

// Actualizar tarifas
function actualizarTarifas(datos) {
    const tbody = document.getElementById('tbody_tarifas');
    tbody.innerHTML = '';
    
    // Calcular total para porcentaje
    const totalGeneral = datos.reduce((sum, item) => sum + parseFloat(item.Total || 0), 0);
    
    datos.forEach((item, index) => {
        const porcentaje = totalGeneral > 0 ? ((parseFloat(item.Total) / totalGeneral) * 100).toFixed(1) : 0;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.NombreTarifa || 'Sin tarifa'}</td>
            <td><span class="badge bg-info">${item.TotalRecepciones}</span></td>
            <td><strong>S/ ${formatearMonto(item.Total)}</strong></td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="progress flex-grow-1" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: ${porcentaje}%"></div>
                    </div>
                    <span class="ms-2">${porcentaje}%</span>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos disponibles</td></tr>';
    }
}

// Actualizar clientes frecuentes
function actualizarClientesFrecuentes(datos) {
    const tbody = document.getElementById('tbody_clientes_frecuentes');
    tbody.innerHTML = '';
    
    datos.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td><i class="ri-user-line text-primary me-1"></i> ${item.NombreCliente}</td>
            <td><code>${item.Documento}</code></td>
            <td><span class="badge bg-success">${item.Visitas}</span></td>
            <td><strong>S/ ${formatearMonto(item.TotalGastado)}</strong></td>
        `;
        tbody.appendChild(tr);
    });
    
    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos disponibles</td></tr>';
    }
}

// Actualizar pisos
function actualizarPisos(datos) {
    const tbody = document.getElementById('tbody_pisos');
    tbody.innerHTML = '';
    
    datos.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td><i class="ri-building-line text-info me-1"></i> ${item.NombrePiso}</td>
            <td>${item.TotalHabitaciones}</td>
            <td><span class="badge bg-primary">${item.TotalRecepciones}</span></td>
            <td><strong>S/ ${formatearMonto(item.Ingresos)}</strong></td>
        `;
        tbody.appendChild(tr);
    });
    
    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos disponibles</td></tr>';
    }
}

// Formatear monto
function formatearMonto(valor) {
    return parseFloat(valor || 0).toFixed(2);
}

// Formatear fecha y hora
function formatearFechaHora(fechaStr) {
    if (!fechaStr || fechaStr === '-') return '-';
    const fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-PE') + ' ' + fecha.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'});
}

// Exportar a Excel
function exportarExcel() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
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
        operacion: 'exportar_excel_recepciones',
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        estado: estado
    });
    
    window.location.href = `../../controller/reporte.php?${params.toString()}`;
}

// Exportar a PDF
function exportarPDF() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
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
        operacion: 'exportar_pdf_recepciones',
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        estado: estado
    });
    
    window.open(`../../controller/reporte.php?${params.toString()}`, '_blank');
}
