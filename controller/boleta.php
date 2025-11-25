<?php
// Ocultar warnings deprecated de bibliotecas de terceros
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 1);

require_once("../config/conexion.php");
require_once("../models/Boleta.php");
require_once("../models/Recepcion.php");
require_once("../models/Cliente.php");
require_once("../models/Venta.php");

$boleta = new Boleta();
$recepcion = new Recepcion();
$cliente = new Cliente();
$venta = new Venta();

$op = isset($_GET["op"]) ? $_GET["op"] : '';

switch ($op) {
    
    case "generar_boleta":
        header('Content-Type: application/json');
        try {
            $rec_id = isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0;
            $tipo_doc = isset($_POST['tipo_doc']) ? $_POST['tipo_doc'] : '03'; // 03=Boleta
            
            if ($rec_id <= 0) {
                echo json_encode(["success" => false, "message" => "ID de recepción inválido"]);
                break;
            }
            
            // Obtener datos de la recepción
            $recData = $recepcion->get_recepcion_x_id($rec_id);
            
            if (!$recData) {
                echo json_encode(["success" => false, "message" => "Recepción no encontrada"]);
                break;
            }
            
            // Obtener datos del cliente
            $clienteResult = $cliente->get_cliente_x_cli_id($recData['IdCliente']);
            
            if (empty($clienteResult)) {
                echo json_encode(["success" => false, "message" => "Cliente no encontrado"]);
                break;
            }
            
            $clienteData = $clienteResult[0]; // Obtener primer elemento del array
            
            // DEBUG: Ver qué campos devuelve realmente
            // echo json_encode(["debug" => "Campos del cliente", "data" => $clienteData]); exit;
            
            // Preparar datos del cliente para la boleta
            $numDoc = $clienteData['cli_doc'] ?? $clienteData['CLI_DOC'] ?? $clienteData['Documento'] ?? '';
            $nombre = ($clienteData['cli_nom'] ?? $clienteData['CLI_NOM'] ?? $clienteData['Nombre'] ?? '') . ' ' . 
                      ($clienteData['cli_ape'] ?? $clienteData['CLI_APE'] ?? $clienteData['Apellido'] ?? '');
            $direccion = $clienteData['cli_direcc'] ?? $clienteData['CLI_DIR'] ?? $clienteData['Direccion'] ?? '';
            
            $cliente_boleta = [
                'tipo_doc' => '1', // DNI siempre para boleta
                'num_doc' => $numDoc,
                'razon_social' => trim($nombre),
                'direccion' => $direccion
            ];
            
            // Preparar detalles (hospedaje + consumos)
            $detalles = [];
            
            // Agregar hospedaje (precio sin IGV)
            $subtotal_hospedaje = $recData['PrecioInicial'] / 1.18;
            $numHabitacion = $recData['HAB_NUM'] ?? $recData['hab_num'] ?? $recData['NumeroHabitacion'] ?? 'N/A';
            $nombreTarifa = $recData['TARIFA_DESC'] ?? $recData['tarifa_desc'] ?? '';
            
            // Construir descripción con habitación y tarifa
            $descripcionHospedaje = 'Hospedaje - Habitación ' . $numHabitacion;
            if (!empty($nombreTarifa)) {
                $descripcionHospedaje .= ' (' . $nombreTarifa . ')';
            }
            
            $detalles[] = [
                'descripcion' => $descripcionHospedaje,
                'cantidad' => 1,
                'precio_unitario' => $subtotal_hospedaje
            ];
            
            // Calcular totales iniciales (solo hospedaje)
            $subtotal = $subtotal_hospedaje;
            
            // Agregar penalidad si existe
            if (isset($recData['costo_penalidad']) && $recData['costo_penalidad'] > 0) {
                $penalidad_subtotal = $recData['costo_penalidad'] / 1.18;
                $detalles[] = [
                    'descripcion' => 'Penalidad por retraso',
                    'cantidad' => 1,
                    'precio_unitario' => $penalidad_subtotal
                ];
                $subtotal += $penalidad_subtotal;
            }
            
            // Agregar consumos/productos de la habitación (ventas)
            $ventas = $venta->get_ventas_x_recepcion($rec_id);
            foreach ($ventas as $v) {
                if ($v['Estado'] != 'ANULADO') {
                    $detalles_venta = $venta->get_detalle_venta_x_id_venta($v['IdVenta']);
                    foreach ($detalles_venta as $dv) {
                        // El precio ya incluye IGV, calcular sin IGV
                        $precio_sin_igv = $dv['PROD_PVENTA'] / 1.18;
                        $detalles[] = [
                            'descripcion' => $dv['PRO_NOM'],
                            'cantidad' => $dv['DETV_CANT'],
                            'precio_unitario' => $precio_sin_igv
                        ];
                        $subtotal += ($precio_sin_igv * $dv['DETV_CANT']);
                    }
                }
            }
            
            // Calcular IGV y total
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);
            $subtotal = round($subtotal, 2);
            
            $totales = [
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ];
            
            // Generar Boleta electrónica
            $resultado = $boleta->generarBoleta($rec_id, $cliente_boleta, $detalles, $totales, $tipo_doc);
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false, 
                "message" => "Error al generar boleta: " . $e->getMessage()
            ]);
        }
        break;
    
    case "generar_pdf":
        // Generar PDF del comprobante
        try {
            $rec_id = isset($_GET['rec_id']) ? intval($_GET['rec_id']) : 0;
            $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'ticket';
            
            if ($rec_id <= 0) {
                echo "ID de recepción inválido";
                exit;
            }
            
            // Generar y mostrar PDF
            $boleta->generarPDF($rec_id, $tipo);
            
        } catch (Exception $e) {
            echo "Error al generar PDF: " . $e->getMessage();
        }
        break;
    
    case "listar_boletas":
        header('Content-Type: application/json');
        try {
            $rec_id = isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0;
            
            // Aquí implementarías el método para listar boletas
            // $boletas = $boleta->listar_por_recepcion($rec_id);
            
            echo json_encode([
                "success" => true,
                "data" => []
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
        break;
    
    default:
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "Operación no soportada"]);
        break;
}
?>
