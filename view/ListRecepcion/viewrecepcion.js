$(function () {
  // Desactivar cache AJAX para evitar datos obsoletos
  $.ajaxSetup({
    cache: false
  });

  const HAB_URL = '../../controller/habitacion.php?op=listar_activos';
  const PISO_URL = '../../controller/piso.php?op=listar_activos';
  const OCUPANTES_URL = '../../controller/recepcion.php?op=listar_ocupaciones_activas';
  
  // Sin buscadores locales: manejamos datos directamente en cada render

  // Construye el HTML de una card según estado
  function buildCard(h, ocupantesMap) {
    const estado = String(h.ESTADO_NOM || '').toUpperCase().trim(); const recId = (ocupantesMap && ocupantesMap[h.HAB_ID] && ocupantesMap[h.HAB_ID].REC_ID) ? ocupantesMap[h.HAB_ID].REC_ID : '';
    const num = h.HAB_NUM || '';
    const cat = h.CAT_NOM || '';
    const amenities = h.CAT_AMENITIES || '';
    
    const ocupante = (ocupantesMap && ocupantesMap[h.HAB_ID]) ? ocupantesMap[h.HAB_ID].CLI_NOMBRE : '';

    // Función para generar HTML de iconos de amenities
    function renderAmenities(amenitiesStr) {
      if (!amenitiesStr) return '';
      const icons = amenitiesStr.split(',').slice(0, 5); // Máximo 5 iconos
      return icons.map(function(icon) {
        var title = getAmenityTitle(icon.trim());
        var color = getAmenityColor(icon.trim());
        return '<i class="' + icon.trim() + ' fs-5 me-1" style="color:' + color + ';" data-bs-toggle="tooltip" data-bs-placement="top" title="' + title + '"></i>';
      }).join('');
    }
    
    // Colores para cada tipo de amenity
    function getAmenityColor(iconClass) {
      const colors = {
        'ri-tv-line': '#000000',        // Azul
        'ri-tv-2-line': '#00000',      // Azul oscuro
        'ri-windy-line': '#0ab39c',     // Verde agua
        'ri-snowy-line': '#299cdb',     // Celeste
        'ri-sofa-line': '#f06548',      // Rojo/Naranja
        'ri-bubble-chart-line': '#6559cc', // Morado
        'ri-disc-line': '#f672a7',      // Rosa
        'ri-speaker-line': '#ffb000',   // Amarillo/Dorado
        'ri-wifi-line': '#0ab39c',      // Verde
        'ri-drop-line': '#299cdb'       // Celeste
      };
      return colors[iconClass] || '#ffffff';
    }
    
    // Títulos para los iconos
    function getAmenityTitle(iconClass) {
      const titles = {
        'ri-tv-line': 'TV Smart',
        'ri-tv-2-line': 'TV 60" 4K',
        'ri-windy-line': 'Ventilador',
        'ri-snowy-line': 'Aire Acondicionado',
        'ri-sofa-line': 'Sillón Tántrico',
        'ri-bubble-chart-line': 'Jacuzzi',
        'ri-disc-line': 'Pole Dance',
        'ri-speaker-line': 'Parlante Bluetooth',
        'ri-wifi-line': 'Wi-Fi',
        'ri-drop-line': 'Agua Caliente'
      };
      return titles[iconClass] || '';
    }





    if (estado === 'OCUPADO') {
      const recId = (ocupantesMap && ocupantesMap[h.HAB_ID] && ocupantesMap[h.HAB_ID].REC_ID) ? ocupantesMap[h.HAB_ID].REC_ID : '';
     
     
     
     
      const expire = (ocupantesMap && ocupantesMap[h.HAB_ID] && ocupantesMap[h.HAB_ID].FECHA_SALIDA) ? ocupantesMap[h.HAB_ID].FECHA_SALIDA : null;
      return (
        '<div class="col">\
          <div class="card ribbon-box border shadow-none right card-success mb-4" style="border-radius:16px; overflow:hidden;">\
            <div class="card-body position-relative">\
              <div class="ribbon ribbon-Success round-shape">Ocupada</div>\
              <div class="d-flex align-items-center justify-content-between mt-2">\
                <h2 class="card-title mb-3 fs-1">' + escapeHtml(num) + '</h2>\
                ' + (expire ? ('<span class="badge badge-gradient-danger d-inline-flex align-items-center gap-1"><i class="ri-time-line align-middle"></i><span class="js-countdown small" data-expire="' + escapeHtml(expire) + '"></span></span>') : '') + '\
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
              <div class="row g-2 align-items-center">\
                <div class="col-6">\
                  <a href="' + (recId ? ('../../view/DetalleRecepcion/index.php?recepcion=' + encodeURIComponent(recId)) : 'javascript:void(0);') + '" class="btn btn-primary btn-border w-100" ' + (recId ? '' : 'title="Sin recepción activa"') + '><i class=" bx bx-detail align-middle lh-1"></i></a>\
                </div>\
                <div class="col-6">\
                  <button type="button" class="btn btn-danger waves-effect waves-light w-100 btn-finalizar" data-recepcion="' + escapeHtml(recId) + '" data-habitacion="' + escapeHtml(h.HAB_ID) + '"> <i class="ri-logout-box-line align-middle lh-1"></i></button>\
                </div>\
              </div>\
            </div>\
          </div>\
        </div>'
      );
    }

    if (estado === 'LIMPIEZA') {
      return (
        '<div class="col">\
          <div class="card ribbon-box border shadow-none right card-warning mb-4" style="border-radius:16px; overflow:hidden;">\
            <div class="card-body">\
              <div class="ribbon ribbon-warning round-shape">Limpieza</div>\
              <div class="d-flex align-items-center">\
                <h2 class="card-title mb-3 fs-1">' + escapeHtml(num) + '</h2>\
              </div>\
              <div class="row align-items-end g-0">\
                <div class="col-6">\
                  <h6 class="mb-1 mt-1">' + escapeHtml(cat) + '</h6>\
                  <span class="text-white-75">' + renderAmenities(amenities) + '</span>\
                </div>\
                <div class="col-6 text-center">\
                  <i class="mdi mdi-broom fs-1 text-white-50"></i>\
                </div>\
              </div>\
            </div>\
            <div class="card-footer">\
              <div class="text-center">\
                <a href="javascript:void(0);" class="link-light btn-marcar-lista" data-habitacion="' + escapeHtml(h.HAB_ID) + '">Marcar lista <i class="ri-check-line align-middle lh-1"></i></a>\
              </div>\
            </div>\
          </div>\
        </div>'
      );
    }

    // Disponible y fallback
    return (
      '<div class="col">\
        <div class="card ribbon-box border shadow-none right card-secondary mb-4" style="border-radius:16px; overflow:hidden;">\
          <div class="card-body">\
            <div class="ribbon ribbon-info round-shape">Disponible</div>\
            <div class="d-flex align-items-center">\
              <h2 class="card-title mb-3 fs-1">' + escapeHtml(num) + '</h2>\
            </div>\
            <div class="row align-items-end g-0">\
              <div class="col-6">\
                <h6 class="mb-1 mt-1">' + escapeHtml(cat) + '</h6>\
                <span class="text-white-75">' + renderAmenities(amenities) + '</span>\
              </div>\
              <div class="col-6 text-center">\
                <i class="mdi mdi-bed-empty fs-1 text-white-50"></i>\
              </div>\
            </div>\
          </div>\
          <div class="card-footer">\
              <div class="text-center">\
                <a href="javascript:void(0);" class="link-light btn-reservar" data-id="' + escapeHtml(h.HAB_ID) + '" data-numero="' + escapeHtml(h.HAB_NUM) + '">Reservar <i class="ri-arrow-right-s-line align-middle lh-1"></i></a>\
              </div>\
            </div>\
        </div>\
      </div>'
    );
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
    
    // Inicializar tooltips de Bootstrap
    $container.find('[data-bs-toggle="tooltip"]').each(function() {
      new bootstrap.Tooltip(this);
    });
  }

  function formatDuration(ms){
    if (ms <= 0) return 'Finalizado';
    var s = Math.floor(ms/1000);
    var h = Math.floor(s/3600); s -= h*3600;
    var m = Math.floor(s/60); s -= m*60;
    return h + ' horas ' + m + ' min y ' + s + ' s';
  }
  function updateCountdowns(){
    var now = Date.now();
    $('.js-countdown').each(function(){
      var val = $(this).attr('data-expire');
      var ts = /^\\d+$/.test(String(val)) ? parseInt(val,10) : new Date(val).getTime();
      var diff = (ts || 0) - now;
      $(this).text(formatDuration(diff));
    });
  }
  function initCountdowns(){
    if (window._recepCountdownInterval) clearInterval(window._recepCountdownInterval);
    updateCountdowns();
    window._recepCountdownInterval = setInterval(updateCountdowns, 1000);
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
             <div class="row g-3 g-xl-4 row-cols-xxl-5 row-cols-xl-4 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1" id="piso-' + id + '-cards-row"></div>\
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

      renderCards('#todos-cards-row', Array.isArray(habs) ? habs : [], ocupantesMap);

      const grouped = {};
      (Array.isArray(habs) ? habs : []).forEach(function (h) {
        const pid = h.HAB_PISO_ID != null ? h.HAB_PISO_ID : (h.PISO_ID != null ? h.PISO_ID : h.IdPiso);
        if (pid == null) return;
        if (!grouped[pid]) grouped[pid] = [];
        grouped[pid].push(h);
      });

      (Array.isArray(pisos) ? pisos : []).forEach(function (p) {
        const list = grouped[p.PISO_ID] || [];
        renderCards('#piso-' + p.PISO_ID + '-cards-row', list, ocupantesMap);
      });

      bindTabEvents();
      initCountdowns();

      
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
          updateCountdowns();
        })
        .fail(function (err) {
          console.error('Error al cargar habitaciones por piso', err);
          renderCards(container, [], null);
          updateCountdowns();
        });
    });
  }

  // Evento click para botones de reservar
  $(document).on('click', '.btn-reservar', function () {
    const habitacionId = $(this).data('id');
    const habitacionNum = $(this).data('numero');
    window.location.href = '../../view/MntRecepcion/index.php?habitacion=' + encodeURIComponent(habitacionNum);
  });
  // Evento click para finalizar recepción desde la card
  $(document).on('click', '.btn-finalizar', function () {
    const recId = $(this).data('recepcion');
    if (!recId) return;
    window.location.href = '../../view/DetalleSalida/index.php?recepcion=' + encodeURIComponent(recId);
  });

  // Evento click para marcar habitación como lista (disponible)
  $(document).on('click', '.btn-marcar-lista', function () {
    const habitacionId = $(this).data('habitacion');
    if (!habitacionId) return;

    $.ajax({
      url: '../../controller/habitacion.php?op=cambiar_tipo_estado',
      type: 'POST',
      dataType: 'json',
      data: {
        hab_id: habitacionId,
        id_estado_habitacion: 11  // Reemplaza 3 con el ID real del estado DISPONIBLE en tu BD
      },
      success: function (resp) {
        if (resp.success) {
          location.reload();
        } else {
          alert('Error: ' + (resp.message || 'No se pudo actualizar'));
        }
      },
      error: function (err) {
        console.error('Error al marcar como lista:', err);
        alert('Error al actualizar la habitación');
      }
    });
  });

  
});