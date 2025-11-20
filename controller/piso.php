<?php
/* TODO: Llamando Clases */
require_once("../config/conexion.php");
require_once("../models/Piso.php");
/* TODO: Inicializando clase */
$piso = new Piso();

switch ($_GET["op"]) {
    /* TODO: Guardar y editar, guardar cuando el ID este vacio, y Actualizar cuando se envie el ID */
    case "guardaryeditar":
        // Validar que el nombre no esté vacío
        if (empty(trim($_POST["piso_nom"]))) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'El nombre del piso es obligatorio'
            ));
            break;
        }

        // Verificar si ya existe un piso con el mismo nombre
        $existe = $piso->verificar_piso_existente(
            $_POST["piso_nom"],
            empty($_POST["piso_id"]) ? null : $_POST["piso_id"]
        );

        if ($existe) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Ya existe un piso con este nombre'
            ));
            break;
        }

        try {
            if (empty($_POST["piso_id"])) {
                $resultado = $piso->insert_piso($_POST["piso_nom"]);
                $response['status'] = 'success';
                $response['message'] = 'Piso registrado correctamente';
            } else {
                $resultado = $piso->update_piso($_POST["piso_id"], $_POST["piso_nom"]);
                $response['status'] = 'success';
                $response['message'] = 'Piso actualizado correctamente';
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
        $datos = $piso->get_piso();
        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["PISO_NOM"];
            $sub_array[] = $row["EST"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            if ($row["EST"] == 1) {
                $sub_array[] = '<button type="button" onClick="editar(' . $row["PISO_ID"] . ')" id="' . $row["PISO_ID"] . '" class="btn btn-warning btn-icon waves-effect waves-light" title="Editar piso"><i class="ri-edit-2-line"></i></button>';
            } else {
                $sub_array[] = '<button type="button" class="btn btn-warning btn-icon waves-effect waves-light" disabled title="Para editar, primero active el piso"><i class="ri-edit-2-line"></i></button>';
            }
               $sub_array[] = '<button type="button" onClick="eliminar(' . $row["PISO_ID"] . ');"  id="' . $row["PISO_ID"] . '" class="btn btn-outline-danger btn-icon waves-effect waves-light"><i class="ri-delete-bin-5-line"></i></button>';
            // Checkbox para cambiar estado
            $checked = $row["EST"] == 1 ? 'checked' : '';
            $sub_array[] = '<div class="form-check form-switch form-switch-custom form-switch-success">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch' . $row["PISO_ID"] . '" ' . $checked . ' onchange="cambiarEstado(' . $row["PISO_ID"] . ', this.checked)">
                                    <label class="form-check-label" for="switch' . $row["PISO_ID"] . '">Yes/No</label>   
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

    /* TODO:Mostrar informacion de registro segun su ID */
    case "mostrar":
        $datos = $piso->get_piso_x_piso_id($_POST["piso_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["PISO_ID"] = $row["PISO_ID"];
                $output["PISO_NOM"] = $row["PISO_NOM"];
            }
            echo json_encode($output);
        }
        break;

    /* TODO: Cambiar Estado a 0 del Registro */
    case "cambiar_estado":
        $nuevo_estado = $_POST["estado"] == 'true' ? 1 : 0;
        $piso->cambiar_estado_piso($_POST["piso_id"], $nuevo_estado);
        echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
        break;

    case "eliminar":
        $piso->delete_piso($_POST["piso_id"]);
        break;

    /* TODO: Activar piso (cambiar estado a 1) */


    /* TODO: Listar Combo */
    case "combo":
        $datos = $piso->get_piso_activo();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option selected>Seleccionar</option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row["PISO_ID"] . "'>" . $row["PISO_NOM"] . "</option>";
            }
            echo $html;
        }
        break;

    /* TODO: Listar pisos activos para vista de recepción */
    case "listar_activos":
        $datos = $piso->get_piso_activo();
        echo json_encode($datos);
        break;
}
?>