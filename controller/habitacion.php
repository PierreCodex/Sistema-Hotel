<?php
/* TODO: Llamando Clases */
// Determinar la ruta base del proyecto

    require_once("../config/conexion.php");
    require_once("../models/Habitacion.php");
    require_once("../models/Categoria.php");
    require_once("../models/Piso.php");
    require_once("../models/Tarifa.php");


/* TODO: Inicializando clases */
$habitacion = new Habitacion();
$categoria = new Categoria();
$piso = new Piso();
$tarifa = new Tarifa();

switch ($_GET["op"]) {
    /* TODO: Guardar y editar, guardar cuando el ID este vacio, y Actualizar cuando se envie el ID */
    case "guardaryeditar":
        // Validar campos obligatorios
        if (empty(trim($_POST["hab_num"]))) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'El número de habitación es obligatorio'
            ));
            break;
        }

        if (empty(trim($_POST["hab_det"]))) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'La descripción de la habitación es obligatoria'
            ));
            break;
        }

        // El precio por noche ya no se registra aquí; se asigna por tarifas

        if (empty($_POST["hab_piso_id"])) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Debe seleccionar un piso'
            ));
            break;
        }

        if (empty($_POST["hab_cat_id"])) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Debe seleccionar una categoría'
            ));
            break;
        }

        // Verificar si ya existe una habitación con el mismo número
        $existe = $habitacion->verificar_habitacion_existente(
            $_POST["hab_num"],
            empty($_POST["hab_id"]) ? null : $_POST["hab_id"]
        );

        if ($existe) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Ya existe una habitación con este número'
            ));
            break;
        }

        try {
            // Si no se especifica estado de habitación, se usará el valor por defecto (Disponible)
            $hab_est_id = !empty($_POST["hab_est_id"]) ? $_POST["hab_est_id"] : 0;

            if (empty($_POST["hab_id"])) {
                $resultado = $habitacion->insert_habitacion(
                    $_POST["hab_num"],
                    $_POST["hab_det"],
                    $hab_est_id,
                    $_POST["hab_piso_id"],
                    $_POST["hab_cat_id"]
                );
                $response['status'] = 'success';
                $response['message'] = 'Habitación registrada correctamente';
            } else {
                $resultado = $habitacion->update_habitacion(
                    $_POST["hab_id"],
                    $_POST["hab_num"],
                    $_POST["hab_det"],
                    $hab_est_id,
                    $_POST["hab_piso_id"],
                    $_POST["hab_cat_id"]
                );
                $response['status'] = 'success';
                $response['message'] = 'Habitación actualizada correctamente';
            }
            echo json_encode($response);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = 'Error al procesar la solicitud: ' . $e->getMessage();
            echo json_encode($response);
        }
        break;

    /* TODO: Listado de registros formato JSON para Datatable JS */
    case "listar":
        $datos = $habitacion->get_habitacion();
        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["HAB_NUM"];
            $sub_array[] = $row["HAB_DET"];
              // Botón para abrir asignación de tarifas
            $sub_array[] = '<div class="text-center"><button type="button" onClick="abrirModalTarifa(' . $row["HAB_ID"] . ')" class="btn btn-info btn-icon waves-effect waves-light" title="Tarifas"><i class="bx bx-money"></i></button></div>';
            $sub_array[] = $row["PISO_NOM"];
            $sub_array[] = $row["CAT_NOM"];
            
        
            $sub_array[] = $row["EST"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            if ($row["EST"] == 1) {
                $sub_array[] = '<button type="button" onClick="editar(' . $row["HAB_ID"] . ')" id="' . $row["HAB_ID"] . '" class="btn btn-warning btn-icon waves-effect waves-light" title="Editar habitación"><i class="ri-edit-2-line"></i></button>';
            } else {
                $sub_array[] = '<button type="button" class="btn btn-warning btn-icon waves-effect waves-light" disabled title="Para editar, primero active la habitación"><i class="ri-edit-2-line"></i></button>';
            }
            
            $sub_array[] = '<button type="button" onClick="eliminar(' . $row["HAB_ID"] . ');" id="' . $row["HAB_ID"] . '" class="btn btn-danger"><i class="bx bx-trash"></i></button>';
            
            // Checkbox para cambiar estado
            $checked = $row["EST"] == 1 ? 'checked' : '';
            $sub_array[] = '<div class="form-check form-switch form-switch-custom form-switch-success">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch' . $row["HAB_ID"] . '" ' . $checked . ' onchange="cambiarEstado(' . $row["HAB_ID"] . ', this.checked)">
                                    <label class="form-check-label" for="switch' . $row["HAB_ID"] . '">Yes/No</label>   
                                </div>';

            $data[] = $sub_array;
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;
        
    /* TODO: Mostrar información de registro según su ID */
    case "mostrar":
        $datos = $habitacion->get_habitacion_x_hab_id($_POST["hab_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["HAB_ID"] = $row["HAB_ID"];
                $output["HAB_NUM"] = $row["HAB_NUM"];
                $output["HAB_DET"] = $row["HAB_DET"];
                $output["HAB_EST_ID"] = $row["HAB_EST_ID"];
                $output["HAB_PISO_ID"] = $row["HAB_PISO_ID"];
                $output["HAB_CAT_ID"] = $row["HAB_CAT_ID"];
                $output["CAT_NOM"] = $row["CAT_NOM"];
                $output["ESTADO_NOM"] = $row["ESTADO_NOM"];
                $output["PISO_NOM"] = $row["PISO_NOM"];
            }
            echo json_encode($output);
        }
        break;

    /* TODO: Cambiar Estado a 0/1 del Registro */
    case "cambiar_estado":
        $nuevo_estado = $_POST["estado"] == 'true' ? 1 : 0;
        $habitacion->cambiar_estado_habitacion($_POST["hab_id"], $nuevo_estado);
        echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
        break;

    /* TODO: Eliminar habitación (cambio de estado) */
    case "eliminar":
        $habitacion->delete_habitacion($_POST["hab_id"]);
        echo json_encode(array("status" => "success", "message" => "Habitación eliminada correctamente"));
        break;

    case 'cambiar_tipo_estado':
        $hab_id = $_POST['hab_id'] ?? null;
        $id_estado_habitacion = $_POST['id_estado_habitacion'] ?? null;
        
        if (!$hab_id || !$id_estado_habitacion) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            exit;
        }
        
    
        $resultado = $habitacion->cambiar_tipo_estado_habitacion($hab_id, $id_estado_habitacion);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Tipo de estado actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el tipo de estado']);
        }
        break;
    
    /* TODO: Combo de Categorías */
    case "combo_categoria":
        $datos = $categoria->get_categoria_activa();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option value=''>Seleccionar Categoría</option>";
            foreach ($datos as $row) {
                // Incluir tarifa como data attribute para auto-rellenar precio de habitación
                $tarifa = isset($row["CAT_TAR"]) ? $row["CAT_TAR"] : '';
                $label = $row["CAT_NOM"];
                if ($tarifa !== '' && is_numeric($tarifa)) {
                    $label .= " (S/. " . number_format($tarifa, 2) . ")";
                }
                $html .= "<option value='" . $row["CAT_ID"] . "' data-tarifa='" . htmlspecialchars($tarifa, ENT_QUOTES, 'UTF-8') . "'>" . $label . "</option>";
            }
            echo $html;
        }
        break;

    /* TODO: Combo de Pisos */
    case "combo_piso":
        $datos = $piso->get_piso_activo();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option value=''>Seleccionar Piso</option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row["PISO_ID"] . "'>" . $row["PISO_NOM"] . "</option>";
            }
            echo $html;
        }
        break;

    /* TODO: Combo de Estados de Habitación */
    case "combo_estado_habitacion":
        // Necesitamos incluir el modelo EstadoHabitacion
        require_once("../models/EstadoHabitacion.php");
        $estadoHabitacion = new EstadoHabitacion();
        $datos = $estadoHabitacion->get_estado_habitacion_activos();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option value=''>Seleccionar Estado</option>";
            foreach ($datos as $row) {
                $selected = ($row["EST_HAB_NOM"] == "DISPONIBLE") ? "selected" : "";
                $html .= "<option value='" . $row["EST_HAB_ID"] . "' " . $selected . ">" . $row["EST_HAB_NOM"] . "</option>";
            }
            echo $html;
        }
        break;

    /* TODO: Filtrar habitaciones por piso */
    case "filtrar_por_piso":
        $datos = $habitacion->get_habitacion_x_piso($_POST["piso_id"]);
        echo json_encode($datos);
        break;
    /* TODO: Listar habitaciones activas para combo */
    case "combo_habitacion":
        $datos = $habitacion->get_habitacion_activa();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option value=''>Seleccionar Habitación</option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row["HAB_ID"] . "'>Hab. " . $row["HAB_NUM"] . " - " . $row["HAB_DET"] . " (S/. " . $row["HAB_PRE"] . ")</option>";
            }
            echo $html;
        }
        break;

    /* TODO: Listar habitaciones activas para vista de recepción */
    case "listar_activos":
        $datos = $habitacion->get_habitacion_activa();
        echo json_encode($datos);
        break;
    
    /* TODO: Listar habitaciones para empleado (vista simplificada) */
    case "listar_para_empleado":
        $datos = $habitacion->get_habitacion_activa();
        // Devolver en formato DataTables
        echo json_encode(array("data" => $datos));
        break;
    
    /* TODO: Listar habitaciones activas para vista de tienda */
    case "listar_ocupados":
        $datos = $habitacion->get_habitacion_ocupada();
        echo json_encode($datos);
        break;
    /* Obtener habitación por Id */
    case "obtener_por_id":
        $hab_id = isset($_POST["hab_id"]) ? intval($_POST["hab_id"]) : 0;
        if ($hab_id <= 0) {
            echo json_encode(array("error" => "hab_id requerido"));
            break;
        }
        $datos = $habitacion->get_habitacion_x_hab_id($hab_id);
        if (is_array($datos) && count($datos) > 0) {
            echo json_encode($datos[0]);
        } else {
            echo json_encode(array("error" => "Habitación no encontrada"));
        }
        break;
    /* TODO: Obtener habitación por número */
    case "obtener_por_numero":
        if (!empty($_POST["hab_num"])) {
            // Buscamos la habitación por su número
            $habitaciones = $habitacion->get_habitacion_activa();
            $habitacion_encontrada = null;
            
            foreach ($habitaciones as $hab) {
                if ($hab["HAB_NUM"] == $_POST["hab_num"]) {
                    $habitacion_encontrada = $hab;
                    break;
                }
            }
            
            if ($habitacion_encontrada) {
                echo json_encode($habitacion_encontrada);
            } else {
                echo json_encode(array("error" => "Habitación no encontrada"));
            }
        } else {
            echo json_encode(array("error" => "Número de habitación requerido"));
        }
        break;

    /* Tarifas: listar todas las tarifas activas */
    case "listar_tarifas":
        $datos = $tarifa->get_tarifas_activas();
        echo json_encode($datos);
        break;

    /* Tarifas: listar tarifas asignadas a una habitación */
    case "listar_tarifas_asignadas":
        if (empty($_POST["hab_id"])) {
            echo json_encode(array('status' => 'error', 'message' => 'hab_id requerido'));
            break;
        }
        $datos = $tarifa->get_tarifas_asignadas_por_habitacion($_POST["hab_id"]);
        echo json_encode($datos);
        break;

    /* Tarifas: asignar una tarifa a habitación */
    case "asignar_tarifa":
        $hab_id = $_POST["hab_id"] ?? null;
        $tarifa_id = $_POST["tarifa_id"] ?? null;
        $fecha_inicio = $_POST["fecha_inicio"] ?? null;
        $fecha_fin = isset($_POST["fecha_fin"]) && $_POST["fecha_fin"] !== '' ? $_POST["fecha_fin"] : null;
        if (!$hab_id || !$tarifa_id || !$fecha_inicio) {
            echo json_encode(array('status' => 'error', 'message' => 'hab_id, tarifa_id y fecha_inicio son obligatorios'));
            break;
        }
        try {
            $tarifa->asignar_tarifa_habitacion($hab_id, $tarifa_id, $fecha_inicio, $fecha_fin);
            echo json_encode(array('status' => 'success'));
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
        }
        break;

    /* Tarifas: actualizar vigencia de una asignación */
    case "actualizar_vigencia_tarifa":
        $habitacion_tarifa_id = $_POST["habitacion_tarifa_id"] ?? null;
        $fecha_inicio = $_POST["fecha_inicio"] ?? null;
        $fecha_fin = isset($_POST["fecha_fin"]) && $_POST["fecha_fin"] !== '' ? $_POST["fecha_fin"] : null;
        if (!$habitacion_tarifa_id || !$fecha_inicio) {
            echo json_encode(array('status' => 'error', 'message' => 'habitacion_tarifa_id y fecha_inicio son obligatorios'));
            break;
        }
        try {
            $tarifa->actualizar_vigencia_tarifa_habitacion($habitacion_tarifa_id, $fecha_inicio, $fecha_fin);
            echo json_encode(array('status' => 'success'));
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
        }
        break;

    /* Tarifas: eliminar asignación de tarifa */
    case "eliminar_tarifa_asignada":
        $habitacion_tarifa_id = $_POST["habitacion_tarifa_id"] ?? null;
        if (!$habitacion_tarifa_id) {
            echo json_encode(array('status' => 'error', 'message' => 'habitacion_tarifa_id requerido'));
            break;
        }
        try {
            $tarifa->eliminar_tarifa_habitacion($habitacion_tarifa_id);
            echo json_encode(array('status' => 'success'));
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
        }
        break;
}
?>
