$(function () {
  const HAB_URL = '../../controller/habitacion.php?op=listar_ocupados';
  const PISO_URL = '../../controller/piso.php?op=listar_activos';
  const OCUPANTES_URL = '../../controller/recepcion.php?op=listar_ocupaciones_activas';
  
  // Sin buscadores locales: manejamos datos directamente en cada render

  // Construye el HTML de una card según estado
  function buildCard(h, ocupantesMap) {
    const estado = String(h.ESTADO_NOM || '').toUpperCase().trim();
    const num = h.HAB_NUM || '';
    const cat = h.CAT_NOM || '';
    
    const precio = (h.HAB_PRE != null ? Number(h.HAB_PRE) : 0).toFixed(2);
    const ocupante = (ocupantesMap && ocupantesMap[h.HAB_ID]) ? ocupantesMap[h.HAB_ID].CLI_NOMBRE : '';

    if (estado === 'OCUPADO') {
      return (
        '<div class="col">\
          <div class="card ribbon-box border shadow-none right mb-lg-0 card-danger" style="border-radius:16px; overflow:hidden;">\
            <div class="card-body position-relative">\
              <div class="d-flex align-items-center">\
                <h2 class="card-title mb-3 fs-1">' + escapeHtml(num) + '</h2>\
              </div>\
              <div class="row align-items-end g-0">\
                <div class="col-6">\
                  <h6 class="mb-1 mt-1">' + escapeHtml(cat) + '</h6>\
                  <span class="badge badge-label bg-dark">' + escapeHtml(ocupante) + '</span>\
                </div>\
                <div class="col-6 text-center position-relative">\
                  <i class="mdi mdi-bed-empty fs-1 text-white-50"></i>\
                </div>\
              </div>\
            </div>\
            <div class="card-footer">\
              <div class="text-center">\
               <a href="javascript:void(0);" class="link-light btn-vender" data-id="' + escapeHtml(h.HAB_ID) + '" data-numero="' + escapeHtml(h.HAB_NUM) + '">Reservar <i class="ri-arrow-right-s-line align-middle lh-1"></i></a>\
              </div>\
            </div>\
          </div>\
        </div>'
      );
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function renderCards(containerSelector, habs, ocupantesMap) {
    const $container = $(containerSelector);
    $container.empty();
    if (!Array.isArray(habs) || habs.length === 0) {
      $container.append('<div class="col"><div class="alert alert-secondary">Sin habitaciones para mostrar.</div></div>');
      return;
    }
    const html = habs.map(function(h){ return buildCard(h, ocupantesMap); }).join('');
    $container.append(html);
  }

  // Eliminado: applySearch (no hay campo de búsqueda)

  function addPisoTab(piso) {
    const id = piso.PISO_ID;
    const nombre = piso.PISO_NOM;
    const tabId = 'tab-piso-' + id;

    // Agregar tab
    $('#recepcion-tabs').append(
      '<li class="nav-item">\
        <a class="nav-link" data-bs-toggle="tab" href="#' + tabId + '" role="tab">' + escapeHtml(nombre) + '</a>\
      </li>'
    );

    // Agregar pane contenedor
    $('#recepcion-tabcontent').append(
      '<div class="tab-pane" id="' + tabId + '" role="tabpanel">\
         <div class="row">\
           <div class="col-12">\
             <div class="row row-cols-xxl-5 row-cols-xl-4 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1" id="piso-' + id + '-cards-row"></div>\
           </div>\
         </div>\
       </div>'
    );
  }

  // Cargar datos y construir vista
  Promise.all([
    $.getJSON(HAB_URL),
    $.getJSON(PISO_URL),
    $.getJSON(OCUPANTES_URL)
  ])
    .then(function (results) {
      const habs = results[0];
      const pisos = results[1];
      const ocupantes = results[2];

      // Mapear ocupantes por HAB_ID
      const ocupantesMap = {};
      if (Array.isArray(ocupantes)) {
        ocupantes.forEach(function(o){
          if (o && o.HAB_ID != null) {
            ocupantesMap[o.HAB_ID] = o;
          }
        });
      }

      // No almacenamos cache local de habitaciones para búsqueda

      // Construir tabs/panes de pisos
      if (Array.isArray(pisos)) {
        pisos.forEach(addPisoTab);
      }

      // Render TODOS
      renderCards('#todos-cards-row', Array.isArray(habs) ? habs : [], ocupantesMap);

      // Render por piso
      const grouped = {};
      (Array.isArray(habs) ? habs : []).forEach(function (h) {
        // Usar el campo correcto para piso: HAB_PISO_ID (fallbacks por seguridad)
        const pid = h.HAB_PISO_ID != null ? h.HAB_PISO_ID : (h.PISO_ID != null ? h.PISO_ID : h.IdPiso);
        if (pid == null) return;
        if (!grouped[pid]) grouped[pid] = [];
        grouped[pid].push(h);
      });

      (Array.isArray(pisos) ? pisos : []).forEach(function (p) {
        const list = grouped[p.PISO_ID] || [];
        renderCards('#piso-' + p.PISO_ID + '-cards-row', list, ocupantesMap);
      });

      // Vincular carga por pestaña (estilo ViewCompra)
      bindTabEvents();

      // Sin eventos de búsqueda
    })
    .catch(function (err) {
      console.error('Error cargando datos de Recepción:', err);
      renderCards('#todos-cards-row', []);
    });
  
  function bindTabEvents() {
    $('#recepcion-tabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
      const target = $(e.target).attr('href'); // ej: #tab-piso-1 o #tab-todos
      if (!target) return;
      if (target === '#tab-todos') return; // ya cargado
      const match = target.match(/^#tab-piso-(\d+)$/);
      if (!match) return;
      const pisoId = parseInt(match[1], 10);
      const container = '#piso-' + pisoId + '-cards-row';
      // Si el contenedor ya tiene elementos, no recargar
      if ($(container).children().length > 0) return;
      // Cargar habitaciones de ese piso vía POST
      $.post('../../controller/habitacion.php?op=filtrar_por_piso', { piso_id: pisoId })
        .done(function (resp) {
          let data = [];
          try { data = JSON.parse(resp); } catch (e) { console.error('JSON inválido en filtrar_por_piso', e); }
          renderCards(container, Array.isArray(data) ? data : [], null);
        })
        .fail(function (err) {
          console.error('Error al cargar habitaciones por piso', err);
          renderCards(container, [], null);
        });
    });
  }

  // Evento click para botones de reservar
  $(document).on('click', '.btn-vender', function () {
    const habitacionId = $(this).data('id');
    const habitacionNum = $(this).data('numero');
    
    // Redirigir a la vista de venta incluyendo número e IdHabitacion
    window.location.href = `../../view/MntVender/index.php?habitacion=${encodeURIComponent(habitacionNum)}&hab_id=${encodeURIComponent(habitacionId)}`;
  });
});