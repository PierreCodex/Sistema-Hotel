<?php
    /* TODO: Llamando Clases */
    require_once("../config/conexion.php");
    require_once("../models/Venta.php");
    require_once("../models/Recepcion.php");
    require_once("../models/Producto.php");
    /* TODO: Inicializando clase */
    $venta = new Venta();
    $recepcion = new Recepcion();
    $producto = new Producto();
    header('Content-Type: application/json');

    switch($_GET["op"]) {
        /* Listar ventas por recepción */
        case "listar_por_recepcion":
            try {
                $rec_id = intval($_POST["rec_id"] ?? 0);
                if ($rec_id <= 0) {
                    echo json_encode(["success" => false, "message" => "Id de recepción inválido"]);
                    break;
                }
                $ventas = $venta->get_ventas_x_recepcion($rec_id);
                echo json_encode(["success" => true, "data" => $ventas]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;
        /* Registrar nueva venta ligada a recepción activa */
        case "registrar":
            try {
                // Prioridad: rec_id por POST -> rec_id en sesión -> buscar por IdHabitacion
                $rec_id = isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0;
                if ($rec_id <= 0) {
                    $rec_id = isset($_SESSION['IdRecepcion']) ? intval($_SESSION['IdRecepcion']) : 0;
                }
                if ($rec_id <= 0) {
                    $hab_id = isset($_SESSION['IdHabitacion']) ? intval($_SESSION['IdHabitacion']) : 0;
                    if ($hab_id > 0) {
                        $rec_id = $recepcion->get_recepcion_activa_por_habitacion($hab_id);
                    }
                }
                if ($rec_id <= 0) {
                    echo json_encode(["success" => false, "message" => "No se encontró recepción activa para registrar venta. Abra esta pantalla desde una recepción activa o envíe rec_id."]);
                    break;
                }
                
                // Reutilizar venta borrador existente o crear nueva
                $vent_id = $venta->get_or_create_venta_borrador($rec_id);
                echo json_encode(["success" => true, "VENT_ID" => intval($vent_id)]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Registrar detalle de venta */
        case "guardardetalle":
            try {
                $vent_id = intval($_POST["vent_id"] ?? 0);
                $prod_id = intval($_POST["prod_id"] ?? 0);
                $prod_pventa = floatval($_POST["prod_pventa"] ?? 0);
                $detv_cant = intval($_POST["detv_cant"] ?? 0);
                if ($vent_id <= 0 || $prod_id <= 0 || $prod_pventa <= 0 || $detv_cant <= 0) {
                    echo json_encode(["success" => false, "message" => "Datos de detalle inválidos"]);
                    break;
                }
                // Validación de stock disponible en tiempo real
                $info_prod = $producto->get_producto_x_id($prod_id);
                $stock_disp = null;
                if (is_array($info_prod) && count($info_prod) > 0) {
                    $row = $info_prod[0];
                    if (isset($row['PRO_CANT'])) {
                        $stock_disp = intval($row['PRO_CANT']);
                    } elseif (isset($row['Cantidad'])) {
                        $stock_disp = intval($row['Cantidad']);
                    }
                }
                if ($stock_disp === null) {
                    echo json_encode(["success" => false, "message" => "Producto no encontrado o sin stock configurado"]);
                    break;
                }
                if ($stock_disp <= 0) {
                    echo json_encode(["success" => false, "message" => "Stock agotado para el producto seleccionado"]);
                    break;
                }
                if ($detv_cant > $stock_disp) {
                    echo json_encode(["success" => false, "message" => "Stock insuficiente. Disponible: " . intval($stock_disp)]);
                    break;
                }
                $det_id = $venta->insert_detalle_venta($vent_id, $prod_id, $prod_pventa, $detv_cant);
                if ($det_id > 0) {
                    echo json_encode(["success" => true, "DETV_ID" => intval($det_id)]);
                } else {
                    echo json_encode(["success" => false, "message" => "No se pudo guardar el detalle"]);
                }
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Eliminar detalle */
        case "eliminardetalle":
            try {
                $detv_id = intval($_POST["detv_id"] ?? 0);
                if ($detv_id <= 0) {
                    echo json_encode(["success" => false, "message" => "Id de detalle inválido"]);
                    break;
                }
                $venta->delete_detalle_venta($detv_id);
                echo json_encode(["success" => true]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Listar detalle de venta (formato DataTable) */
        case "listardetalle":
            try {
                $vent_id = intval($_POST["vent_id"] ?? 0);
                $datos = $venta->get_detalle_venta_x_id_venta($vent_id);
                $data = [];
                foreach($datos as $row){
                    $sub_array = [];
                    $sub_array[] = $row["DETV_ID"];
                    $sub_array[] = htmlspecialchars($row["PRO_NOM"], ENT_QUOTES, 'UTF-8');
                    $sub_array[] = intval($row["DETV_CANT"]);
                    $sub_array[] = number_format((float)$row["PROD_PVENTA"], 2);
                    $sub_array[] = number_format((float)$row["DETV_TOTAL"], 2);
   
                    $sub_array[] = '<button type="button" onClick="eliminar('.intval($row["DETV_ID"]).','.intval($row["VENT_ID"]).')" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';
                    $data[] = $sub_array;
                }

                $results = [
                    "sEcho"=>1,
                    "iTotalRecords"=>count($data),
                    "iTotalDisplayRecords"=>count($data),
                    "aaData"=>$data
                ];
                echo json_encode($results);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Calcular totales y actualizar venta.Total */
        case "calculo":
            try {
                $vent_id = intval($_POST["vent_id"] ?? 0);
                $totales = $venta->calcular_totales_y_actualizar($vent_id);
                echo json_encode([
                    "VENT_SUBTOTAL" => number_format((float)$totales["VENT_SUBTOTAL"] ?? 0, 2, '.', ''),
                    "VENT_IGV" => number_format((float)$totales["VENT_IGV"] ?? 0, 2, '.', ''),
                    "VENT_TOTAL" => number_format((float)$totales["VENT_TOTAL"] ?? 0, 2, '.', '')
                ]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Guardar venta: según estado seleccionado (Pendiente/Cancelado/Activo) */
        case "guardar":
            try {
                $vent_id = intval($_POST["vent_id"] ?? 0);
                $estado_sel = isset($_POST["vent_estado"]) ? trim($_POST["vent_estado"]) : null;
                if ($vent_id <= 0) {
                    echo json_encode(["success" => false, "message" => "Id de venta inválido"]);
                    break;
                }
                // Recalcular totales de la venta
                $venta->calcular_totales_y_actualizar($vent_id);

                if (strtoupper($estado_sel) === "PENDIENTE") {
                    // Pendiente (no pagado aún): conservar para cobrar en salida
                    $venta->update_venta_estado($vent_id, 'PENDIENTE');
                } else {
                    // Pagado (o fallback): finalizar y descontar stock, marcar como PAGADO
                    $venta->finalizar_venta($vent_id);
                    $venta->update_venta_estado($vent_id, 'PAGADO');
                }

                // Confirmar estado persistido desde BD
                $info = $venta->get_venta_x_id($vent_id);
                $estado_bd = (is_array($info) && count($info) > 0) ? ($info[0]['Estado'] ?? null) : null;
                echo json_encode(["success" => true, "VENT_ID" => $vent_id, "estado" => $estado_bd]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Cancelar venta borrador: restaura stock y elimina la venta */
        case "cancelar_borrador":
            try {
                $vent_id = intval($_POST["vent_id"] ?? 0);
                if ($vent_id <= 0) {
                    echo json_encode(["success" => false, "message" => "Id de venta inválido"]);
                    break;
                }
                $resultado = $venta->cancelar_venta_borrador($vent_id);
                if ($resultado) {
                    echo json_encode(["success" => true, "message" => "Venta cancelada y stock restaurado"]);
                } else {
                    echo json_encode(["success" => false, "message" => "La venta no está en estado borrador o ya fue procesada"]);
                }
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Listar ventas por recepción para DataTable (Historial de Ventas) */
        case "listar_ventas_por_recepcion":
            try {
                $rec_id = intval($_POST["rec_id"] ?? 0);
                if ($rec_id <= 0) {
                    echo json_encode(["aaData" => []]);
                    break;
                }
                
                $ventas = $venta->get_ventas_x_recepcion($rec_id);
                $data = [];
                
                foreach ($ventas as $row) {
                    $sub_array = [];
                    $sub_array[] = $row["IdVenta"];
                    $sub_array[] = isset($row["FechaCreacion"]) ? date('d/m/Y H:i', strtotime($row["FechaCreacion"])) : '-';
                    $sub_array[] = 'S/ ' . number_format($row["Total"], 2);
                    
                    // Badge de estado
                    $estado_badge = '';
                    switch($row["Estado"]){
                        case 'ACTIVO':
                            $estado_badge = '<span class="badge bg-success">ACTIVO</span>';
                            break;
                        case 'PENDIENTE':
                            $estado_badge = '<span class="badge bg-warning">PENDIENTE</span>';
                            break;
                        case 'BORRADOR':
                            $estado_badge = '<span class="badge bg-secondary">BORRADOR</span>';
                            break;
                        default:
                            $estado_badge = '<span class="badge bg-info">' . htmlspecialchars($row["Estado"]) . '</span>';
                    }
                    $sub_array[] = $estado_badge;
                    
                    // Contar productos
                    $detalles = $venta->get_detalle_venta_x_id_venta($row["IdVenta"]);
                    $num_productos = count($detalles);
                    $sub_array[] = $num_productos . ' producto(s)';
                    
                    // Botón ver detalles
                    $sub_array[] = '<button type="button" onClick="verDetalles(' . $row["IdVenta"] . ')" class="btn btn-info btn-sm" title="Ver Detalles"><i class="ri-eye-line"></i> Ver</button>';
                    
                    $data[] = $sub_array;
                }
                
                echo json_encode([
                    "sEcho" => 1,
                    "iTotalRecords" => count($data),
                    "iTotalDisplayRecords" => count($data),
                    "aaData" => $data
                ]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;

        /* Mostrar venta completa con detalles (para modal) */
        case "mostrar_venta_completa":
            try {
                $vent_id = intval($_POST["vent_id"] ?? 0);
                if ($vent_id <= 0) {
                    echo json_encode(["error" => "Id de venta inválido"]);
                    break;
                }
                
                $venta_data = $venta->get_venta_x_id($vent_id);
                if(empty($venta_data)){
                    echo json_encode(["error" => "Venta no encontrada"]);
                    break;
                }
                
                $venta_info = $venta_data[0];
                $detalles = $venta->get_detalle_venta_x_id_venta($vent_id);
                
                $venta_info['detalles'] = $detalles;
                
                echo json_encode($venta_info);
            } catch (Exception $e) {
                echo json_encode(["error" => $e->getMessage()]);
            }
            break;

        /* Listar productos vendidos por recepción (para modal de historial) */
        case "listar_productos_por_recepcion":
            try {
                $rec_id = intval($_POST["rec_id"] ?? 0);
                error_log("DEBUG: listar_productos_por_recepcion - rec_id: " . $rec_id);
                
                if ($rec_id <= 0) {
                    echo json_encode(["error" => "ID de recepción inválido"]);
                    break;
                }
                
                $ventas = $venta->get_ventas_x_recepcion($rec_id);
                error_log("DEBUG: Ventas encontradas: " . count($ventas));
                
                $productos = [];
                
                foreach ($ventas as $v) {
                    error_log("DEBUG: Procesando venta ID: " . $v['IdVenta'] . " Estado: " . $v['Estado']);
                    
                    // Excluir ventas BORRADOR y ANULADO
                    if ($v['Estado'] != 'ANULADO' && $v['Estado'] != 'BORRADOR') {
                        $detalles_venta = $venta->get_detalle_venta_x_id_venta($v['IdVenta']);
                        error_log("DEBUG: Detalles encontrados para venta " . $v['IdVenta'] . ": " . count($detalles_venta));
                        
                        foreach ($detalles_venta as $dv) {
                            $productos[] = [
                                'producto' => $dv['PRO_NOM'],
                                'cantidad' => $dv['DETV_CANT'],
                                'precio_unitario' => $dv['PROD_PVENTA'],
                                'subtotal' => $dv['DETV_TOTAL'],
                                'estado_venta' => $v['Estado']
                            ];
                        }
                    }
                }
                
                error_log("DEBUG: Total productos a devolver: " . count($productos));
                
                echo json_encode([
                    "success" => true,
                    "productos" => $productos
                ]);
            } catch (Exception $e) {
                error_log("ERROR en listar_productos_por_recepcion: " . $e->getMessage());
                echo json_encode(["error" => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Operación no soportada"]);
            break;
    }
?>