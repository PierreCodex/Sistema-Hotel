<?php
/**
 * Modelo Factura - Facturación Electrónica SUNAT
 * Tipo de documento: 01 (Factura)
 * Cliente: RUC (empresas)
 * Serie: F001, F002, etc.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Sale\Cuota;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;

class Factura extends Conectar
{
    private $see;
    private $empresa;
    private $usuario_id;
    private $metodo_pago;
    
    // Configuración de la empresa emisora
    private $config = [
        'ruc' => '20123456789',
        'razon_social' => 'HOTEL LAS PALMERAS SAC',
        'nombre_comercial' => 'HOTEL LAS PALMERAS',
        'direccion' => 'Av. Principal 123',
        'ubigeo' => '150101',
        'departamento' => 'LIMA',
        'provincia' => 'LIMA',
        'distrito' => 'LIMA',
        'urbanizacion' => '',
        'telefono' => '01-1234567',
        'email' => 'ventas@hotelpalmeras.com'
    ];
    
    public function __construct()
    {
        $this->initGreenter();
    }
    
    /**
     * Inicializar Greenter con certificado y configuración
     */
    private function initGreenter()
    {
        $this->see = new See();
        
        // Cargar certificado digital
        $certPath = __DIR__ . '/../config/certificado.pem';
        if (file_exists($certPath)) {
            $this->see->setCertificate(file_get_contents($certPath));
        }
        
        // Configurar credenciales SOL (usuario secundario SUNAT)
        $this->see->setService(SunatEndpoints::FE_BETA); // Cambiar a FE_PRODUCCION en producción
        $this->see->setClaveSOL($this->config['ruc'], 'MODDATOS', 'moddatos');
        
        // Configurar empresa emisora
        $address = new Address();
        $address->setUbigueo($this->config['ubigeo'])
            ->setDepartamento($this->config['departamento'])
            ->setProvincia($this->config['provincia'])
            ->setDistrito($this->config['distrito'])
            ->setUrbanizacion($this->config['urbanizacion'])
            ->setDireccion($this->config['direccion']);
        
        $this->empresa = new Company();
        $this->empresa->setRuc($this->config['ruc'])
            ->setRazonSocial($this->config['razon_social'])
            ->setNombreComercial($this->config['nombre_comercial'])
            ->setAddress($address);
    }
    
    /**
     * Establecer el usuario que genera el comprobante
     */
    public function setUsuarioId($usuario_id)
    {
        $this->usuario_id = $usuario_id;
    }
    
    /**
     * Establecer el método de pago
     */
    public function setMetodoPago($metodo)
    {
        $this->metodo_pago = $metodo;
    }
    
    /**
     * Generar factura electrónica para una recepción
     * @param int $rec_id ID de la recepción
     * @param array $cliente_data Datos del cliente (RUC, razón social, dirección)
     * @param array $detalles Detalles de los servicios
     * @param string $forma_pago 'Contado' o 'Credito'
     * @param array $cuotas Array de cuotas si es crédito
     */
    public function generarFactura($rec_id, $cliente_data, $detalles, $forma_pago = 'Contado', $cuotas = [])
    {
        try {
            // Validar que el cliente tenga RUC
            if (empty($cliente_data['ruc']) || strlen($cliente_data['ruc']) != 11) {
                throw new Exception("Se requiere un RUC válido (11 dígitos) para emitir factura");
            }
            
            // Obtener siguiente correlativo
            $serie = 'F001';
            $correlativo = $this->obtenerSiguienteCorrelativo($serie);
            
            // Crear cliente (receptor)
            $client = new Client();
            $client->setTipoDoc('6') // 6 = RUC
                ->setNumDoc($cliente_data['ruc'])
                ->setRznSocial($cliente_data['razon_social'])
                ->setAddress((new Address())
                    ->setDireccion($cliente_data['direccion'] ?? '')
                    ->setUbigueo($cliente_data['ubigeo'] ?? '150101'));
            
            // Si tiene email, configurarlo
            if (!empty($cliente_data['email'])) {
                // El email se usará para envío posterior
            }
            
            // Calcular totales
            $totales = $this->calcularTotales($detalles);
            
            // Crear detalles de la factura
            $items = [];
            $orden = 1;
            foreach ($detalles as $item) {
                $cantidad = $item['cantidad'] ?? 1;
                $precioUnitario = $item['precio_unitario'];
                $subtotal = $cantidad * $precioUnitario;
                $igv = round($subtotal * 0.18, 2);
                $total = $subtotal + $igv;
                
                $detail = new SaleDetail();
                $detail->setCodProducto('P' . str_pad($orden, 3, '0', STR_PAD_LEFT))
                    ->setUnidad('ZZ') // Unidad de servicio
                    ->setCantidad($cantidad)
                    ->setMtoValorUnitario($precioUnitario)
                    ->setDescripcion($item['descripcion'])
                    ->setMtoBaseIgv($subtotal)
                    ->setPorcentajeIgv(18)
                    ->setIgv($igv)
                    ->setTipAfeIgv('10') // Gravado - Operación Onerosa
                    ->setTotalImpuestos($igv)
                    ->setMtoValorVenta($subtotal)
                    ->setMtoPrecioUnitario(round($precioUnitario * 1.18, 2));
                
                $items[] = $detail;
                $orden++;
            }
            
            // Crear la factura
            $invoice = new Invoice();
            $invoice->setUblVersion('2.1')
                ->setTipoOperacion('0101') // Venta interna
                ->setTipoDoc('01') // 01 = Factura
                ->setSerie($serie)
                ->setCorrelativo($correlativo)
                ->setFechaEmision(new DateTime())
                ->setTipoMoneda('PEN')
                ->setClient($client)
                ->setCompany($this->empresa)
                ->setMtoOperGravadas($totales['subtotal'])
                ->setMtoOperExoneradas(0)
                ->setMtoOperInafectas(0)
                ->setMtoIGV($totales['igv'])
                ->setTotalImpuestos($totales['igv'])
                ->setValorVenta($totales['subtotal'])
                ->setSubTotal($totales['total'])
                ->setMtoImpVenta($totales['total'])
                ->setDetails($items);
            
            // Configurar forma de pago
            if ($forma_pago === 'Credito' && !empty($cuotas)) {
                $formaPago = new FormaPagoCredito();
                $formaPago->setTipo('Credito')
                    ->setMonto($totales['total']);
                
                $cuotasObj = [];
                foreach ($cuotas as $i => $cuota) {
                    $c = new Cuota();
                    $c->setMonto($cuota['monto'])
                        ->setFechaPago(new DateTime($cuota['fecha_vencimiento']));
                    $cuotasObj[] = $c;
                }
                
                $invoice->setFormaPago($formaPago);
                $invoice->setCuotas($cuotasObj);
            } else {
                $invoice->setFormaPago(new FormaPagoContado());
            }
            
            // Agregar leyendas
            $legend = new Legend();
            $legend->setCode('1000')
                ->setValue($this->numeroALetras($totales['total']));
            $invoice->setLegends([$legend]);
            
            // Firmar y enviar a SUNAT
            $result = $this->see->send($invoice);
            
            // Obtener XML firmado
            $xml = $this->see->getFactory()->getLastXml();
            
            // Extraer hash del XML
            $hash_cpe = $this->extraerHashDelXML($xml);
            
            if ($result->isSuccess()) {
                $cdr = $result->getCdrResponse();
                $cdrZip = $result->getCdrZip();
                $estado = 'ACEPTADA';
                
                // Guardar en base de datos
                $this->guardarFacturaCompleta(
                    $rec_id, $invoice, $xml, $cdrZip, $estado, 
                    $cdr->getDescription(), $cliente_data, $totales, 
                    $detalles, $hash_cpe, $forma_pago, $cuotas
                );
                
                return [
                    'success' => true,
                    'mensaje' => 'Factura emitida correctamente',
                    'serie' => $serie,
                    'correlativo' => $correlativo,
                    'hash' => $hash_cpe,
                    'estado' => $estado,
                    'cdr' => $cdr->getDescription()
                ];
            } else {
                $estado = 'RECHAZADA';
                $mensaje = $result->getError()->getMessage();
                
                // Guardar aunque sea rechazada para tener registro
                $this->guardarFacturaCompleta(
                    $rec_id, $invoice, $xml, '', $estado,
                    $mensaje, $cliente_data, $totales,
                    $detalles, $hash_cpe, $forma_pago, []
                );
                
                return [
                    'success' => false,
                    'mensaje' => $mensaje,
                    'serie' => $serie,
                    'correlativo' => $correlativo,
                    'estado' => $estado
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error generando factura: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calcular totales de la factura
     */
    private function calcularTotales($detalles)
    {
        $subtotal = 0;
        foreach ($detalles as $item) {
            $cantidad = $item['cantidad'] ?? 1;
            $subtotal += $cantidad * $item['precio_unitario'];
        }
        
        $igv = round($subtotal * 0.18, 2);
        $total = round($subtotal + $igv, 2);
        
        return [
            'subtotal' => round($subtotal, 2),
            'igv' => $igv,
            'total' => $total
        ];
    }
    
    /**
     * Obtener siguiente correlativo para facturas
     */
    public function obtenerSiguienteCorrelativo($serie)
    {
        $conectar = parent::conexion();
        parent::set_names();
        
        $stmt = $conectar->prepare("CALL SP_FAC_OBTENER_CORRELATIVO(?)");
        $stmt->execute([$serie]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return str_pad($result['siguiente'], 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Extraer hash del XML firmado
     */
    private function extraerHashDelXML($xml)
    {
        try {
            if (preg_match('/<ds:DigestValue>([^<]+)<\/ds:DigestValue>/', $xml, $matches)) {
                return $matches[1];
            }
            if (preg_match('/<DigestValue>([^<]+)<\/DigestValue>/', $xml, $matches)) {
                return $matches[1];
            }
            return null;
        } catch (Exception $e) {
            error_log("Error extrayendo hash: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Guardar factura completa en BD
     */
    private function guardarFacturaCompleta($rec_id, $invoice, $xml, $cdrZip, $estado, $descripcion, $cliente_data, $totales, $detalles, $hash_cpe, $forma_pago, $cuotas)
    {
        try {
            $conectar = parent::conexion();
            parent::set_names();
            $conectar->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Insertar factura
            $stmt = $conectar->prepare("CALL SP_FAC_INSERTAR(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $rec_id,
                $invoice->getSerie(),
                $invoice->getCorrelativo(),
                $invoice->getFechaEmision()->format('Y-m-d H:i:s'),
                $cliente_data['ruc'],
                $cliente_data['razon_social'],
                $cliente_data['direccion'] ?? '',
                $cliente_data['ubigeo'] ?? '',
                $cliente_data['email'] ?? '',
                $totales['subtotal'], // op_gravadas
                $totales['subtotal'],
                $totales['igv'],
                $totales['total'],
                $forma_pago,
                $this->metodo_pago ?? 'EFECTIVO',
                $estado,
                $hash_cpe,
                $xml,
                $cdrZip ? base64_encode($cdrZip) : '',
                $descripcion,
                $this->usuario_id
            ]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $fac_id = $row['fac_id'] ?? null;
            $stmt->closeCursor();
            
            if (!$fac_id) {
                throw new Exception("No se pudo obtener el ID de la factura");
            }
            
            // Guardar XML
            $this->guardarXMLEnStorage($fac_id, $invoice->getSerie(), $invoice->getCorrelativo(), $xml);
            
            // Guardar CDR
            if ($cdrZip) {
                $this->guardarCDREnStorage($fac_id, $invoice->getSerie(), $invoice->getCorrelativo(), $cdrZip);
            }
            
            // Insertar detalles
            $stmt_det = $conectar->prepare("CALL SP_FAC_INSERTAR_DETALLE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $orden = 1;
            foreach ($detalles as $item) {
                $cantidad = $item['cantidad'] ?? 1;
                $precio = $item['precio_unitario'];
                $subtotal = $cantidad * $precio;
                $igv = round($subtotal * 0.18, 2);
                $total = $subtotal + $igv;
                
                $stmt_det->execute([
                    $fac_id,
                    $orden,
                    'P' . str_pad($orden, 3, '0', STR_PAD_LEFT),
                    $item['descripcion'],
                    'ZZ',
                    $cantidad,
                    $precio,
                    round($precio * 1.18, 2),
                    $subtotal,
                    $igv,
                    $total,
                    '10' // Gravado
                ]);
                $stmt_det->closeCursor();
                $orden++;
            }
            
            // Insertar cuotas si es crédito
            if ($forma_pago === 'Credito' && !empty($cuotas)) {
                $stmt_cuo = $conectar->prepare("CALL SP_FAC_INSERTAR_CUOTA(?, ?, ?, ?)");
                $num = 1;
                foreach ($cuotas as $cuota) {
                    $stmt_cuo->execute([
                        $fac_id,
                        $num,
                        $cuota['monto'],
                        $cuota['fecha_vencimiento']
                    ]);
                    $stmt_cuo->closeCursor();
                    $num++;
                }
            }
            
            error_log("Factura guardada: " . $invoice->getSerie() . "-" . $invoice->getCorrelativo());
            
        } catch (Exception $e) {
            error_log("Error guardando factura: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Guardar XML en storage
     */
    private function guardarXMLEnStorage($fac_id, $serie, $correlativo, $xml)
    {
        try {
            $year = date('Y');
            $month = date('m');
            $storageDir = __DIR__ . '/../storage/xml/facturas/' . $year . '/' . $month;
            
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $filename = $this->config['ruc'] . '-01-' . $serie . '-' . $correlativo . '.xml';
            $filepath = $storageDir . '/' . $filename;
            $relativePath = 'storage/xml/facturas/' . $year . '/' . $month . '/' . $filename;
            
            file_put_contents($filepath, $xml);
            
            // Actualizar ruta en BD
            $conectar = parent::conexion();
            $stmt = $conectar->prepare("CALL SP_FAC_ACTUALIZAR_RUTA_XML(?, ?)");
            $stmt->execute([$fac_id, $relativePath]);
            $stmt->closeCursor();
            
        } catch (Exception $e) {
            error_log("Error guardando XML factura: " . $e->getMessage());
        }
    }
    
    /**
     * Guardar CDR en storage
     */
    private function guardarCDREnStorage($fac_id, $serie, $correlativo, $cdrZip)
    {
        try {
            $year = date('Y');
            $month = date('m');
            $storageDir = __DIR__ . '/../storage/cdr/facturas/' . $year . '/' . $month;
            
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $filename = 'R-' . $this->config['ruc'] . '-01-' . $serie . '-' . $correlativo . '.zip';
            $filepath = $storageDir . '/' . $filename;
            $relativePath = 'storage/cdr/facturas/' . $year . '/' . $month . '/' . $filename;
            
            file_put_contents($filepath, $cdrZip);
            
            // Actualizar ruta en BD
            $conectar = parent::conexion();
            $stmt = $conectar->prepare("CALL SP_FAC_ACTUALIZAR_RUTA_CDR(?, ?)");
            $stmt->execute([$fac_id, $relativePath]);
            $stmt->closeCursor();
            
        } catch (Exception $e) {
            error_log("Error guardando CDR factura: " . $e->getMessage());
        }
    }
    
    /**
     * Generar PDF de la factura
     */
    public function generarPDF($rec_id, $tipo = 'a4')
    {
        try {
            $conexion = parent::conexion();
            parent::set_names();
            
            $stmt = $conexion->prepare("CALL SP_FAC_OBTENER_POR_RECEPCION(?)");
            $stmt->execute([$rec_id]);
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if (!$factura) {
                throw new Exception("No se encontró factura para esta recepción");
            }
            
            // Obtener detalles
            $det_stmt = $conexion->prepare("CALL SP_FAC_OBTENER_DETALLES(?)");
            $det_stmt->execute([$factura['fac_id']]);
            $detalles_bd = $det_stmt->fetchAll(PDO::FETCH_ASSOC);
            $det_stmt->closeCursor();
            
            // Preparar datos para plantilla
            $empresa = [
                'razon_social' => $this->config['razon_social'],
                'ruc' => $this->config['ruc'],
                'direccion' => $this->config['direccion'] . ', ' . $this->config['distrito'] . ' - ' . $this->config['provincia'] . ' - ' . $this->config['departamento'],
                'telefono' => $this->config['telefono'],
                'email' => $this->config['email']
            ];
            
            $cliente = [
                'razon_social' => $factura['fac_cliente_razon_social'],
                'tipo_doc' => '6',
                'num_doc' => $factura['fac_cliente_ruc'],
                'direccion' => $factura['fac_cliente_direccion']
            ];
            
            $detalles = [];
            foreach ($detalles_bd as $det) {
                $detalles[] = [
                    'cantidad' => $det['fac_det_cantidad'],
                    'descripcion' => $det['fac_det_descripcion'],
                    'precio_unitario' => $det['fac_det_precio_unitario'],
                    'codigo' => $det['fac_det_codigo']
                ];
            }
            
            $totales = [
                'subtotal' => $factura['fac_subtotal'],
                'igv' => $factura['fac_igv'],
                'total' => $factura['fac_total']
            ];
            
            $tipo_documento = '01';
            $serie = $factura['fac_serie'];
            $correlativo = $factura['fac_correlativo'];
            $fecha_emision = date('d/m/Y H:i:s', strtotime($factura['fac_fecha_emision']));
            $total_letras = $this->numeroALetras($factura['fac_total']);
            $forma_pago = $factura['fac_forma_pago'];
            $metodo_pago = $factura['fac_metodo_pago'];
            $hash_cpe = $factura['fac_hash'] ?? '';
            
            $encargado = trim(($factura['usuario_nombre'] ?? '') . ' ' . ($factura['usuario_apellido'] ?? ''));
            
            // Generar QR
            $qr_text = sprintf(
                "%s|%s|%s|%s|%s|%s|%s|%s",
                $empresa['ruc'],
                $tipo_documento,
                $serie,
                $correlativo,
                number_format($factura['fac_igv'], 2),
                number_format($factura['fac_total'], 2),
                date('Y-m-d', strtotime($factura['fac_fecha_emision'])),
                '6' . $cliente['num_doc']
            );
            
            // Variable para indicar que es factura (para la plantilla)
            $es_factura = true;
            $tipo_comprobante_nombre = 'FACTURA ELECTRÓNICA';
            
            // Cargar plantilla según tipo
            ob_start();
            if ($tipo === 'a4') {
                include(__DIR__ . '/../view/pdf/factura-a4.php');
                $paperSize = 'A4';
                $paperOrientation = 'portrait';
            } elseif ($tipo === '80mm') {
                include(__DIR__ . '/../view/pdf/ticket-80mm.php');
                $paperSize = [0, 0, 226.77, 800]; // 80mm ancho
                $paperOrientation = 'portrait';
            } else {
                include(__DIR__ . '/../view/pdf/ticket-50mm.php');
                $paperSize = [0, 0, 141.73, 800]; // 50mm ancho
                $paperOrientation = 'portrait';
            }
            $html = ob_get_clean();
            
            // Generar PDF
            $dompdf = new \Dompdf\Dompdf([
                'enable_remote' => true,
                'isHtml5ParserEnabled' => true
            ]);
            $dompdf->loadHtml($html);
            
            if (is_array($paperSize)) {
                $dompdf->setPaper($paperSize);
            } else {
                $dompdf->setPaper($paperSize, $paperOrientation);
            }
            $dompdf->render();
            
            // Guardar PDF
            $this->guardarPDFEnStorage($factura['fac_id'], $serie, $correlativo, $dompdf->output());
            
            return $dompdf->stream($serie . '-' . $correlativo . '.pdf', ['Attachment' => false]);
            
        } catch (Exception $e) {
            throw new Exception("Error generando PDF: " . $e->getMessage());
        }
    }
    
    /**
     * Guardar PDF en storage
     */
    private function guardarPDFEnStorage($fac_id, $serie, $correlativo, $pdfContent)
    {
        try {
            $year = date('Y');
            $month = date('m');
            $storageDir = __DIR__ . '/../storage/boletas/facturas/' . $year . '/' . $month;
            
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $filename = $serie . '-' . $correlativo . '_' . date('Ymd_His') . '.pdf';
            $filepath = $storageDir . '/' . $filename;
            $relativePath = 'storage/boletas/facturas/' . $year . '/' . $month . '/' . $filename;
            
            file_put_contents($filepath, $pdfContent);
            
            $conectar = parent::conexion();
            $stmt = $conectar->prepare("CALL SP_FAC_ACTUALIZAR_RUTA_PDF(?, ?)");
            $stmt->execute([$fac_id, $relativePath]);
            $stmt->closeCursor();
            
        } catch (Exception $e) {
            error_log("Error guardando PDF factura: " . $e->getMessage());
        }
    }
    
    /**
     * Convertir número a letras
     */
    private function numeroALetras($numero)
    {
        $formatter = new NumberFormatter('es_PE', NumberFormatter::SPELLOUT);
        $partes = explode('.', number_format($numero, 2, '.', ''));
        $entero = $formatter->format(intval($partes[0]));
        $decimal = $partes[1];
        
        return strtoupper($entero) . ' CON ' . $decimal . '/100 SOLES';
    }
    
    /**
     * Descargar XML de una factura
     */
    public function descargarXML($fac_id) {
        try {
            $conectar = parent::conexion();
            parent::set_names();
            
            $stmt = $conectar->prepare("SELECT fac_serie, fac_correlativo, fac_xml_ruta FROM factura WHERE fac_id = ?");
            $stmt->execute([$fac_id]);
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if (!$factura) {
                throw new Exception("Factura no encontrada");
            }
            
            // Obtener XML del archivo guardado
            if (empty($factura['fac_xml_ruta'])) {
                throw new Exception("XML no disponible para esta factura");
            }
            
            $filepath = __DIR__ . '/../' . $factura['fac_xml_ruta'];
            if (!file_exists($filepath)) {
                throw new Exception("Archivo XML no encontrado en el servidor");
            }
            
            $xml = file_get_contents($filepath);
            
            if (empty($xml)) {
                throw new Exception("XML vacío o corrupto");
            }
            
            $filename = $this->config['ruc'] . '-01-' . $factura['fac_serie'] . '-' . $factura['fac_correlativo'] . '.xml';
            
            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($xml));
            
            echo $xml;
            exit;
            
        } catch (Exception $e) {
            throw new Exception("Error al descargar XML: " . $e->getMessage());
        }
    }
}
