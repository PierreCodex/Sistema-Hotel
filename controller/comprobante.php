<?php
require_once("../config/conexion.php");
require_once("../models/Boleta.php");

/**
 * Controlador para Historial de Comprobantes
 * Las consultas SQL están en el modelo Boleta.php
 */

$boleta = new Boleta();

// Manejar peticiones POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
$operacion = $_POST['operacion'] ?? '';

    switch ($operacion) {
        case 'listar_comprobantes':
            $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
            $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
            $tipo = $_POST['tipo'] ?? '';
            $estado = $_POST['estado'] ?? '';
            
            try {
                $lista = $boleta->listarComprobantes($fecha_inicio, $fecha_fin, $tipo, $estado);
                $resumen = $boleta->obtenerResumenComprobantes($fecha_inicio, $fecha_fin, $tipo, $estado);
                
                echo json_encode([
                    'status' => true,
                    'lista' => $lista,
                    'resumen' => $resumen
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'obtener_detalle':
            $bol_id = intval($_POST['bol_id'] ?? 0);
            
            try {
                $comprobante = $boleta->obtenerComprobantePorId($bol_id);
                
                if (!$comprobante) {
                    echo json_encode(['status' => false, 'message' => 'Comprobante no encontrado']);
                    break;
                }
                
                $detalles = $boleta->obtenerDetallesComprobante($bol_id);
                
                echo json_encode([
                    'status' => true,
                    'comprobante' => $comprobante,
                    'detalles' => $detalles
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        default:
            echo json_encode(['status' => false, 'message' => 'Operación no válida']);
            break;
    }
    exit;
}

// Manejar peticiones GET (descargas y exportaciones)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
$operacion = $_GET['operacion'] ?? '';    
    switch ($operacion) {
        case 'descargar_pdf':
            $bol_id = intval($_GET['bol_id'] ?? 0);
            
            if ($bol_id <= 0) {
                die('ID de comprobante no válido');
            }
            
            // Redirigir al generador de PDF A4
            header("Location: ../view/pdf/generar-pdf-a4.php?id=" . $bol_id);
            exit;
            break;
            
        case 'descargar_xml':
            $bol_id = intval($_GET['bol_id'] ?? 0);
            
            try {
                $boleta->descargarXML($bol_id);
            } catch (Exception $e) {
                die('Error al descargar XML: ' . $e->getMessage());
            }
            break;
            
        case 'exportar_excel':
            $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
            $tipo = $_GET['tipo'] ?? '';
            $estado = $_GET['estado'] ?? '';
            
            exportarExcel($boleta, $fecha_inicio, $fecha_fin, $tipo, $estado);
            break;
            
        case 'exportar_pdf_reporte':
            $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
            $tipo = $_GET['tipo'] ?? '';
            $estado = $_GET['estado'] ?? '';
            
            exportarPDFReporte($boleta, $fecha_inicio, $fecha_fin, $tipo, $estado);
            break;
    }
    exit;
}

/**
 * Exportar a Excel (CSV)
 */
function exportarExcel($boleta, $fecha_inicio, $fecha_fin, $tipo, $estado) {
    try {
        $lista = $boleta->listarComprobantes($fecha_inicio, $fecha_fin, $tipo, $estado);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="comprobantes_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        
        // Encabezados
        fputcsv($output, [
            'Tipo', 'Serie', 'Correlativo', 'Fecha Emisión', 
            'Cliente', 'Documento', 'SubTotal', 'IGV', 'Total', 'Estado', 'Método Pago'
        ], ';');
        
        // Datos
        foreach ($lista as $item) {
            $tipoDoc = $item['bol_tipo'] == '03' ? 'Boleta' : 'Factura';
            
            fputcsv($output, [
                $tipoDoc,
                $item['bol_serie'],
                $item['bol_correlativo'],
                $item['bol_fecha_emision'],
                $item['bol_cliente_razon_social'],
                $item['bol_cliente_num_doc'],
                $item['bol_subtotal'],
                $item['bol_igv'],
                $item['bol_total'],
                $item['bol_estado'],
                $item['bol_metodo_pago']
            ], ';');
        }
        
        fclose($output);
        
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

/**
 * Exportar reporte a PDF
 */
function exportarPDFReporte($boleta, $fecha_inicio, $fecha_fin, $tipo, $estado) {
    try {
        require_once(__DIR__ . '/../vendor/autoload.php');
        
        $lista = $boleta->listarComprobantes($fecha_inicio, $fecha_fin, $tipo, $estado);
        $resumen = $boleta->obtenerResumenComprobantes($fecha_inicio, $fecha_fin, $tipo, $estado);
        
        // Crear HTML para el PDF
        $html = generarHTMLReporte($lista, $resumen, $fecha_inicio, $fecha_fin);
        
        // Generar PDF con dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $dompdf->stream('reporte_comprobantes_' . date('Y-m-d') . '.pdf', ['Attachment' => false]);
        
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

/**
 * Generar HTML para el reporte PDF
 */
function generarHTMLReporte($lista, $resumen, $fecha_inicio, $fecha_fin) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; }
            .header { text-align: center; margin-bottom: 20px; }
            .header h1 { margin: 0; color: #405189; font-size: 18px; }
            .header p { margin: 5px 0; color: #666; }
            .resumen { margin-bottom: 20px; }
            .resumen-item { background: #f8f9fa; padding: 8px; border-radius: 5px; text-align: center; width: 24%; display: inline-block; margin: 0 0.5%; }
            .resumen-item h3 { margin: 0; color: #405189; font-size: 14px; }
            .resumen-item p { margin: 3px 0 0 0; color: #666; font-size: 9px; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th { background: #405189; color: white; padding: 6px 4px; text-align: left; font-size: 9px; }
            td { border-bottom: 1px solid #ddd; padding: 5px 4px; font-size: 9px; }
            tr:nth-child(even) { background: #f8f9fa; }
            .text-right { text-align: right; }
            .estado-aceptada { color: #0ab39c; font-weight: bold; }
            .estado-rechazada { color: #f06548; font-weight: bold; }
            .estado-emitida { color: #f7b84b; font-weight: bold; }
            .footer { margin-top: 20px; text-align: center; color: #666; font-size: 9px; }
            .total-row { background: #e3f2fd !important; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>HOTEL LAS PALMERAS</h1>
            <p>Reporte de Comprobantes Electrónicos</p>
            <p>Período: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>
        </div>
        
        <div class="resumen">
            <div class="resumen-item">
                <h3>' . ($resumen['total_emitidos'] ?? 0) . '</h3>
                <p>Total Emitidos</p>
            </div>
            <div class="resumen-item">
                <h3>' . ($resumen['total_boletas'] ?? 0) . '</h3>
                <p>Boletas</p>
            </div>
            <div class="resumen-item">
                <h3>' . ($resumen['total_facturas'] ?? 0) . '</h3>
                <p>Facturas</p>
            </div>
            <div class="resumen-item">
                <h3>S/ ' . number_format($resumen['total_facturado'] ?? 0, 2) . '</h3>
                <p>Total Facturado</p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Serie-Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Doc.</th>
                    <th class="text-right">SubTotal</th>
                    <th class="text-right">IGV</th>
                    <th class="text-right">Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';
    
    $totalSubtotal = 0;
    $totalIgv = 0;
    $totalGeneral = 0;
    
    foreach ($lista as $item) {
        $tipoDoc = $item['bol_tipo'] == '03' ? 'BOL' : 'FAC';
        $estadoClass = 'estado-' . strtolower($item['bol_estado']);
        
        $totalSubtotal += floatval($item['bol_subtotal']);
        $totalIgv += floatval($item['bol_igv']);
        $totalGeneral += floatval($item['bol_total']);
        
        $html .= '
                <tr>
                    <td>' . $tipoDoc . '</td>
                    <td>' . $item['bol_serie'] . '-' . $item['bol_correlativo'] . '</td>
                    <td>' . date('d/m/Y', strtotime($item['bol_fecha_emision'])) . '</td>
                    <td>' . substr($item['bol_cliente_razon_social'] ?? 'Cliente', 0, 25) . '</td>
                    <td>' . ($item['bol_cliente_num_doc'] ?? '-') . '</td>
                    <td class="text-right">S/ ' . number_format($item['bol_subtotal'], 2) . '</td>
                    <td class="text-right">S/ ' . number_format($item['bol_igv'], 2) . '</td>
                    <td class="text-right">S/ ' . number_format($item['bol_total'], 2) . '</td>
                    <td class="' . $estadoClass . '">' . $item['bol_estado'] . '</td>
                </tr>';
    }
    
    $html .= '
                <tr class="total-row">
                    <td colspan="5" class="text-right">TOTALES:</td>
                    <td class="text-right">S/ ' . number_format($totalSubtotal, 2) . '</td>
                    <td class="text-right">S/ ' . number_format($totalIgv, 2) . '</td>
                    <td class="text-right">S/ ' . number_format($totalGeneral, 2) . '</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Reporte generado el ' . date('d/m/Y H:i:s') . '</p>
            <p>Sistema de Gestión Hotelera - Hotel Las Palmeras</p>
        </div>
    </body>
    </html>';
    
    return $html;
}
