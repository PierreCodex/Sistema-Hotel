var timelineSwiper = null;
var currentPage = 1;
var limit = 3; // Number of items per page

$(document).ready(function () {
    // Initial Load
    loadClients();

    // Search functionality
    $('#btnSearch').on('click', function () {
        var query = $('#searchInput').val();
        $('#searchQueryLabel').text(query || 'Todos');
        currentPage = 1; // Reset to page 1 on search
        loadClients(query, currentPage);
    });

    $('#searchInput').on('keyup', function (e) {
        if (e.key === 'Enter') {
            $('#btnSearch').click();
        }
    });

    // Pagination click listener removed

    // Handle "View Timeline" clicks dynamically (for items added via JS)
    $(document).on('click', '.view-timeline-btn, .client-name-link', function () {
        var cli_id = $(this).data('id');
        var cli_name = $(this).data('name');
        verHistorial(cli_id, cli_name);
    });

    // Product toggle
    $(document).on('click', '.btn-toggle-products', function () {
        var container = $(this).closest('.timeline-text').find('.products-container');
        container.toggleClass('d-none');
    });
});

function loadClients(query = '', page = 1) {
    var start = (page - 1) * limit;

    $.ajax({
        url: "../../controller/cliente.php?op=listar_con_conteo",
        type: "GET",
        data: {
            start: start,
            limit: limit,
            search: query
        },
        dataType: "json",
        success: function (response) {
            var container = $('#results-list-container');
            container.empty();

            if (response && response.aaData && response.aaData.length > 0) {
                var template = document.getElementById('search-result-template');

                response.aaData.forEach(function (client) {
                    // aaData structure: 
                    // [0]=Tipo, [1]=Doc, [2]=Nom, [3]=Ape, [4]=Dir, [5]=Visitas, [6]=Estado, [7]=Btn

                    var fullName = client[2] + ' ' + client[3];
                    var doc = client[1];
                    var dir = client[4];
                    var visits = client[5];

                    // Extract ID from the button at index 7
                    var idMatch = client[7].match(/onClick="editar\((\d+)\)"/);
                    var id = idMatch ? idMatch[1] : 0;

                    if (query && fullName.toLowerCase().indexOf(query.toLowerCase()) === -1 && doc.indexOf(query) === -1) {
                        // return; // Skip if filtered -- Backend handles filtering now
                    }

                    // Clone template
                    var clone = template.content.cloneNode(true);

                    // Fill data
                    clone.querySelector('.client-name-link').textContent = fullName;
                    clone.querySelector('.client-name-link').dataset.id = id;
                    clone.querySelector('.client-name-link').dataset.name = fullName;

                    clone.querySelector('.client-doc span').textContent = doc;
                    clone.querySelector('.client-address').textContent = "Dirección: " + dir;

                    // Visit count
                    clone.querySelector('.client-visits').textContent = visits;

                    // Assign ID to button
                    var btn = clone.querySelector('.view-timeline-btn');
                    btn.dataset.id = id;
                    btn.dataset.name = fullName;

                    container.append(clone);
                });

                // Pagination removed as per user request (Show only preview)
                $('#pagination-container').empty();

            } else {
                container.html('<div class="text-center p-5 text-muted">No se encontraron clientes.</div>');
                $('#pagination-container').empty();
            }
        },
        error: function (e) {
        }
    });
}

// function renderPagination removed

