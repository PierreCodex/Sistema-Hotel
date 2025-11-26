<?php
/* TODO: Llamando Clases */
require_once("../config/conexion.php");
require_once("../models/Categoria.php");
/* TODO: Inicializando clase */
$categoria = new Categoria();

switch ($_GET["op"]) {
    /* TODO: Guardar y editar, guardar cuando el ID este vacio, y Actualizar cuando se envie el ID */
    case "guardaryeditar":
        // Validar que el nombre no esté vacío
        if (empty(trim($_POST["cat_nom"]))) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'El nombre de la categoría es obligatorio'
            ));
            break;
        }

        // Verificar si ya existe una categoría con el mismo nombre
        $existe = $categoria->verificar_categoria_existente(
            $_POST["cat_nom"],
            empty($_POST["cat_id"]) ? null : $_POST["cat_id"]
        );

        if ($existe) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Ya existe una categoría con este nombre'
            ));
            break;
        }

        try {
            if (empty($_POST["cat_id"])) {
                $resultado = $categoria->insert_categoria($_POST["cat_nom"]);
                $response['status'] = 'success';
                $response['message'] = 'Categoría registrada correctamente';
            } else {
                $resultado = $categoria->update_categoria($_POST["cat_id"], $_POST["cat_nom"]);
                $response['status'] = 'success';
                $response['message'] = 'Categoría actualizada correctamente';
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
        $datos = $categoria->get_categoria();
        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["CAT_NOM"];
            $sub_array[] = $row["EST"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            if ($row["EST"] == 1) {
                $sub_array[] = '<button type="button" onClick="editar(' . $row["CAT_ID"] . ')" id="' . $row["CAT_ID"] . '" class="btn btn-warning btn-icon waves-effect waves-light" title="Editar rol"><i class="ri-edit-2-line"></i></button>';
            } else {
                $sub_array[] = '<button type="button" class="btn btn-warning btn-icon waves-effect waves-light" disabled title="Para editar, primero active el rol"><i class="ri-edit-2-line"></i></button>';
            }
            $sub_array[] = '<button type="button" onClick="eliminar(' . $row["CAT_ID"] . ');" id="' . $row["CAT_ID"] . '" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';
            // Checkbox para cambiar estado
            $checked = $row["EST"] == 1 ? 'checked' : '';
            $sub_array[] = '<div class="form-check form-switch form-switch-custom form-switch-success">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch' . $row["CAT_ID"] . '" ' . $checked . ' onchange="cambiarEstado(' . $row["CAT_ID"] . ', this.checked)">
                                    <label class="form-check-label" for="switch' . $row["CAT_ID"] . '">Yes/No</label>   
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
        $datos = $categoria->get_categoria_x_cat_id($_POST["cat_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["CAT_ID"] = $row["CAT_ID"];
                $output["CAT_NOM"] = $row["CAT_NOM"];
            }
            echo json_encode($output);
        }
        break;

    /* TODO: Cambiar Estado a 0 del Registro */
    case "cambiar_estado":
        $nuevo_estado = $_POST["estado"] == 'true' ? 1 : 0;
        $categoria->cambiar_estado_categoria($_POST["cat_id"], $nuevo_estado);
        echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
        break;

    case "eliminar":
        $categoria->delete_categoria($_POST["cat_id"]);
        break;

    /* TODO: Activar categoría (cambiar estado a 1) */


    /* TODO: Listar Combo */
    case "combo":
        $datos = $categoria->get_categoria_activa();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option selected>Seleccionar</option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row["CAT_ID"] . "'>" . $row["CAT_NOM"] . "</option>";
            }
            echo $html;
        }
        break;
}