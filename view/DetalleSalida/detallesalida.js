$(document).ready(function () {
  function getParam(name) {
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
  }

  const recId = getParam('recepcion');
  if (!recId) return;

  // Variable global para el tipo de comprobante
  var tipoComprobante = '03'; // Por defecto Boleta
  var nombreComprobante = 'Boleta';

  function num2(val) {
    const n = parseFloat(val);
    return isNaN(n) ? '' : n.toFixed(2);
  }

  function cargarRecepcion(recId) {
    return $.post('../../controller/recepcion.php?op=obtener_x_id', { rec_id: recId })
      .then(function (resp) {
        let parsed = resp;
        if (typeof resp === 'string') { try { parsed = JSON.parse(resp); } catch (e) { } }
        return (parsed && parsed.success) ? parsed.data : null;
      });
  }

  function renderResumenHabitacion(hab) {
    $('#txtnombre').text(hab && (hab.HAB_NUM || hab.Numero || ''));
    $('#txtdetalle').text(hab && (hab.HAB_DET || hab.Detalle || ''));
    $('#txtcategoria').text(hab && (hab.CAT_NOM || hab.CategoriaNombre || hab.Categoria || ''));
    $('#txtestado').text(hab && (hab.ESTADO_NOM || hab.EstadoNombre || hab.Estado || ''));

  }

  function renderCliente(cli) {
    const nombre = ((cli && (cli.CLI_NOM || cli.CLI_NOMBRE)) || '') + ' ' + ((cli && cli.CLI_APE) || '');
    $('#txtcliente').text(nombre.trim());
    $('#txtdocumento').text((cli && (cli.CLI_DOC || cli.Documento)) || '');
    $('#txtdireccion').text((cli && (cli.CLI_DIR || cli.cli_direcc || cli.Direccion)) || '');

  }

  function cargarHabitacionYCliente(habId, cliId) {
    const pHab = habId ? $.post('../../controller/habitacion.php?op=obtener_por_id', { hab_id: habId }) : $.Deferred().resolve(null);
    const pCli = cliId ? $.post('../../controller/cliente.php?op=mostrar', { cli_id: cliId }) : $.Deferred().resolve(null);
    return $.when(pHab, pCli).then(function (habResp, cliResp) {
      let habData = habResp[0];
      let cliData = cliResp[0];
      if (typeof habData === 'string') { try { habData = JSON.parse(habData); } catch (e) { } }
      if (typeof cliData === 'string') { try { cliData = JSON.parse(cliData); } catch (e) { } }
      renderResumenHabitacion(habData || {});
      renderCliente(cliData || {});
    });
  }

  function listarVentasPorRecepcion(recId) {
    return $.post('../../controller/venta.php?op=listar_por_recepcion', { rec_id: recId })
      .then(function (resp) {
        let parsed = resp;
        if (typeof resp === 'string') { try { parsed = JSON.parse(resp); } catch (e) { } }
        return (parsed && parsed.success && Array.isArray(parsed.data)) ? parsed.data : [];
      });
  }

  function cargarYRenderizarDetalles(ventas) {
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
    ventas.forEach(function (v) {
      $.post('../../controller/venta.php?op=listardetalle', { vent_id: v.IdVenta })
        .then(function (resp) {
          let data = resp;
          if (typeof resp === 'string') { try { data = JSON.parse(resp); } catch (e) { } }
          const rows = Array.isArray(data.aaData) ? data.aaData : [];
          if (rows.length > 0) {
            rows.forEach(function (row) {
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
        .always(function () {
          pending--;
          if (pending === 0) {
            if (tbody.children().length === 0) {
              tbody.html('<tr><td colspan="5" class="text-center">Sin detalles de productos para esta recepción.</td></tr>');
            }
            dfd.resolve({ sumaPendientes: sumaPendientes });
          }
        });
    });
    return dfd.promise();
  }

  $.when(cargarRecepcion(recId), listarVentasPorRecepcion(recId))
    .then(function (recData, ventas) {
      if (recData) {
        $('#txtcosto').val(num2(recData.PrecioInicial));
        $('#Adelanto').val(num2(recData.Adelanto));

        // DEBUG: Ver qué datos vienen de la recepción
        console.log('Datos recepción:', recData);
        console.log('TipoComprobante raw:', recData.TipoComprobante);

        // Guardar tipo de comprobante de la recepción
        // El valor viene como '01' (Factura) o '03' (Boleta)
        tipoComprobante = (recData.TipoComprobante || '03').toString().trim();
        nombreComprobante = (tipoComprobante === '01') ? 'Factura' : 'Boleta';

        console.log('Tipo comprobante final:', tipoComprobante, '- Nombre:', nombreComprobante);

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
    .then(function (res) {
      window._sumaPendSalida = parseFloat(res.sumaPendientes);
      var penal = parseFloat($('#costo_penalidad').val());
      var restante = parseFloat($('#txtcantidadrestante').val());
      var sumaPend = window._sumaPendSalida;
      var total = (isNaN(restante) ? 0 : restante) + (isNaN(sumaPend) ? 0 : sumaPend) + (isNaN(penal) ? 0 : penal);
      $('#total_pagar').val(num2(total));
      $('#costo_penalidad').on('input', function () {
        var p = parseFloat($(this).val());
        var t = (isNaN(window._restanteSalida) ? 0 : window._restanteSalida) + (isNaN(window._sumaPendSalida) ? 0 : window._sumaPendSalida) + (isNaN(p) ? 0 : p);
        $('#total_pagar').val(num2(t));
      });
    })
    .catch(function () {
      $('#table_data tbody').html('<tr><td colspan="5" class="text-center">Error cargando datos.</td></tr>');
    });

  $('#btn_confirmar_salida').on('click', function () {
    var btn = $(this);

    // Si ya se confirmó, el botón es "Regresar"
    if (btn.data('confirmado')) {
      window.location.href = '../ListRecepcion/index.php';
      return;
    }

    var penal = parseFloat($('#costo_penalidad').val());
    var total = parseFloat($('#total_pagar').val());
    var adelanto = parseFloat($('#Adelanto').val());
    var metodoPago = $('#metodo_pago').val() || 'EFECTIVO';

    var payload = {
      rec_id: recId,
      costo_penalidad: isNaN(penal) ? 0 : penal,
      total_pagado: (isNaN(adelanto) ? 0 : adelanto) + (isNaN(total) ? 0 : total),
      fecha_confirmacion: (function () {
        var now = new Date();
        var offset = now.getTimezoneOffset() * 60000;
        var localIso = new Date(now.getTime() - offset).toISOString().slice(0, 19).replace('T', ' ');
        return localIso;
      })()
    };

    // Deshabilitar botón mientras procesa
    btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Procesando...');

    $.post('../../controller/recepcion.php?op=confirmar_salida', payload)
      .done(function (resp) {
        try {
          var r = typeof resp === 'string' ? JSON.parse(resp) : resp;
          if (r && r.success) {
            // Marcar como confirmado
            btn.data('confirmado', true);
            btn.removeClass('btn-primary').addClass('btn-secondary');
            btn.html('<i class="ri-arrow-left-line"></i> Regresar').prop('disabled', false);

            // Deshabilitar campos
            $('#costo_penalidad').prop('readonly', true);
            $('#metodo_pago').prop('disabled', true);

            // Abrir modal y generar boleta automáticamente
            $('#modal-comprobante').modal('show');
            generarBoletaAutomatica(metodoPago);
            return;
          }
        } catch (e) {
          console.error('Error parsing response:', e);
        }
        // Si hay error
        btn.html('<i class="ri-check-line"></i> Confirmar Salida').prop('disabled', false);
        alert('Error al confirmar la salida');
      })
      .fail(function () {
        btn.html('<i class="ri-check-line"></i> Confirmar Salida').prop('disabled', false);
        alert('Error al confirmar la salida');
      });
  });

  // Función para generar comprobante automáticamente (boleta o factura)
  function generarBoletaAutomatica(metodoPago) {
    // Actualizar título del modal según tipo de comprobante
    var iconoTipo = (tipoComprobante === '01') ? 'ri-file-text-line' : 'ri-file-list-3-line';
    $('#titulo_comprobante').html('<i class="' + iconoTipo + ' me-2"></i>' + nombreComprobante + ': <span id="numero_comprobante">---</span>');

    // Resetear modal a estado inicial
    $('#generando_contenido').show();
    $('#generado_contenido').hide();
    $('#error_contenido').hide();
    $('#numero_comprobante').text('---');
    $('#estado_boleta').removeClass('alert-success alert-danger').addClass('alert-info');
    $('#mensaje_estado').html('<div class="spinner-border spinner-border-sm me-2" role="status"></div> Generando ' + nombreComprobante.toLowerCase() + ' electrónica...');

    // Determinar endpoint según tipo de comprobante
    var endpoint = (tipoComprobante === '01')
      ? '../../controller/factura.php?op=emitir'
      : '../../controller/boleta.php?op=generar_boleta';

    $.post(endpoint, {
      rec_id: recId,
      tipo_doc: tipoComprobante,
      metodo_pago: metodoPago
    })
      .done(function (resp) {
        try {
          var r = typeof resp === 'string' ? JSON.parse(resp) : resp;

          if (r && r.success) {
            // Actualizar número de comprobante
            var seriePrefijo = (tipoComprobante === '01') ? 'F001' : 'B001';
            var numComprobante = (r.serie || seriePrefijo) + '-' + (r.correlativo || '00000000');
            $('#numero_comprobante').text(numComprobante);

            // Mensaje según si ya existía o se generó nueva
            var mensaje = r.ya_existe
              ? nombreComprobante + ' recuperada exitosamente'
              : (r.descripcion || r.mensaje || nombreComprobante + ' generada exitosamente');

            $('#estado_boleta').removeClass('alert-info alert-danger').addClass('alert-success');
            $('#mensaje_estado').html('<i class="ri-checkbox-circle-line me-2"></i>' + mensaje);

            // Mostrar botones de descarga
            $('#generando_contenido').hide();
            $('#generado_contenido').show();

            // Mostrar botón A4 solo para facturas
            if (tipoComprobante === '01') {
              $('#btn_a4_container').show();
            } else {
              $('#btn_a4_container').hide();
            }

          } else {
            // Error al generar - buscar mensaje en diferentes propiedades
            var errorMsg = r.mensaje || r.message || r.error || r.error_message || 'Error desconocido';
            console.log('Error factura/boleta:', r);
            mostrarErrorBoleta(errorMsg);
          }
        } catch (e) {
          console.log('Error parseando respuesta:', e, resp);
          mostrarErrorBoleta('Error al procesar respuesta del servidor');
        }
      })
      .fail(function (xhr, status, error) {
        console.log('Error AJAX:', status, error, xhr.responseText);
        mostrarErrorBoleta('Error de conexión con el servidor');
      });
  }

  // Función para mostrar error en el modal
  function mostrarErrorBoleta(mensaje) {
    $('#estado_boleta').removeClass('alert-info alert-success').addClass('alert-danger');
    $('#mensaje_estado').html('<i class="ri-error-warning-line me-2"></i> Error al generar ' + nombreComprobante.toLowerCase());
    $('#generando_contenido').hide();
    $('#error_contenido').show();
    $('#error_mensaje').text(mensaje);
  }

  // Botón reintentar
  $(document).on('click', '#btn_reintentar', function () {
    var metodoPago = $('#metodo_pago').val() || 'EFECTIVO';
    generarBoletaAutomatica(metodoPago);
  });

  // Botones de formato de descarga
  $(document).on('click', '.btn-formato', function () {
    var formato = $(this).data('formato');
    // Usar el endpoint correcto según tipo de comprobante
    var pdfEndpoint = (tipoComprobante === '01')
      ? '../../controller/factura.php?op=pdf&rec_id=' + recId + '&tipo=' + formato
      : '../../controller/boleta.php?op=generar_pdf&rec_id=' + recId + '&tipo=' + formato;
    window.open(pdfEndpoint, '_blank');
  });

  // Enviar por WhatsApp (abrir WhatsApp Web con el PDF)
  $(document).on('click', '#btn_enviar_whatsapp', function () {
    var numero = $('#whatsapp_numero').val().replace(/\D/g, '');
    if (!numero || numero.length < 9) {
      alert('Ingrese un número de WhatsApp válido');
      return;
    }
    // Por ahora solo abrimos WhatsApp, en el futuro podría enviar el PDF
    var mensaje = 'Gracias por su visita. Adjuntamos su comprobante electrónico.';
    window.open('https://wa.me/51' + numero + '?text=' + encodeURIComponent(mensaje), '_blank');
  });

  // Evento cuando se cierra el modal de comprobante - NO redirigir automáticamente
  $('#modal-comprobante').on('hidden.bs.modal', function () {
    // El usuario usa el botón "Regresar" para ir a la lista
  });
});