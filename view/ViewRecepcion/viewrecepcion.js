$(document).ready(function() {
    const HAB_URL = "../../controller/habitacion.php?op=listar_ocupados";
    const PISO_URL = "../../controller/piso.php?op=listar";

    function buildCard(habitacion) {
        const clienteNombre = 'Huésped actual';
        
        return `
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="card-room bg-success text-white mb-3">
                    <div class="card-room-header">
                        <h5 class="card-room-title">Habitación ${habitacion.HAB_NUM}</h5>
                        <span class="card-room-status">OCUPADO</span>
                    </div>
                    <div class="card-room-body">
                        <p class="card-room-detail">${escapeHtml(habitacion.HAB_DET)}</p>
                        <p class="card-room-category">${habitacion.CAT_NOM}</p>
                        <p class="card-room-price">S/. ${habitacion.HAB_PRE}</p>
                        <p class="card-room-guest">Huésped: ${escapeHtml(clienteNombre)}</p>
                        <button class="btn btn-warning btn-block btn-vender" 
                                data-id="${habitacion.HAB_ID}" 
                                data-numero="${habitacion.HAB_NUM}">
                            <i class="fa fa-shopping-cart"></i> Vender
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function renderCards(habitaciones, containerId) {
        const container = $(containerId);
        container.empty();
        
        if (habitaciones.length === 0) {
            container.html('<div class="col-12"><p class="text-center">No hay habitaciones ocupadas</p></div>');
            return;
        }

        habitaciones.forEach(habitacion => {
            container.append(buildCard(habitacion));
        });
    }

    function addPisoTab(piso, isActive = false) {
        const tabClass = isActive ? 'active' : '';
        const tabContent = isActive ? 'active in' : '';
        
        $('#pisoTabs').append(`
            <li class="${tabClass}">
                <a href="#tab_${piso.PISO_ID}" data-toggle="tab" aria-expanded="${isActive}">
                    ${piso.PISO_NOM}
                </a>
            </li>
        `);
        
        $('#pisoTabContent').append(`
            <div class="tab-pane fade ${tabContent}" id="tab_${piso.PISO_ID}">
                <div class="row" id="cards_piso_${piso.PISO_ID}">
                    <div class="col-12 text-center">
                        <p>Cargando habitaciones...</p>
                    </div>
                </div>
            </div>
        `);
    }

    function loadData() {
        Promise.all([
            $.getJSON(HAB_URL),
            $.getJSON(PISO_URL),
            $.getJSON(OCUPANTES_URL)
        ]).then(([habitaciones, pisos, ocupantes]) => {
            if (pisos.length > 0) {
                pisos.forEach((piso, index) => {
                    addPisoTab(piso, index === 0);
                    
                    const habitacionesPiso = habitaciones.filter(h => h.HAB_PISO_ID == piso.PISO_ID);
                    renderCards(habitacionesPiso, ocupantes, `#cards_piso_${piso.PISO_ID}`);
                });
            } else {
                $('#pisoTabContent').html('<div class="col-12"><p class="text-center">No hay pisos disponibles</p></div>');
            }
        }).catch(error => {
            console.error('Error al cargar datos:', error);
            $('#pisoTabContent').html('<div class="col-12"><p class="text-center">Error al cargar datos</p></div>');
        });
    }

    // Evento para cargar habitaciones al hacer clic en una pestaña
    $(document).on('click', '[data-toggle="tab"]', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        const pisoId = target.replace('#tab_', '');
        
        $.post('../../controller/habitacion.php?op=filtrar_por_piso', { piso_id: pisoId }, function(data) {
            const habitaciones = JSON.parse(data);
            $.getJSON(OCUPANTES_URL, function(ocupantes) {
                renderCards(habitaciones, ocupantes, `#cards_piso_${pisoId}`);
            });
        });
    });

    // Evento para el botón Vender
    $(document).on('click', '.btn-vender', function() {
        const habitacionId = $(this).data('id');
        const habitacionNum = $(this).data('numero');
        
        // Redirigir a la vista de ventas con el número de habitación
        window.location.href = `../../view/MntVender/index.php?habitacion=${encodeURIComponent(habitacionNum)}&hab_id=${encodeURIComponent(habitacionId)}`;
    });

    // Cargar datos al iniciar
    loadData();
});