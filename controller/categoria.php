<?php
    /* TODO: Llamando Clases */
    require_once("../config/conexion.php");
    require_once("../models/Categoria.php");
    /* TODO: Inicializando clase */
    $categoria = new Categoria();

    switch($_GET["op"]){
        /* TODO: Guardar y editar, guardar cuando el ID este vacio, y Actualizar cuando se envie el ID */
        case "guardaryeditar":
            // Validar que el nombre no esté vacío
            if(empty(trim($_POST["cat_nom"]))){
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'El nombre de la categoría es obligatorio'
                ));
                break;
            }

            // Verificar si ya existe una categoría con el mismo nombre
            $existe = $categoria->verificar_categoria_existente($_POST["cat_nom"], 
                empty($_POST["cat_id"]) ? null : $_POST["cat_id"]);
            
            if($existe){
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Ya existe una categoría con este nombre'
                ));
                break;
            }

            try {
                if(empty($_POST["cat_id"])){
                    $categoria->insert_categoria($_POST["cat_nom"]);
                    echo json_encode(array(
                        'status' => 'success',
                        'message' => 'Categoría registrada correctamente'
                    ));
                } else {
                    $categoria->update_categoria($_POST["cat_id"], $_POST["cat_nom"]);
                    echo json_encode(array(
                        'status' => 'success',
                        'message' => 'Categoría actualizada correctamente'
                    ));
                }
            } catch (Exception $e) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
                ));
            }
            break;

        /* TODO: Listado de registros formato JSON para Datatable JS */
        case "listar":
            $datos = $categoria->get_categoria();
            $data = Array();
            foreach($datos as $row){
                $sub_array = array();
                $sub_array[] = $row["CAT_NOM"];
                $sub_array[] = $row["FECH_CREA"];
                $sub_array[] = '<button type="button" onClick="editar('.$row["CAT_ID"].');" id="'.$row["CAT_ID"].'" class="btn btn-warning btn-sm"><i class="bx bx-edit-alt"></i></button>';
                $sub_array[] = '<button type="button" onClick="eliminar('.$row["CAT_ID"].');" id="'.$row["CAT_ID"].'" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';
                $data[] = $sub_array;
            }

            $results = array(
                "sEcho"=>1,
                "iTotalRecords"=>count($data),
                "iTotalDisplayRecords"=>count($data),
                "aaData"=>$data);
            echo json_encode($results);
            break;

        /* TODO:Mostrar informacion de registro segun su ID */
        case "mostrar":
            $datos=$categoria->get_categoria_x_cat_id($_POST["cat_id"]);
            if (is_array($datos)==true and count($datos)>0){
                foreach($datos as $row){
                    $output["CAT_ID"] = $row["CAT_ID"];
                    $output["CAT_NOM"] = $row["CAT_NOM"];
                }
                echo json_encode($output);
            }
            break;

        /* TODO: Cambiar Estado a 0 del Registro */
        case "eliminar":
            $categoria->delete_categoria($_POST["cat_id"]);
            break;

        /* TODO: Activar categoría (cambiar estado a 1) */


        /* TODO: Listar Combo */
        case "combo":
            $datos=$categoria->get_categoria();
            if(is_array($datos)==true and count($datos)>0){
                $html="";
                $html.="<option selected>Seleccionar</option>";
                foreach($datos as $row){
                    $html.= "<option value='".$row["CAT_ID"]."'>".$row["CAT_NOM"]."</option>";
                }
                echo $html;
            }
            break;

    }
?>