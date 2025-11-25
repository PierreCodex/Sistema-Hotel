<?php
require_once("../config/conexion.php");
require_once(__DIR__ . '/../vendor/autoload.php');

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;

class Boleta extends Conectar {
    
    /**
     * Generar Boleta electrónica
     */
    public function generarBoleta($rec_id, $cliente_data, $detalles, $totales, $tipo_doc = '03') {
        try {
            $see = $this->configurarSee();
            
            // Cliente
            $client = $this->crearCliente($cliente_data);
            
            // Emisor (tu empresa)
            $company = $this->crearEmpresa();
            
            // Crear comprobante (Boleta o Factura)
            $invoice = $this->crearInvoice($rec_id, $company, $client, $totales, $tipo_doc);
            
            // Agregar detalles
            $items = $this->crearDetalles($detalles);
            $invoice->setDetails($items);
            
            // Leyenda (monto en letras)
            $legend = new Legend();
            $legend->setCode('1000')
                   ->setValue($this->numeroALetras($totales['total']));
            $invoice->setLegends([$legend]);
            
            // Enviar a SUNAT
            $result = $see->send($invoice);
            
            // Guardar XML firmado digitalmente
            $xml = $see->getFactory()->getLastXml();
            
            // Verificamos que la conexión con SUNAT fue exitosa
            if (!$result->isSuccess()) {
                // Error al conectarse a SUNAT
                $error = $result->getError();
                return [
                    'success' => false,
                    'error_code' => $error->getCode(),
                    'error_message' => $error->getMessage()
                ];
            }
            
            // Obtener CDR
            $cdr = $result->getCdrResponse();
            $cdrZip = $result->getCdrZip();
            
            // Verificar el estado del comprobante
            $code = (int)$cdr->getCode();
            
            if ($code === 0) {
                // ACEPTADA
                $estado = 'ACEPTADA';
                $observaciones = count($cdr->getNotes()) > 0 ? $cdr->getNotes() : null;
            } else if ($code >= 2000 && $code <= 3999) {
                // RECHAZADA
                $estado = 'RECHAZADA';
                $observaciones = null;
            } else {
                // Código inesperado
                $estado = 'EXCEPCION';
                $observaciones = null;
            }
            
            // Guardar en base de datos
            $this->guardarBoletaCompleta($rec_id, $invoice, $xml, $cdrZip, $estado, $cdr->getDescription(), $cliente_data, $totales, $detalles);
            
            return [
                'success' => true,
                'estado' => $estado,
                'codigo_sunat' => $code,
                'descripcion' => $cdr->getDescription(),
                'observaciones' => $observaciones,
                'serie' => $invoice->getSerie(),
                'correlativo' => $invoice->getCorrelativo(),
                'xml' => $xml
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
    }
    
    /**
     * Configurar el servicio de Greenter
     */
    private function configurarSee() {
        $see = new See();
        $see->setCertificate(file_get_contents(__DIR__ . '/../config/certificado.pem'));
        $see->setService(SunatEndpoints::FE_BETA); // Cambiar a FE_PRODUCCION en producción
        $see->setClaveSOL('20000000001', 'MODDATOS', 'moddatos'); // Cambiar por tus credenciales
        
        return $see;
    }
    
    /**
     * Crear datos del cliente
     */
    private function crearCliente($data) {
        $client = new Client();
        $client->setTipoDoc($data['tipo_doc'] ?? '1') // 1=DNI, 6=RUC
               ->setNumDoc($data['num_doc'])
               ->setRznSocial($data['razon_social']);
        
        return $client;
    }
    
    /**
     * Crear datos de la empresa emisora
     */
    private function crearEmpresa() {
        $address = new Address();
        $address->setUbigueo('150101') // Cambiar por tu ubigeo
                ->setDepartamento('LIMA')
                ->setProvincia('LIMA')
                ->setDistrito('LIMA')
                ->setUrbanizacion('-')
                ->setDireccion('Av. Principal 123') // Cambiar por tu dirección
                ->setCodLocal('0000');
        
        $company = new Company();
        $company->setRuc('20123456789') // Cambiar por tu RUC
                ->setRazonSocial('HOTEL LAS PALMERAS SAC') // Cambiar por tu razón social
                ->setNombreComercial('HOTEL LAS PALMERAS')
                ->setAddress($address);
        
        return $company;
    }
    
    /**
     * Crear objeto Invoice (Boleta o Factura)
     */
    private function crearInvoice($rec_id, $company, $client, $totales, $tipo_doc = '03') {
        // Determinar serie según tipo de documento
        $serie = ($tipo_doc == '01') ? 'F001' : 'B001';
        
        // Obtener siguiente número de correlativo
        $correlativo = $this->obtenerSiguienteCorrelativo($serie);
        
        $invoice = new Invoice();
        $invoice->setUblVersion('2.1')
                ->setTipoOperacion('0101')
                ->setTipoDoc($tipo_doc) // 01=Factura, 03=Boleta
                ->setSerie($serie)
                ->setCorrelativo($correlativo)
                ->setFechaEmision(new DateTime())
                ->setTipoMoneda('PEN')
                ->setCompany($company)
                ->setClient($client)
                ->setMtoOperGravadas($totales['subtotal'])
                ->setMtoIGV($totales['igv'])
                ->setTotalImpuestos($totales['igv'])
                ->setValorVenta($totales['subtotal'])
                ->setSubTotal($totales['total'])
                ->setMtoImpVenta($totales['total']);
        
        return $invoice;
    }
    
    /**
     * Crear detalles de la boleta
     */
    private function crearDetalles($detalles) {
        $items = [];
        $counter = 1;
        
        foreach ($detalles as $detalle) {
            $item = new SaleDetail();
            $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
            $igv = $subtotal * 0.18;
            
            $item->setCodProducto('P' . str_pad($counter, 3, '0', STR_PAD_LEFT))
                 ->setUnidad('NIU')
                 ->setCantidad($detalle['cantidad'])
                 ->setMtoValorUnitario($detalle['precio_unitario'])
                 ->setDescripcion($detalle['descripcion'])
                 ->setMtoBaseIgv($subtotal)
                 ->setPorcentajeIgv(18.00)
                 ->setIgv($igv)
                 ->setTipAfeIgv('10')
                 ->setTotalImpuestos($igv)
                 ->setMtoValorVenta($subtotal)
                 ->setMtoPrecioUnitario($detalle['precio_unitario'] * 1.18);
            
            $items[] = $item;
            $counter++;
        }
        
        return $items;
    }
    
    /**
     * Obtener siguiente correlativo
     */
    private function obtenerSiguienteCorrelativo($serie) {
        $conectar = parent::conexion();
        parent::set_names();
        
        $sql = "SELECT COALESCE(MAX(bol_correlativo), 0) + 1 as siguiente 
                FROM boleta 
                WHERE bol_serie = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->execute([$serie]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return str_pad($result['siguiente'], 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Guardar boleta completa con CDR de SUNAT
     */
    private function guardarBoletaCompleta($rec_id, $invoice, $xml, $cdrZip, $estado, $descripcion, $cliente_data, $totales, $detalles) {
        $conectar = parent::conexion();
        parent::set_names();
        
        // Insertar boleta
        $sql = "INSERT INTO boleta (rec_id, bol_tipo, bol_serie, bol_correlativo, 
                bol_fecha_emision, bol_cliente_tipo_doc, bol_cliente_num_doc, 
                bol_cliente_razon_social, bol_cliente_direccion,
                bol_subtotal, bol_igv, bol_total, bol_estado, bol_xml, bol_cdr, bol_observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conectar->prepare($sql);
        $stmt->execute([
            $rec_id,
            $invoice->getTipoDoc(),
            $invoice->getSerie(),
            $invoice->getCorrelativo(),
            $invoice->getFechaEmision()->format('Y-m-d H:i:s'),
            $cliente_data['tipo_doc'] ?? '1',
            $cliente_data['num_doc'] ?? '',
            $cliente_data['razon_social'] ?? '',
            $cliente_data['direccion'] ?? '',
            $totales['subtotal'] ?? 0,
            $totales['igv'] ?? 0,
            $invoice->getMtoImpVenta(),
            $estado,
            $xml,
            base64_encode($cdrZip),
            $descripcion
        ]);
        
        // Obtener el ID de la boleta insertada
        $bol_id = $conectar->lastInsertId();
        
        // Insertar detalles de la boleta
        $sql_detalle = "INSERT INTO boleta_detalle (bol_id, bol_det_orden, bol_det_codigo, 
                        bol_det_descripcion, bol_det_unidad, bol_det_cantidad, 
                        bol_det_precio_unitario, bol_det_subtotal, bol_det_igv, bol_det_total) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_detalle = $conectar->prepare($sql_detalle);
        
        $orden = 1;
        foreach ($detalles as $item) {
            $cantidad = $item['cantidad'] ?? 1;
            $precio_unitario = $item['precio_unitario'] ?? 0;
            $subtotal_item = $cantidad * $precio_unitario;
            $igv_item = round($subtotal_item * 0.18, 2);
            $total_item = round($subtotal_item + $igv_item, 2);
            
            $stmt_detalle->execute([
                $bol_id,
                $orden,
                'P' . str_pad($orden, 3, '0', STR_PAD_LEFT),
                $item['descripcion'] ?? 'Servicio',
                'NIU',
                $cantidad,
                round($precio_unitario, 2),
                round($subtotal_item, 2),
                $igv_item,
                $total_item
            ]);
            
            $orden++;
        }
    }
    
    /**
     * Generar PDF del comprobante
     */
    public function generarPDF($rec_id, $tipo = 'ticket') {
        try {
            // Obtener datos de la boleta desde BD
            $conexion = parent::conexion();
            
            $sql = "SELECT b.*, r.TotalPagado, r.Adelanto, r.IdCliente, r.IdHabitacion
                    FROM boleta b
                    INNER JOIN recepcion r ON b.rec_id = r.IdRecepcion
                    WHERE b.rec_id = ?
                    ORDER BY b.bol_id DESC
                    LIMIT 1";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$rec_id]);
            $boleta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$boleta) {
                throw new Exception("No se encontró comprobante para esta recepción");
            }
            
            // Obtener cliente directamente desde BD
            $cliente_sql = "SELECT IdCliente, TipoDocumento, Documento, Nombre, Apellido, Direccion 
                           FROM cliente WHERE IdCliente = ?";
            $cliente_stmt = $conexion->prepare($cliente_sql);
            $cliente_stmt->execute([$boleta['IdCliente']]);
            $cliente_bd = $cliente_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cliente_bd) {
                throw new Exception("No se encontró información del cliente");
            }
            
            // Preparar datos para la plantilla
            $empresa = [
                'razon_social' => 'HOTEL LAS PALMERAS SAC',
                'ruc' => '20123456789',
                'direccion' => 'Av. Principal 123, Lima - Lima - Perú',
                'telefono' => '01-1234567',
                'email' => 'ventas@hotelpalmeras.com'
            ];
            
            $cliente = [
                'razon_social' => $boleta['bol_cliente_razon_social'] ?? trim(($cliente_bd['Nombre'] ?? '') . ' ' . ($cliente_bd['Apellido'] ?? '')),
                'tipo_doc' => $boleta['bol_cliente_tipo_doc'] ?? ($cliente_bd['TipoDocumento'] == 1 ? '1' : '6'),
                'num_doc' => $boleta['bol_cliente_num_doc'] ?? ($cliente_bd['Documento'] ?? ''),
                'direccion' => $boleta['bol_cliente_direccion'] ?? ($cliente_bd['Direccion'] ?? '')
            ];
            
            // Usar los montos guardados en la boleta (ya calculados correctamente)
            $total = $boleta['bol_total'];
            $subtotal = $boleta['bol_subtotal'];
            $igv = $boleta['bol_igv'];
            
            // Obtener detalles de la boleta desde la BD
            $det_sql = "SELECT * FROM boleta_detalle WHERE bol_id = ? ORDER BY bol_det_orden";
            $det_stmt = $conexion->prepare($det_sql);
            $det_stmt->execute([$boleta['bol_id']]);
            $detalles_bd = $det_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si hay detalles guardados, usarlos; si no, crear uno genérico
            if (!empty($detalles_bd)) {
                $detalles = [];
                foreach ($detalles_bd as $det) {
                    $detalles[] = [
                        'cantidad' => $det['bol_det_cantidad'],
                        'descripcion' => $det['bol_det_descripcion'],
                        'precio_unitario' => $det['bol_det_precio_unitario'],
                        'codigo' => $det['bol_det_codigo']
                    ];
                }
            } else {
                // Fallback: obtener número de habitación
                $hab_sql = "SELECT Numero FROM habitacion WHERE IdHabitacion = ?";
                $hab_stmt = $conexion->prepare($hab_sql);
                $hab_stmt->execute([$boleta['IdHabitacion']]);
                $habitacion = $hab_stmt->fetch(PDO::FETCH_ASSOC);
                
                $detalles = [[
                    'cantidad' => 1,
                    'descripcion' => 'Hospedaje - Habitación ' . ($habitacion['Numero'] ?? 'N/A'),
                    'precio_unitario' => $subtotal,
                    'codigo' => 'P001'
                ]];
            }
            
            $totales = [
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ];
            
            // Datos adicionales
            $tipo_documento = $boleta['bol_tipo'];
            $serie = $boleta['bol_serie'];
            $correlativo = $boleta['bol_correlativo'];
            $fecha_emision = date('d/m/Y H:i:s', strtotime($boleta['bol_fecha_emision']));
            $total_letras = $this->numeroALetras($total);
            $forma_pago = 'CONTADO';
            $metodo_pago = 'EFECTIVO';
            $url_consulta = 'sunat.gob.pe';
            $hash_cpe = $boleta['bol_hash'] ?? '';
            
            // Generar código QR
            $qr_text = sprintf(
                "%s|%s|%s|%s|%s|%s|%s|%s",
                $empresa['ruc'],
                $tipo_documento,
                $serie,
                $correlativo,
                number_format($igv, 2),
                number_format($total, 2),
                date('Y-m-d', strtotime($boleta['bol_fecha_emision'])),
                $cliente['tipo_doc'] . $cliente['num_doc']
            );
            
            // QR deshabilitado temporalmente
            $qr_data = null;
            
            // Determinar formato y dimensiones
            $formatos = [
                '50mm' => ['ancho' => 155, 'plantilla' => 'ticket-50mm.php'],
                '80mm' => ['ancho' => 240, 'plantilla' => 'ticket-80mm.php'],
                'ticket' => ['ancho' => 240, 'plantilla' => 'ticket-80mm.php']
            ];
            
            $formato = $formatos[$tipo] ?? $formatos['80mm'];
            
            // Cargar plantilla
            ob_start();
            include(__DIR__ . '/../view/pdf/' . $formato['plantilla']);
            $html = ob_get_clean();
            
            // Generar PDF con dompdf
            $dompdf = new \Dompdf\Dompdf([
                'enable_remote' => true,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true
            ]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper([0, 0, $formato['ancho'], 1000], 'portrait');
            $dompdf->render();
            
            return $dompdf->stream($serie . '-' . $correlativo . '.pdf', ['Attachment' => false]);
            
        } catch (Exception $e) {
            throw new Exception("Error al generar PDF: " . $e->getMessage());
        }
    }
    
    /**
     * Convertir número a letras
     */
    private function numeroALetras($numero) {
        $entero = floor($numero);
        $decimales = round(($numero - $entero) * 100);
        
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        
        if ($entero == 0) return "CERO CON $decimales/100 SOLES";
        if ($entero == 100) return "CIEN CON $decimales/100 SOLES";
        
        $resultado = '';
        
        // Centenas
        if ($entero >= 100) {
            $c = floor($entero / 100);
            $resultado .= $centenas[$c] . ' ';
            $entero = $entero % 100;
        }
        
        // Decenas especiales (10-19)
        if ($entero >= 10 && $entero <= 19) {
            $resultado .= $especiales[$entero - 10];
        } else {
            // Decenas normales
            if ($entero >= 20) {
                $d = floor($entero / 10);
                $resultado .= $decenas[$d];
                $entero = $entero % 10;
                if ($entero > 0) $resultado .= ' Y ';
            }
            // Unidades
            if ($entero > 0 && $entero < 10) {
                $resultado .= $unidades[$entero];
            }
        }
        
        return trim($resultado) . " CON $decimales/100 SOLES";
    }
}
?>
