<?php
    /* TODO: Llamando Clases */
    require_once("../config/conexion.php");
    require_once("../models/Recepcion.php");
    require_once("../models/Habitacion.php");

    /* TODO: Inicializando clase */
    $recepcion = new Recepcion();
    $habitacionModel = new Habitacion();

    switch($_GET["op"]){
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

        default:
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "message" => "Operación no soportada"]);
            break;
    }
?>