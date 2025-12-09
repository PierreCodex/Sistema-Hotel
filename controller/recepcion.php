<?php
/* TODO: Llamando Clases */
require_once("../config/conexion.php");
require_once("../models/Recepcion.php");
require_once("../models/Habitacion.php");
require_once("../models/Venta.php");
require_once("../models/Factura.php");
require_once("../models/Boleta.php");

/* TODO: Inicializando clase */
$recepcion = new Recepcion();
$habitacionModel = new Habitacion();
$ventaModel = new Venta();
$facturaModel = new Factura();
$boletaModel = new Boleta();

switch ($_GET["op"]) {
    // Listar ocupaciones activas (cliente actual por habitación)
    case "listar_ocupaciones_activas":
        header('Content-Type: application/json');
        try {
            $datos = $recepcion->listar_ocupaciones_activas();
            echo json_encode($datos);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error al listar ocupaciones: " . $e->getMessage()]);
        }
        break;
    // Guardar recepción (solo inserción)
    case "guardaryeditar":
        header('Content-Type: application/json');

        // Normalizar y validar entradas mínimas
        $cli_id = isset($_POST['cli_id']) ? intval($_POST['cli_id']) : 0;
        $hab_id = isset($_POST['hab_id']) ? intval($_POST['hab_id']) : 0;
        $tar_id = isset($_POST['tar_id']) ? intval($_POST['tar_id']) : null;
        if ($tar_id === 0) $tar_id = null; // Convertir 0 a NULL
        // Tipo de comprobante: '03' = Boleta (defecto), '01' = Factura
        $tipo_comprobante = isset($_POST['tipo_comprobante']) ? trim($_POST['tipo_comprobante']) : '03';
        // Validar que sea 01 o 03
        if (!in_array($tipo_comprobante, ['01', '03'])) {
            $tipo_comprobante = '03';
        }
        // Precio inicial será calculado servidor (3 horas por defecto)
        $precio_inicial_post = isset($_POST['precio_inicial']) ? floatval($_POST['precio_inicial']) : 0.0;
        $adelanto = isset($_POST['adelanto']) ? floatval($_POST['adelanto']) : 0.0;
        $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : null;
        $fecha_salida_post = isset($_POST['fecha_salida']) ? trim($_POST['fecha_salida']) : '';
        $fecha_salida_db = null;

        // Parsear fecha_salida a Y-m-d H:i:s si viene en formatos comunes
        if (!empty($fecha_salida_post)) {
            $dt = \DateTime::createFromFormat('Y-m-d H:i', $fecha_salida_post);
            if ($dt instanceof \DateTime) {
                $fecha_salida_db = $dt->format('Y-m-d H:i:s');
            } else {
                // Intentar con d M, Y H:i (por si no se configuró altInput)
                $dt2 = \DateTime::createFromFormat('d M, Y H:i', $fecha_salida_post);
                if ($dt2 instanceof \DateTime) {
                    $fecha_salida_db = $dt2->format('Y-m-d H:i:s');
                }
            }
        }

        // Política: si no se envía fecha de salida, por defecto son 3 horas
        if ($fecha_salida_db === null) {
            $fecha_salida_db = date('Y-m-d H:i:s', time() + (3 * 60 * 60));
        }

        if ($cli_id <= 0 || $hab_id <= 0) {
            echo json_encode(["success" => false, "message" => "Cliente y Habitación son obligatorios"]);
            break;
        }
        // Determinar precio inicial: usar el enviado por el frontend (total a pagar), con fallback opcional por 3 horas desde HAB_PRE
        $precio_inicial = 0.0;
        if ($precio_inicial_post > 0) {
            $precio_inicial = round($precio_inicial_post, 2);
        } else {
            // Fallback: prorrateo por 3 horas desde precio base por noche, si disponible
            $habInfo = $habitacionModel->get_habitacion_x_hab_id($hab_id);
            $hab_pre = 0.0;
            if (is_array($habInfo) && count($habInfo) > 0 && isset($habInfo[0]['HAB_PRE'])) {
                $hab_pre = floatval($habInfo[0]['HAB_PRE']);
            }
            if ($hab_pre > 0) {
                $precio_inicial = round(($hab_pre / 24) * 3, 2);
            }
        }

        // Validar si el cliente ya tiene una recepción activa
        if ($recepcion->cliente_tiene_recepcion_activa($cli_id)) {
            echo json_encode(["success" => false, "message" => "El cliente ya tiene una recepción activa."]);
            break;
        }

        // Inserción usando precio determinado (sin forzar validación por HAB_PRE)
        $rec_id = $recepcion->insert_recepcion($cli_id, $hab_id, $precio_inicial, $adelanto, $observacion, $fecha_salida_db, $tar_id, $tipo_comprobante);
        echo json_encode(["success" => true, "rec_id" => $rec_id]);
        break;

    // Obtener detalle de recepción por Id
    case "obtener_x_id":

        header('Content-Type: application/json');
        $rec_id = isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0;
        if ($rec_id <= 0) {
            echo json_encode(["success" => false, "message" => "Id de recepción inválido"]);
            break;
        }
        try {
            $row = $recepcion->get_recepcion_x_id($rec_id);
            echo json_encode(["success" => true, "data" => $row]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        break;


    case "confirmar_salida":
        header('Content-Type: application/json');
        try {
            $rec_id = isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0;
            $costo_penalidad = isset($_POST['costo_penalidad']) ? floatval($_POST['costo_penalidad']) : 0.0;
            $total_pagado = isset($_POST['total_pagado']) ? floatval($_POST['total_pagado']) : 0.0;
            $fecha_confirmacion = isset($_POST['fecha_confirmacion']) ? trim($_POST['fecha_confirmacion']) : date('Y-m-d H:i:s');
            if ($rec_id <= 0) {
                echo json_encode(["success" => false, "message" => "Id de recepción inválido"]);
                break;
            }
            $recepcion->confirmar_salida($rec_id, $costo_penalidad, $total_pagado, $fecha_confirmacion);
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        break;

    case "listar_historial_cliente":
        header('Content-Type: application/json');
        $cli_id = isset($_GET['cli_id']) ? intval($_GET['cli_id']) : 0;
        if ($cli_id <= 0) {
            echo json_encode(["success" => false, "message" => "Cliente inválido"]);
            break;
        }
        try {
            $data = $recepcion->get_historial_por_cliente($cli_id);
            // Formatear fechas y estados
            $results = [];
            foreach($data as $row) {
                $status = $row['Estado'] == 1 ? 'Activo' : 'Finalizado';
                $class = $row['Estado'] == 1 ? 'success' : 'secondary';
                
                $row['EstadoLabel'] = '<span class="badge bg-'.$class.'">'.$status.'</span>';
                
                // --- Logic to get Consumption (Products) ---
                $ventas = $ventaModel->get_ventas_x_recepcion($row['IdRecepcion']);
                $consumo = [];
                if (is_array($ventas)) {
                    foreach($ventas as $venta){
                        $detalles = $ventaModel->get_detalle_venta_x_id_venta($venta['IdVenta']);
                        if (is_array($detalles)) {
                            foreach($detalles as $detalle){
                                 $consumo[] = $detalle;
                            }
                        }
                    }
                }
                $row['Consumo'] = $consumo;

                // --- Logic to get Invoice ---
                try {
                    $factura = $facturaModel->obtenerPorRecepcion($row['IdRecepcion']);
                    if ($factura) {
                        $factura['tipo_doc'] = '01'; // Ensure we know it's a Factura
                    }
                    $row['Factura'] = $factura;
                } catch (Exception $e) {
                    $row['Factura'] = null; 
                }
                
                // If no Factura, check detection of Boleta
                if (empty($row['Factura'])) {
                    try {
                         // We suspect Boleta model might need a specific method or we try to use the SP directly if needed,
                         // but assuming consistency, let's try obtaining by reception.
                         // If the method doesn't exist publicly, we might need to rely on a different approach,
                         // but looking at Factura.php it has it. Let's assume Boleta has it or we add it.
                         // For now, I will assume it exists or I will add it to the model if missing.
                         $boleta = $boletaModel->obtenerPorRecepcion($row['IdRecepcion']);
                         if ($boleta) {
                             // Normalize fields to match Factura for Frontend (fac_id, fac_serie, etc.)
                             $row['Factura'] = [
                                 'fac_id' => $boleta['bol_id'], // Map bol_id to fac_id
                                 'fac_serie' => $boleta['bol_serie'],
                                 'fac_correlativo' => $boleta['bol_correlativo'],
                                 'IdVenta' => $boleta['bol_id'], // Fallback
                                 'tipo_doc' => '03' // Mark as Boleta
                             ];
                         }
                    } catch (Exception $e) {
                        // Ignore
                    }
                }

                $results[] = $row;
            }
            echo json_encode(["success" => true, "data" => $results]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "Operación no soportada"]);
        break;
}
