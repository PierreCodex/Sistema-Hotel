<?php
/* TODO: Llamando Clases */
require_once("../config/conexion.php");
require_once("../models/Producto.php");
/* TODO: Inicializando clase */
$producto = new Producto();

switch ($_GET["op"]) {
    /* TODO: Guardar y editar, guardar cuando el ID este vacio, y Actualizar cuando se envie el ID */
    case "guardaryeditar":
        // Validar que el nombre no esté vacío
        if (empty(trim($_POST["pro_nom"]))) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'El nombre del producto es obligatorio'
            ));
            break;
        }

        // Verificar si ya existe una categoría con el mismo nombre
        $existe = $producto->verificar_producto_existente(
            $_POST["pro_nom"],
            empty($_POST["pro_id"]) ? null : $_POST["pro_id"]
        );

        if ($existe) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Ya existe un producto con este nombre'
            ));
            break;
        }

        try {
            if (empty($_POST["pro_id"])) {          
                $resultado = $producto->insert_producto($_POST["pro_nom"], $_POST["pro_det"], $_POST["pro_pre"], $_POST["pro_cant"]);
                $response['status'] = 'success';
                $response['message'] = 'Producto registrado correctamente';
            } else {
                $resultado = $producto->update_producto($_POST["pro_id"], $_POST["pro_nom"], $_POST["pro_det"], $_POST["pro_pre"], $_POST["pro_cant"]); 
                $response['status'] = 'success';
                $response['message'] = 'Producto actualizado correctamente';
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
        $datos = $producto->get_producto();
        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["PRO_NOM"];
            $sub_array[] = $row["PRO_DET"];
            $sub_array[] = $row["PRO_PRE"];
            $sub_array[] = $row["PRO_CANT"];   
            $sub_array[] = $row["EST"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            if ($row["EST"] == 1) {
                $sub_array[] = '<button type="button" onClick="editar(' . $row["PRO_ID"] . ')" id="' . $row["PRO_ID"] . '" class="btn btn-warning btn-icon waves-effect waves-light" title="Editar rol"><i class="ri-edit-2-line"></i></button>';   
            } else {
                $sub_array[] = '<button type="button" class="btn btn-warning btn-icon waves-effect waves-light" disabled title="Para editar, primero active el rol"><i class="ri-edit-2-line"></i></button>';
            }
            $sub_array[] = '<button type="button" onClick="eliminar(' . $row["PRO_ID"] . ');" id="' . $row["PRO_ID"] . '" class="btn btn-danger"><i class="bx bx-trash"></i></button>';
            // Checkbox para cambiar estado
            $checked = $row["EST"] == 1 ? 'checked' : '';
            $sub_array[] = '<div class="form-check form-switch form-switch-custom form-switch-success">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch' . $row["PRO_ID"] . '" ' . $checked . ' onchange="cambiarEstado(' . $row["PRO_ID"] . ', this.checked)">
                                    <label class="form-check-label" for="switch' . $row["PRO_ID"] . '">Yes/No</label>   
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
        $datos = $producto->get_producto_x_id($_POST["pro_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["PRO_ID"]   = $row["PRO_ID"];
                $output["PRO_NOM"]  = $row["PRO_NOM"];
                $output["PRO_DET"]  = $row["PRO_DET"];
                $output["PRO_PRE"]  = $row["PRO_PRE"];
                $output["PRO_CANT"] = $row["PRO_CANT"];
                $output["EST"]      = $row["EST"];
            }
            echo json_encode($output);
        }
        break;

    /* TODO: Cambiar Estado a 0 del Registro */
    case "cambiar_estado":
        $nuevo_estado = $_POST["estado"] == 'true' ? 1 : 0;
        $producto->cambiar_estado_producto($_POST["pro_id"], $nuevo_estado);
        echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
        break;

    case "eliminar":
        $producto->delete_producto($_POST["pro_id"]);
        break;

    /* TODO: Activar producto (cambiar estado a 1) */


    /* TODO: Listar Combo */
    case "combo":
        $datos = $producto->get_producto_activo();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option selected>Seleccionar</option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row["PRO_ID"] . "'>" . $row["PRO_NOM"] . "</option>";
            }
            echo $html;
        }
        break;
}