function verHistorial(cli_id, clientName) {
    // 1. Switch Tab
    var triggerEl = document.querySelector('#tab-timeline');
    var tab = new bootstrap.Tab(triggerEl);
    tab.show();

    // 2. Set Header
    $('#timeline-client-name').html('Historial de visitas: <span class="text-primary">' + clientName + '</span>');

    // 3. Load Data
    var container = $('#timeline-container');
    container.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>');

    $.ajax({
        url: "../../controller/recepcion.php?op=listar_historial_cliente",
        type: "GET",
        data: { cli_id: cli_id },
        dataType: "json",
        success: function (response) {
            container.empty();

            if (response.success && response.data.length > 0) {
                var template = document.getElementById('timeline-item-template');
                var lastYear = null;

                response.data.forEach(function (item) {
                    var clone = template.content.cloneNode(true);

                    // --- Dates ---
                    var dateObj = new Date(item.FechaEntrada);
                    var year = dateObj.getFullYear();
                    var monthYear = dateObj.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
                    var timeStr = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

                    var dateSalida = item.FechaSalida ? new Date(item.FechaSalida) : null;
                    var timeSalida = dateSalida ? dateSalida.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '...';

                    // --- Year Marker ---
                    if (year !== lastYear) {
                        var yearDiv = clone.querySelector('.timeline-year');
                        yearDiv.classList.remove('d-none');
                        yearDiv.querySelector('p').textContent = year;
                        lastYear = year;
                    }

                    // --- Content ---
                    clone.querySelector('.visit-time-range').textContent = `${timeStr} - ${timeSalida}`;
                    clone.querySelector('.visit-full-date').textContent = monthYear; // Re-using this if we want date per item

                    clone.querySelector('.visit-room-title').textContent = `Habitación ${item.NumeroHabitacion} - ${item.Tarifa}`;
                    clone.querySelector('.visit-summary').innerHTML = `Estadía ${item.EstadoLabel}. Total: <span class="fw-bold">S/. ${parseFloat(item.TotalPagado).toFixed(2)}</span>`;

                    // --- Products (Consumo) ---
                    if (item.Consumo && item.Consumo.length > 0) {
                        var ul = clone.querySelector('.products-list');
                        item.Consumo.forEach(prod => {
                            var li = document.createElement('li');
                            li.textContent = `${prod.PRO_NOM} (x${prod.DETV_CANT}) - S/. ${parseFloat(prod.DETV_TOTAL).toFixed(2)}`;
                            ul.appendChild(li);
                        });
                        // Allow toggle
                        clone.querySelector('.btn-toggle-products').classList.remove('d-none');
                    } else {
                        // Hide toggle if no products
                        clone.querySelector('.btn-toggle-products').classList.add('d-none');
                    }

                    // --- Invoice ---
                    var btnInvoice = clone.querySelector('.btn-invoice-link');
                    if (item.Factura && item.Factura.fac_id) {
                        // Assuming we have a route to view details/invoice
                        // TODO: Set actual invoice link.
                        btnInvoice.href = "javascript:void(0)";

                        var invoiceLabel = (item.Factura.fac_serie || 'F001') + '-' + (item.Factura.fac_correlativo || '???');
                        btnInvoice.onclick = function () {
                            showInvoiceModal(item.Factura, item.IdRecepcion);
                        };

                        btnInvoice.classList.remove('d-none');
                        btnInvoice.textContent = "Ver Factura " + invoiceLabel;
                    } else {
                        btnInvoice.classList.add('d-none');
                    }

                    // --- Status Badge ---
                    clone.querySelector('.visit-status-badge').innerHTML = item.EstadoLabel;

                    container.append(clone);
                });

            } else {
                container.html('<div class="col-12 text-center p-5"><h5>No hay historial de visitas registrado para este cliente.</h5></div>');
            }
        },
        error: function (e) {
            container.html('<div class="col-12 text-center text-danger p-5">Error al cargar historial.</div>');
        }
    });
}

function showInvoiceModal(factura, rec_id) {
    var modalEl = document.getElementById('modal-comprobante');
    if (!modalEl) {
        console.error("Modal element not found");
        return;
    }

    // 1. Reset & Prepare UI
    $('#generando_contenido').hide();
    $('#error_contenido').hide();
    $('#generado_contenido').show();
    $('#btn_a4_container').show(); // Always show A4 for History as it's likely a Factura/Boleta
    $('#mensaje_estado').text('Comprobante emitido correctamente');
    $('#estado_boleta').removeClass('alert-info alert-danger').addClass('alert-success');
    $('#estado_boleta .spinner-border').hide();

    // 2. Set Data
    $('#numero_comprobante').text(factura.fac_serie + '-' + factura.fac_correlativo);

    // 3. Configure Download Buttons
    // Remove previous listeners to avoid duplicates if any (or just use direct onclick which is safer here)
    $('.btn-formato').off('click').on('click', function () {
        var formato = $(this).data('formato'); // 'a4' or '80mm' or '50mm'

        var tipoDoc = factura.tipo_doc || (factura.fac_serie && factura.fac_serie.startsWith('F') ? '01' : '03');
        var controllerFile = (tipoDoc === '01') ? 'factura.php' : 'boleta.php';
        var opCode = (tipoDoc === '01') ? 'pdf' : 'generar_pdf';

        var url = `../../controller/${controllerFile}?op=${opCode}&rec_id=${rec_id}&tipo=${formato}`;
        window.open(url, '_blank');
    });

    // 4. Show Modal
    var modalFn = new bootstrap.Modal(modalEl);
    modalFn.show();
}
