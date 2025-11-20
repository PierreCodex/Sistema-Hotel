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

  // Cargar la venta de la recepción que tenga detalles; si ninguna tiene, usar la más reciente
  function cargarVentaConDetallesPorRecepcion(recId){
    return $.post('../../controller/venta.php?op=listar_por_recepcion', { rec_id: recId })
      .then(function(resp){
        console.log('Respuesta ventas cruda:', resp);
        let parsed = resp;
        if (typeof resp === 'string') {
          try { parsed = JSON.parse(resp); } catch(e) {}
        }
        console.log('Ventas parseadas:', parsed);
        const ventas = (parsed && parsed.success && Array.isArray(parsed.data)) ? parsed.data : [];
        if (ventas.length === 0) return null;

        // Buscar secuencialmente la primera venta con detalles
        const dfd = $.Deferred();
        (function iter(i){
          if (i >= ventas.length) {
            // Ninguna con detalles: devolver la más reciente
            dfd.resolve(ventas[0]);
            return;
          }
          const v = ventas[i];
          $.post('../../controller/venta.php?op=listardetalle', { vent_id: v.IdVenta })
            .then(function(r){
              let d = r;
              if (typeof r === 'string') {
                try { d = JSON.parse(r); } catch(e) {}
              }
              if (d && Array.isArray(d.aaData) && d.aaData.length > 0){
                dfd.resolve(v);
              } else {
                iter(i+1);
              }
            })
            .catch(function(){
              iter(i+1);
            });
        })(0);
        return dfd.promise();
      });
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

  function cargarDetalle(ventId, estadoVenta){
    return $.post('../../controller/venta.php?op=listardetalle', { vent_id: ventId })
      .then(function(resp){
        console.log('Respuesta detalles cruda:', resp);
        let data = resp;
        if (typeof resp === 'string') {
          try { data = JSON.parse(resp); } catch(e) {}
        }
        console.log('Detalles parseados:', data);
        const tbody = $('#table_data tbody');
        tbody.empty();
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
        } else {
          tbody.html('<tr><td colspan="5" class="text-center">Sin detalles de productos para esta venta.</td></tr>');
        }
      });
  }

  let ventaEstadoActual = '';

  $.when(cargarRecepcion(recId), cargarVentaConDetallesPorRecepcion(recId))
    .then(function(recData, ventaCab){
      // Poblar campos de recepción
      if (recData) {
        console.log('Datos de recepción:', recData);
        const feOut = recData.FechaSalida || '';
        $('#fecha_salida').val(feOut);
        // Moneda: los inputs ya tienen prefijo S/. en el grupo
        function num(val){
          const n = parseFloat(val);
          return isNaN(n) ? '' : n.toFixed(2);
        }
        console.log('PrecioInicial:', recData.PrecioInicial);
        console.log('Adelanto:', recData.Adelanto);
        $('#txtcosto').val(num(recData.PrecioInicial));
        $('#Adelanto').val(num(recData.Adelanto));
        // Restante basado solo en costo inicial y adelanto
        var restanteCalc = 0;
        var pi = parseFloat(recData.PrecioInicial);
        var ad = parseFloat(recData.Adelanto);
        if (!isNaN(pi) || !isNaN(ad)) {
          restanteCalc = (isNaN(pi) ? 0 : pi) - (isNaN(ad) ? 0 : ad);
        }
        console.log('Cantidad Restante calculada:', restanteCalc);
        $('#txtcantidadrestante').val(num(restanteCalc));
        // Cargar resumen de habitación y cliente desde recepción
        cargarHabitacionYCliente(recData.IdHabitacion, recData.IdCliente);
      } else {
        console.log('No se recibieron datos de recepción');
      }

      // Poblar detalles de venta si existe
      if (ventaCab) {
        ventaEstadoActual = ventaCab.Estado || '';
        return $.when(
          cargarDetalle(ventaCab.IdVenta, ventaCab.Estado || ''),
          $.post('../../controller/venta.php?op=calculo', { vent_id: ventaCab.IdVenta }).then(function(resp){
            // Totales de venta en consola (si se requiere)
            // let data = {}; try { data = JSON.parse(resp); } catch(e) {}
          })
        );
      } else {
        $('#table_data tbody').html('<tr><td colspan="5" class="text-center">Sin venta registrada o sin detalles para esta recepción.</td></tr>');
      }
    })
    .catch(function(err){
      console.error('Error cargando DetalleRecepcion:', err);
    });
  console.log('Carga de DetalleRecepcion completada');
});