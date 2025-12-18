<?php
// Limpiar cualquier output previo
ob_start();

// Ocultar warnings deprecated de bibliotecas de terceros
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0); // No mostrar errores en output
ini_set('log_errors', 1);     // Pero sí logearlos

// Conexión ya maneja la sesión automáticamente
require_once("../config/conexion.php");
require_once("../models/Boleta.php");
require_once("../models/Recepcion.php");
require_once("../models/Cliente.php");
require_once("../models/Venta.php");

$boleta = new Boleta();
$recepcion = new Recepcion();
$cliente = new Cliente();
$venta = new Venta();

// Obtener ID del usuario logueado (usar IdUsuario que es como se guarda en la sesión)
$usuario_id = isset($_SESSION['IdUsuario']) ? intval($_SESSION['IdUsuario']) : null;

$op = $_GET["op"] ?? '';

switch ($op) {

    case "generar_boleta":
        // LIMPIAR cualquier output previo (warnings, deprecated, etc.)
        ob_end_clean();
        ob_start();
        
        header('Content-Type: application/json');
        error_log("POST: " . print_r($_POST, true));
        error_log("SESSION: " . print_r($_SESSION, true));
        error_log("rec_id: " . ($_POST['rec_id'] ?? 'NO DEFINIDO'));

        try {
            $rec_id = isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0;
            $tipo_doc = $_POST['tipo_doc'] ?? '03'; // 03=Boleta
            $metodo_pago = $_POST['metodo_pago'] ?? 'EFECTIVO';
            
            error_log("rec_id procesado: $rec_id, tipo_doc: $tipo_doc, metodo_pago: $metodo_pago");
            
            if ($rec_id <= 0) {
                error_log("ERROR: rec_id invalido");
                echo json_encode(["success" => false, "message" => "ID de recepción inválido"]);
                break;
            }

            // VERIFICAR SI YA EXISTE UNA BOLETA PARA ESTA RECEPCIÓN
            // Usamos el método del modelo que llama al Stored Procedure
            $boletaExistente = $boleta->verificarBoleta($rec_id);

            if ($boletaExistente) {
                echo json_encode([
                    "success" => true,
                    "message" => "Ya existe un comprobante para esta recepción",
                    "ya_existe" => true,
                    "serie" => $boletaExistente['bol_serie'],
                    "correlativo" => $boletaExistente['bol_correlativo'],
                    "estado" => $boletaExistente['bol_estado']
                ]);
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

            // Agregar hospedaje
            // Guardamos el precio ORIGINAL (el que se cobra) y el precio sin IGV para SUNAT
            $precioOriginalHospedaje = $recData['PrecioInicial'];
            $precioSinIgvHospedaje = round($precioOriginalHospedaje / 1.18, 2);
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
                'precio_unitario' => $precioSinIgvHospedaje,
                'precio_original' => $precioOriginalHospedaje // Precio real cobrado (con IGV)
            ];

            // Calcular totales iniciales (solo hospedaje)
            $subtotal = $precioSinIgvHospedaje;

            // Agregar penalidad si existe
            if (isset($recData['costo_penalidad']) && $recData['costo_penalidad'] > 0) {
                $precioOriginalPenalidad = $recData['costo_penalidad'];
                $precioSinIgvPenalidad = round($precioOriginalPenalidad / 1.18, 2);
                $detalles[] = [
                    'descripcion' => 'Penalidad por retraso',
                    'cantidad' => 1,
                    'precio_unitario' => $precioSinIgvPenalidad,
                    'precio_original' => $precioOriginalPenalidad
                ];
                $subtotal += $precioSinIgvPenalidad;
            }

            // Agregar consumos/productos de la habitación (ventas)
            $ventas = $venta->get_ventas_x_recepcion($rec_id);
            foreach ($ventas as $v) {
                if ($v['Estado'] != 'ANULADO') {
                    $detalles_venta = $venta->get_detalle_venta_x_id_venta($v['IdVenta']);
                    foreach ($detalles_venta as $dv) {
                        // El precio de venta ya incluye IGV
                        $precioOriginalProducto = $dv['PROD_PVENTA'];
                        $precioSinIgvProducto = round($precioOriginalProducto / 1.18, 2);
                        $detalles[] = [
                            'descripcion' => $dv['PRO_NOM'],
                            'cantidad' => $dv['DETV_CANT'],
                            'precio_unitario' => $precioSinIgvProducto,
                            'precio_original' => $precioOriginalProducto
                        ];
                        $subtotal += ($precioSinIgvProducto * $dv['DETV_CANT']);
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

            // Generar Boleta electrónica (incluye ID del usuario que genera)
            $resultado = $boleta->generarBoleta($rec_id, $cliente_boleta, $detalles, $totales, $tipo_doc, $metodo_pago, $usuario_id);

            // LIMPIAR cualquier output previo (warnings de Greenter)
            ob_end_clean();
            echo json_encode($resultado);
        } catch (Exception $e) {
            ob_end_clean();
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
           $tipo = $_GET['tipo'] ?? 'ticket';
           
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

    case "descargar_xml":
        // Descargar XML firmado de la boleta
        try {
            $bol_id = isset($_GET['bol_id']) ? intval($_GET['bol_id']) : 0;

            if ($bol_id <= 0) {
                echo "ID de boleta inválido";
                exit;
            }

            $boleta->descargarXML($bol_id);
        } catch (Exception $e) {
            echo "Error al descargar XML: " . $e->getMessage();
        }
        break;

    case "descargar_cdr":
        // Descargar CDR (respuesta de SUNAT) de la boleta
        try {
            $bol_id = isset($_GET['bol_id']) ? intval($_GET['bol_id']) : 0;

            if ($bol_id <= 0) {
                echo "ID de boleta inválido";
                exit;
            }

            $boleta->descargarCDR($bol_id);
        } catch (Exception $e) {
            echo "Error al descargar CDR: " . $e->getMessage();
        }
        break;

    case "descargar_pdf":
        // Descargar PDF guardado de la boleta
        try {
            $bol_id = isset($_GET['bol_id']) ? intval($_GET['bol_id']) : 0;

            if ($bol_id <= 0) {
                echo "ID de boleta inválido";
                exit;
            }

            $boleta->descargarPDF($bol_id);
        } catch (Exception $e) {
            echo "Error al descargar PDF: " . $e->getMessage();
        }
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "Operación no soportada"]);
        break;
}
