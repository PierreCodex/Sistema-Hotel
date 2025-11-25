$(document).ready(function(){
  function getParam(name){
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
  }

  const recId = getParam('recepcion');
  if (!recId) return;

  function num2(val){
    const n = parseFloat(val);
    return isNaN(n) ? '' : n.toFixed(2);
  }

  function cargarRecepcion(recId){
    return $.post('../../controller/recepcion.php?op=obtener_x_id', { rec_id: recId })
      .then(function(resp){
        let parsed = resp;
        if (typeof resp === 'string') { try { parsed = JSON.parse(resp); } catch(e) {} }
        return (parsed && parsed.success) ? parsed.data : null;
      });
  }

  function renderResumenHabitacion(hab){
    $('#txtnombre').text(hab && (hab.HAB_NUM || hab.Numero || ''));
    $('#txtdetalle').text(hab && (hab.HAB_DET || hab.Detalle || ''));
    $('#txtcategoria').text(hab && (hab.CAT_NOM || hab.CategoriaNombre || hab.Categoria || ''));
    $('#txtestado').text(hab && (hab.ESTADO_NOM || hab.EstadoNombre || hab.Estado || ''));
   
  }

  function renderCliente(cli){
    const nombre = ((cli && (cli.CLI_NOM || cli.CLI_NOMBRE)) || '') + ' ' + ((cli && cli.CLI_APE) || '');
    $('#txtcliente').text(nombre.trim());
    $('#txtdocumento').text((cli && (cli.CLI_DOC || cli.Documento)) || '');
    $('#txtdireccion').text((cli && (cli.CLI_DIR || cli.cli_direcc || cli.Direccion)) || '');

  }

  function cargarHabitacionYCliente(habId, cliId){
    const pHab = habId ? $.post('../../controller/habitacion.php?op=obtener_por_id', { hab_id: habId }) : $.Deferred().resolve(null);
    const pCli = cliId ? $.post('../../controller/cliente.php?op=mostrar', { cli_id: cliId }) : $.Deferred().resolve(null);
    return $.when(pHab, pCli).then(function(habResp, cliResp){
      let habData = habResp[0];
      let cliData = cliResp[0];
      if (typeof habData === 'string') { try { habData = JSON.parse(habData); } catch (e) {} }
      if (typeof cliData === 'string') { try { cliData = JSON.parse(cliData); } catch (e) {} }
      renderResumenHabitacion(habData || {});
      renderCliente(cliData || {});
    });
  }

  function listarVentasPorRecepcion(recId){
    return $.post('../../controller/venta.php?op=listar_por_recepcion', { rec_id: recId })
      .then(function(resp){
        let parsed = resp;
        if (typeof resp === 'string') { try { parsed = JSON.parse(resp); } catch(e) {} }
        return (parsed && parsed.success && Array.isArray(parsed.data)) ? parsed.data : [];
      });
  }

  function cargarYRenderizarDetalles(ventas){
    const tbody = $('#table_data tbody');
    tbody.empty();
    let sumaPendientes = 0;
    const dfd = $.Deferred();
    let pending = ventas.length;
    if (pending === 0) {
      tbody.html('<tr><td colspan="5" class="text-center">Sin venta registrada o sin detalles para esta recepción.</td></tr>');
      dfd.resolve({ sumaPendientes: 0 });
      return dfd.promise();
    }
    ventas.forEach(function(v){
      $.post('../../controller/venta.php?op=listardetalle', { vent_id: v.IdVenta })
        .then(function(resp){
          let data = resp;
          if (typeof resp === 'string') { try { data = JSON.parse(resp); } catch(e) {} }
          const rows = Array.isArray(data.aaData) ? data.aaData : [];
          if (rows.length > 0){
            rows.forEach(function(row){
              const tr = '<tr>' +
                '<td>' + row[1] + '</td>' +
                '<td>' + row[2] + '</td>' +
                '<td>' + row[3] + '</td>' +
                '<td>' + (v.Estado || '') + '</td>' +
                '<td>' + row[4] + '</td>' +
              '</tr>';
              tbody.append(tr);
              if (String(v.Estado || '').toUpperCase() === 'PENDIENTE') {
                const st = parseFloat(row[4]);
                if (!isNaN(st)) sumaPendientes += st;
              }
            });
          }
        })
        .always(function(){
          pending--;
          if (pending === 0){
            if (tbody.children().length === 0){
              tbody.html('<tr><td colspan="5" class="text-center">Sin detalles de productos para esta recepción.</td></tr>');
            }
            dfd.resolve({ sumaPendientes: sumaPendientes });
          }
        });
    });
    return dfd.promise();
  }

  $.when(cargarRecepcion(recId), listarVentasPorRecepcion(recId))
    .then(function(recData, ventas){
      if (recData){
        $('#txtcosto').val(num2(recData.PrecioInicial));
        $('#Adelanto').val(num2(recData.Adelanto));
        
        // Mostrar fechas de entrada y salida
        $('#fecha_entrada').text(recData.FechaEntrada || recData.fecha_entrada || recData.Entrada || '');
        $('#fecha_salida').text(recData.FechaSalida || recData.fecha_salida || recData.Salida || '');
        
        var pi = parseFloat(recData.PrecioInicial);
        var ad = parseFloat(recData.Adelanto);
        var restanteCalc = 0;
        if (!isNaN(pi) || !isNaN(ad)) {
          restanteCalc = (isNaN(pi) ? 0 : pi) - (isNaN(ad) ? 0 : ad);
        }
        $('#txtcantidadrestante').val(num2(restanteCalc));
        window._restanteSalida = restanteCalc;
        cargarHabitacionYCliente(recData.IdHabitacion, recData.IdCliente);
      }
      return cargarYRenderizarDetalles(Array.isArray(ventas) ? ventas : []);
    })
    .then(function(res){
      window._sumaPendSalida = parseFloat(res.sumaPendientes);
      var penal = parseFloat($('#costo_penalidad').val());
      var restante = parseFloat($('#txtcantidadrestante').val());
      var sumaPend = window._sumaPendSalida;
      var total = (isNaN(restante) ? 0 : restante) + (isNaN(sumaPend) ? 0 : sumaPend) + (isNaN(penal) ? 0 : penal);
      $('#total_pagar').val(num2(total));
      $('#costo_penalidad').on('input', function(){
        var p = parseFloat($(this).val());
        var t = (isNaN(window._restanteSalida) ? 0 : window._restanteSalida) + (isNaN(window._sumaPendSalida) ? 0 : window._sumaPendSalida) + (isNaN(p) ? 0 : p);
        $('#total_pagar').val(num2(t));
      });
    })
    .catch(function(){
      $('#table_data tbody').html('<tr><td colspan="5" class="text-center">Error cargando datos.</td></tr>');
    });

  $('#btn_confirmar_salida').on('click', function(){
    var penal = parseFloat($('#costo_penalidad').val());
    var total = parseFloat($('#total_pagar').val());
    var adelanto = parseFloat($('#Adelanto').val());
    var payload = {
      rec_id: recId,
      costo_penalidad: isNaN(penal) ? 0 : penal,
      total_pagado: (isNaN(adelanto) ? 0 : adelanto) + (isNaN(total) ? 0 : total),
      fecha_confirmacion: new Date().toISOString().slice(0,19).replace('T',' ')
    };
    $.post('../../controller/recepcion.php?op=confirmar_salida', payload)
      .done(function(resp){
        try { 
          var r = typeof resp === 'string' ? JSON.parse(resp) : resp; 
          if (r && r.success) { 
            // Abrir el modal de comprobante
            $('#modal-comprobante').modal('show');
            return; 
          } 
        } catch(e) {}
        // Si hay error, abrir el modal de todas formas
        $('#modal-comprobante').modal('show');
      })
      .fail(function(){
        alert('Error al confirmar la salida');
      });
  });

  // Evento cuando se cierra el modal de comprobante
  $('#modal-comprobante').on('hidden.bs.modal', function(){
    window.location.href = '../ListRecepcion/index.php';
  });

  // Generar factura electrónica
  $(document).on('click', '#btn_generar_factura', function(){
    var btn = $(this);
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Generando...');
    
    var tipoDoc = $('#tipo_comprobante').val();
    
    $.post('../../controller/boleta.php?op=generar_boleta', {
      rec_id: recId,
      tipo_doc: tipoDoc
    })
    .done(function(resp){
      try {
        var r = typeof resp === 'string' ? JSON.parse(resp) : resp;
        if (r && r.success) {
          $('#mensaje_factura')
            .removeClass('alert-danger')
            .addClass('alert alert-success')
            .html('<i class="ri-checkbox-circle-line"></i> Comprobante generado exitosamente. Hash: ' + (r.hash || 'N/A'))
            .show();
          
          // Habilitar descarga del PDF después de 2 segundos
          setTimeout(function(){
            btn.html('<i class="ri-download-line"></i> Descargar PDF');
            btn.prop('disabled', false);
            btn.off('click').on('click', function(){
              window.open('../../controller/boleta.php?op=generar_pdf&rec_id=' + recId, '_blank');
            });
          }, 2000);
        } else {
          $('#mensaje_factura')
            .removeClass('alert-success')
            .addClass('alert alert-danger')
            .html('<i class="ri-error-warning-line"></i> Error: ' + (r.error || r.message || 'Error desconocido'))
            .show();
          btn.html(originalHtml).prop('disabled', false);
        }
      } catch(e) {
        $('#mensaje_factura')
          .removeClass('alert-success')
          .addClass('alert alert-danger')
          .html('<i class="ri-error-warning-line"></i> Error al procesar respuesta')
          .show();
        btn.html(originalHtml).prop('disabled', false);
      }
    })
    .fail(function(){
      $('#mensaje_factura')
        .removeClass('alert-success')
        .addClass('alert alert-danger')
        .html('<i class="ri-error-warning-line"></i> Error de conexión con el servidor')
        .show();
      btn.html(originalHtml).prop('disabled', false);
    });
  });

  // Descargar PDF con formato seleccionado
  $(document).on('click', '#btn_descargar_pdf', function(){
    var formato = $('#formato_impresion').val();
    window.open('../../controller/boleta.php?op=generar_pdf&rec_id=' + recId + '&tipo=' + formato, '_blank');
  });
});