$(document).ready(function(){
  console.log('DOM cargado, iniciando carga de datos...');
  // Util: obtener parámetro de query
  function getParam(name){
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
  }

  const recId = getParam('recepcion');
  console.log('Parámetro recepción obtenido:', recId);
  if (!recId) {
    console.error('Sin parámetro recepcion en URL');
    return;
  }

  // Listar todas las ventas ligadas a la recepción (sin filtrar)
  function cargarVentasPorRecepcion(recId){
    return $.post('../../controller/venta.php?op=listar_por_recepcion', { rec_id: recId })
      .then(function(resp){
        console.log('Respuesta ventas cruda:', resp);
        let parsed = resp;
        if (typeof resp === 'string') {
          try { parsed = JSON.parse(resp); } catch(e) {}
        }
        console.log('Ventas parseadas:', parsed);
        const ventas = (parsed && parsed.success && Array.isArray(parsed.data)) ? parsed.data : [];
        return ventas;
      });
  }

  // Cargar y renderizar detalles de todas las ventas de la recepción
  function cargarDetallesDeVentas(ventas){
    const tbody = $('#table_data tbody');
    tbody.empty();
    const dfd = $.Deferred();
    let pending = ventas.length;
    if (pending === 0) {
      tbody.html('<tr><td colspan="5" class="text-center">Sin venta registrada o sin detalles para esta recepción.</td></tr>');
      dfd.resolve();
      return dfd.promise();
    }
    ventas.forEach(function(v){
      $.post('../../controller/venta.php?op=listardetalle', { vent_id: v.IdVenta })
        .then(function(resp){
          let data = resp;
          if (typeof resp === 'string') { try { data = JSON.parse(resp); } catch(e) {} }
          const rows = Array.isArray(data.aaData)
            ? data.aaData
            : (Array.isArray(data.data) ? data.data.map(function(r){
                return [r.DETV_ID, r.PRO_NOM, r.DETV_CANT, r.PROD_PVENTA, r.DETV_TOTAL];
              }) : []);
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
            });
          }
        })
        .always(function(){
          pending--;
          if (pending === 0){
            if (tbody.children().length === 0){
              tbody.html('<tr><td colspan="5" class="text-center">Sin detalles de productos para esta recepción.</td></tr>');
            }
            dfd.resolve();
          }
        });
    });
    return dfd.promise();
  }

  // Cargar detalle de la recepción por Id
  function cargarRecepcion(recId){
    return $.post('../../controller/recepcion.php?op=obtener_x_id', { rec_id: recId })
      .then(function(resp){
        console.log('Respuesta recepción cruda:', resp);
        let parsed = resp;
        // Si es string, intentar parsear
        if (typeof resp === 'string') {
          try { parsed = JSON.parse(resp); } catch(e) {}
        }
        console.log('Recepción parseada:', parsed);
        if (parsed && parsed.success && parsed.data) {
          return parsed.data;
        }
        return null;
      });
  }

  function renderResumenHabitacion(hab){
    console.log('Renderizando habitación:', hab);
    $('#txtnombre').text(hab && (hab.HAB_NUM || hab.Numero || ''));
    $('#txtdetalle').text(hab && (hab.HAB_DET || hab.Detalle || ''));
    $('#txtcategoria').text(hab && (hab.CAT_NOM || hab.CategoriaNombre || hab.Categoria || ''));
    $('#txtestado').text(hab && (hab.ESTADO_NOM || hab.EstadoNombre || hab.Estado || ''));
  }

  function renderCliente(cli){
    console.log('Renderizando cliente:', cli);
    const nombre = ((cli && (cli.CLI_NOM || cli.CLI_NOMBRE)) || '') + ' ' + ((cli && cli.CLI_APE) || '');
    $('#txtcliente').text(nombre.trim());
    $('#txtdocumento').text((cli && (cli.CLI_DOC || cli.Documento)) || '');
    $('#txtdireccion').text((cli && (cli.CLI_DIR || cli.cli_direcc || cli.Direccion)) || '');
  }

  function cargarHabitacionYCliente(habId, cliId){
    const pHab = habId
      ? $.post('../../controller/habitacion.php?op=obtener_por_id', { hab_id: habId })
      : $.Deferred().resolve(null);
    const pCli = cliId
      ? $.post('../../controller/cliente.php?op=mostrar', { cli_id: cliId })
      : $.Deferred().resolve(null);

    return $.when(pHab, pCli).then(function(habResp, cliResp){
      let habData = habResp[0];
      let cliData = cliResp[0];
      
      // Si es string, intentar parsear
      if (typeof habData === 'string') {
        try { habData = JSON.parse(habData); } catch (e) {}
      }
      if (typeof cliData === 'string') {
        try { cliData = JSON.parse(cliData); } catch (e) {}
      }
      
      console.log('Datos de habitación:', habData);
      console.log('Datos de cliente:', cliData);
      renderResumenHabitacion(habData || {});
      renderCliente(cliData || {});
    });
  }

  function cargarDetalle(ventId, estadoVenta, opts){
    const append = opts && opts.append === true;
    return $.post('../../controller/venta.php?op=listardetalle', { vent_id: ventId })
      .then(function(resp){
        console.log('Respuesta detalles cruda:', resp);
        let data = resp;
        if (typeof resp === 'string') { try { data = JSON.parse(resp); } catch(e) {} }
        console.log('Detalles parseados:', data);
        const tbody = $('#table_data tbody');
        if (!append) tbody.empty();
        const rows = Array.isArray(data.aaData)
          ? data.aaData
          : (Array.isArray(data.data) ? data.data.map(function(r){
              return [r.DETV_ID, r.PRO_NOM, r.DETV_CANT, r.PROD_PVENTA, r.DETV_TOTAL];
            }) : []);
        if (rows.length > 0){
          rows.forEach(function(row){
            const tr = '<tr>' +
              '<td>' + row[1] + '</td>' +
              '<td>' + row[2] + '</td>' +
              '<td>' + row[3] + '</td>' +
              '<td>' + (estadoVenta || '') + '</td>' +
              '<td>' + row[4] + '</td>' +
            '</tr>';
            tbody.append(tr);
          });
        } else if (!append){
          tbody.html('<tr><td colspan="5" class="text-center">Sin detalles de productos para esta venta.</td></tr>');
        }
      });
  }

  $.when(cargarRecepcion(recId), cargarVentasPorRecepcion(recId))
    .then(function(recData, ventas){
      if (recData) {
        console.log('Datos de recepción:', recData);
        const feOut = recData.FechaSalida || '';
        $('#fecha_salida').val(feOut);
        function num(val){ const n = parseFloat(val); return isNaN(n) ? '' : n.toFixed(2); }
        $('#txtcosto').val(num(recData.PrecioInicial));
        $('#Adelanto').val(num(recData.Adelanto));
        var restanteCalc = 0;
        var pi = parseFloat(recData.PrecioInicial);
        var ad = parseFloat(recData.Adelanto);
        if (!isNaN(pi) || !isNaN(ad)) {
          restanteCalc = (isNaN(pi) ? 0 : pi) - (isNaN(ad) ? 0 : ad);
        }
        $('#txtcantidadrestante').val(num(restanteCalc));
        cargarHabitacionYCliente(recData.IdHabitacion, recData.IdCliente);
      }
      if (Array.isArray(ventas) && ventas.length > 0){
        return cargarDetallesDeVentas(ventas);
      } else {
        $('#table_data tbody').html('<tr><td colspan="5" class="text-center">Sin venta registrada o sin detalles para esta recepción.</td></tr>');
      }
    })
    .catch(function(err){
      console.error('Error cargando DetalleRecepcion:', err);
    });
  console.log('Carga de DetalleRecepcion completada');
});