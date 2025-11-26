<?php
require_once("../config/conexion.php");
require_once("../models/EstadoHabitacion.php");

$estadohabitacion = new EstadoHabitacion();

switch ($_GET["op"]) {
    case "guardaryeditar":
        // Validar que el nombre no esté vacío
        if (empty(trim($_POST["est_hab_nom"]))) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'El nombre del estado de habitación es obligatorio'
            ));
            break;
        }

        // Verificar si ya existe una categoría con el mismo nombre
        $existe = $estadohabitacion->verificar_estado_habitacion_existe(
            $_POST["est_hab_nom"],
            empty($_POST["est_hab_id"]) ? null : $_POST["est_hab_id"]
        );

        if ($existe) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Ya existe una categoría con este nombre'
            ));
            break;
        }

        try {
            if (empty($_POST["est_hab_id"])) {
                $resultado = $estadohabitacion->insert_estado_habitacion($_POST["est_hab_nom"]);
                $response['status'] = 'success';
                $response['message'] = 'Estado de habitación registrada correctamente';
            } else {
                $resultado = $estadohabitacion->update_estado_habitacion($_POST["est_hab_id"], $_POST["est_hab_nom"]);
                $response['status'] = 'success';
                $response['message'] = 'Estado de habitación actualizada correctamente';
            }
            echo json_encode($response);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = 'Error al procesar la solicitud: ' . $e->getMessage();
            echo json_encode($response);
        }
        break;

    case "listar":
        $datos = $estadohabitacion->get_estado_habitacion();
        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["EST_HAB_NOM"];
            $sub_array[] = $row["EST"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            if ($row["EST"] == 1) {
                $sub_array[] = '<button type="button" onClick="editar(' . $row["EST_HAB_ID"] . ')" id="' . $row["EST_HAB_ID"] . '" class="btn btn-warning btn-icon waves-effect waves-light" title="Editar rol"><i class="ri-edit-2-line"></i></button>';
            } else {
                $sub_array[] = '<button type="button" class="btn btn-warning btn-icon waves-effect waves-light" disabled title="Para editar, primero active el rol"><i class="ri-edit-2-line"></i></button>';
            }
            $sub_array[] = '<button type="button" onClick="eliminar(' . $row["EST_HAB_ID"] . ');" id="' . $row["EST_HAB_ID"] . '" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';
            // Checkbox para cambiar estado
            $checked = $row["EST"] == 1 ? 'checked' : '';
            $sub_array[] = '<div class="form-check form-switch form-switch-custom form-switch-success">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch' . $row["EST_HAB_ID"] . '" ' . $checked . ' onchange="cambiarEstado(' . $row["EST_HAB_ID"] . ', this.checked)">
                                    <label class="form-check-label" for="switch' . $row["EST_HAB_ID"] . '">Yes/No</label>   
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

    case "mostrar":
        $datos = $estadohabitacion->get_estado_habitacion_x_id($_POST["est_hab_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["EST_HAB_ID"] = $row["EST_HAB_ID"];
                $output["EST_HAB_NOM"] = $row["EST_HAB_NOM"];
                $output["EST"] = $row["EST"];
                $output["FECH_CREA"] = $row["FECH_CREA"];
            }
            echo json_encode($output);
        }
        break;

    /* TODO: Cambiar Estado a 0 del Registro */
    case "cambiar_estado":
        $nuevo_estado = $_POST["estado"] == 'true' ? 1 : 0;
        $estadohabitacion->cambiar_estado_estado_habitacion($_POST["est_hab_id"], $nuevo_estado);
        echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
        break;


    case "eliminar":
        $estadohabitacion->delete_estado_habitacion($_POST["est_hab_id"]);
        break;

    /* TODO: Activar estado de habitación (cambiar estado a 1) */


    /* TODO: Listar Combo */
    case "combo":
        $datos = $estadohabitacion->get_estado_habitacion_activos();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option selected>Seleccionar</option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row["EST_HAB_ID"] . "'>" . $row["EST_HAB_NOM"] . "</option>";
            }
            echo $html;
        }
        break;
}
