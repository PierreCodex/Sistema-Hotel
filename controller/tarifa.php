<?php
/* TODO: Llamando Clases */
require_once("../config/conexion.php");
require_once("../models/Tarifa.php");

/* TODO: Inicializando clase */
$tarifa = new Tarifa();

switch ($_GET["op"]) {

    case "guardaryeditar":
        // Validar que la descripción no esté vacía
        if (empty(trim($_POST["tar_desc"]))) {
            echo json_encode([
                'status' => 'error',
                'message' => 'La descripción de la tarifa es obligatoria'
            ]);
            break;
        }

        // Validar que el precio no esté vacío
        if (empty(trim($_POST["tar_precio"]))) {
            echo json_encode([
                'status' => 'error',
                'message' => 'El precio de la tarifa es obligatorio'
            ]);
            break;
        }

        // Verificar si ya existe una tarifa con la misma descripción
        $existe = $tarifa->verificar_tarifa_existente(
            $_POST["tar_desc"],
            empty($_POST["tar_id"]) ? null : $_POST["tar_id"]
        );

        if ($existe) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ya existe una tarifa con esta descripción'
            ]);
            break;
        }

        try {
            if (empty($_POST["tar_id"])) {
                $resultado = $tarifa->insert_tarifa($_POST["tar_desc"], $_POST["tar_precio"]);
                $response['status'] = 'success';
                $response['message'] = 'Tarifa registrada correctamente';
            } else {
                $resultado = $tarifa->update_tarifa($_POST["tar_id"], $_POST["tar_desc"], $_POST["tar_precio"]);
                $response['status'] = 'success';
                $response['message'] = 'Tarifa actualizada correctamente';
            }
            echo json_encode($response);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = 'Error al procesar la solicitud: ' . $e->getMessage();
            echo json_encode($response);
        }
        break;


    /* Listar tarifas activas */

    case "listar":
        $datos = $tarifa->get_tarifa();
        $data = [];
        foreach ($datos as $row) {
            $sub_array = [];
            $sub_array[] = $row["Descripcion"];
            $sub_array[] = $row["Precio"];
            $sub_array[] = $row["Estado"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            if ($row["Estado"] == 1) {
                $sub_array[] = '<button type="button" onClick="editar(' . $row["IdTarifa"] . ')" id="' . $row["IdTarifa"] . '" class="btn btn-warning btn-icon waves-effect waves-light" title="Editar rol"><i class="ri-edit-2-line"></i></button>';
            } else {
                $sub_array[] = '<button type="button" class="btn btn-warning btn-icon waves-effect waves-light" disabled title="Para editar, primero active el rol"><i class="ri-edit-2-line"></i></button>';
            }
            $sub_array[] = '<button type="button" onClick="eliminar(' . $row["IdTarifa"] . ');" id="' . $row["IdTarifa"] . '" class="btn btn-danger btn-icon waves-effect waves-light" title="Eliminar rol"><i class="bx bx-trash"></i></button>';
            // Checkbox para cambiar estado
            $checked = $row["Estado"] == 1 ? 'checked' : '';
            $sub_array[] = '<div class="form-check form-switch form-switch-custom form-switch-success"> 
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch' . $row["IdTarifa"] . '" ' . $checked . ' onchange="cambiarEstado(' . $row["IdTarifa"] . ', this.checked)">
                                    <label class="form-check-label" for="switch' . $row["IdTarifa"] . '">Yes/No</label>   
                                </div>';


            $data[] = $sub_array;
        }

        $results = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
        echo json_encode($results);
        break;


    case "listar-activas":
        try {
            $datos = $tarifa->get_tarifas_activas();
            echo json_encode($datos);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;


    /* Listar tarifas asignadas a una habitación */
    case "listar_asignadas":
        $hab_id = $_POST["hab_id"] ?? null;
        if (!$hab_id) {
            echo json_encode(['status' => 'error', 'message' => 'hab_id requerido']);
            break;
        }
        try {
            $datos = $tarifa->get_tarifas_asignadas_por_habitacion($hab_id);
            echo json_encode($datos);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    /* Asignar tarifa a habitación */
    case "asignar":
        $hab_id = $_POST["hab_id"] ?? null;
        $tarifa_id = $_POST["tarifa_id"] ?? null;
        $fecha_inicio = $_POST["fecha_inicio"] ?? null;
        $fecha_fin = isset($_POST["fecha_fin"]) && $_POST["fecha_fin"] !== '' ? $_POST["fecha_fin"] : null;
        if (!$hab_id || !$tarifa_id || !$fecha_inicio) {
            echo json_encode(['status' => 'error', 'message' => 'hab_id, tarifa_id y fecha_inicio son obligatorios']);
            break;
        }
        try {
            $tarifa->asignar_tarifa_habitacion($hab_id, $tarifa_id, $fecha_inicio, $fecha_fin);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    /* Actualizar vigencia de una asignación */
    case "actualizar_vigencia":
        $habitacion_tarifa_id = $_POST["habitacion_tarifa_id"] ?? null;
        $fecha_inicio = $_POST["fecha_inicio"] ?? null;
        $fecha_fin = isset($_POST["fecha_fin"]) && $_POST["fecha_fin"] !== '' ? $_POST["fecha_fin"] : null;
        if (!$habitacion_tarifa_id || !$fecha_inicio) {
            echo json_encode(['status' => 'error', 'message' => 'habitacion_tarifa_id y fecha_inicio son obligatorios']);
            break;
        }
        try {
            $tarifa->actualizar_vigencia_tarifa_habitacion($habitacion_tarifa_id, $fecha_inicio, $fecha_fin);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    /* Eliminar asignación de tarifa */
    case "eliminar_asignada":
        $habitacion_tarifa_id = $_POST["habitacion_tarifa_id"] ?? null;
        if (!$habitacion_tarifa_id) {
            echo json_encode(['status' => 'error', 'message' => 'habitacion_tarifa_id requerido']);
            break;
        }
        try {
            $tarifa->eliminar_tarifa_habitacion($habitacion_tarifa_id);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    /* Mostrar tarifa por ID */
    case "mostrar":
        $tar_id = $_POST["tar_id"] ?? null;
        if (!$tar_id) {
            echo json_encode(['status' => 'error', 'message' => 'tar_id requerido']);
            break;
        }
        $datos = $tarifa->get_tarifa_x_tar_id($tar_id);
        if (is_array($datos) == true && count($datos) > 0) {
            foreach ($datos as $row) {
                $output["TAR_ID"] = $row["TAR_ID"];
                $output["TAR_DESC"] = $row["TAR_DESC"];
                $output["TAR_PRECIO"] = $row["TAR_PRECIO"];
                $output["EST"] = $row["EST"];
            }
            echo json_encode($output);
        }
        break;

    /* Cambiar estado de la tarifa */
    case "cambiar_estado":
        $tar_id = $_POST["tar_id"] ?? null;
        $nuevo_estado = isset($_POST["estado"]) && ($_POST["estado"] === 'true' || $_POST["estado"] === true) ? 1 : 0;
        if (!$tar_id) {
            echo json_encode(['status' => 'error', 'message' => 'tar_id requerido']);
            break;
        }
        $tarifa->cambiar_estado_tarifa($tar_id, $nuevo_estado);
        echo json_encode(["status" => "success", "message" => "Estado actualizado correctamente"]);
        break;

    /* Eliminar tarifa (baja lógica / física según SP) */
    case "eliminar":


        $tar_id = $_POST["tar_id"] ?? null;
        if (!$tar_id) {
            echo json_encode(['status' => 'error', 'message' => 'tar_id requerido']);
            break;
        }
        try {
            $tarifa->delete_tarifa($tar_id);
            echo json_encode(['status' => 'success', 'message' => 'Tarifa eliminada']);
        } catch (PDOException $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            if ($code === '23000' || strpos($msg, '1451') !== false || stripos($msg, 'Integrity constraint violation') !== false) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se puede eliminar la tarifa porque está asociada a una habitación. Desvincule la tarifa antes de eliminar.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al eliminar la tarifa: ' . $msg
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al eliminar la tarifa: ' . $e->getMessage()
            ]);
        }
        break;
    /* TODO: Listar Combo */
    case "combo":
        // Leer hab_id desde POST; si no llega, devolver tarifas activas como fallback
        $hab_id = $_POST["hab_id"] ?? null;
        $html = "<option value='0' selected>Seleccione</option>";
        try {
            if ($hab_id) {
                $datos = $tarifa->get_tarifas_asignadas_por_habitacion($hab_id);
            } else {
            }
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                 
                    // Id de tarifa puede venir como TAR_ID (con alias), IdTarifa (catálogo) o id_tarifa (asignaciones por habitación)
                    $id = $row["TAR_ID"] ?? ($row["IdTarifa"] ?? ($row["id_tarifa"] ?? null));
                    // Descripción puede venir como TAR_DESC o Descripcion
                    $desc = $row["TAR_DESC"] ?? ($row["Descripcion"] ?? ($row["descripcion"] ?? "Tarifa"));
                    // Precio puede venir como Precio (catálogo/asignaciones) o TAR_PRECIO (consulta por ID)
                    $precio = $row["Precio"] ?? ($row["TAR_PRECIO"] ?? null);
                    if ($id !== null) {
                        // Mostrar solo el nombre en el combo; mantener precio en data-precio
                        $label = $desc;
                        $attrPrecio = '';
                        if ($precio !== null && $precio !== '') {
                            $precioNum = (float)$precio;
                            $precioFmt = number_format($precioNum, 2, '.', '');
                            $attrPrecio = " data-precio='" . htmlspecialchars($precioFmt, ENT_QUOTES, 'UTF-8') . "'";
                        }
                        $html .= "<option value='" . $id . "'" . $attrPrecio . ">" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                }
            } else {
                $html .= "<option disabled>No hay tarifas disponibles</option>";
            }
        } catch (Exception $e) {
            $html .= "<option disabled>Error al cargar tarifas</option>";
        }
        echo $html;
        break;


}
