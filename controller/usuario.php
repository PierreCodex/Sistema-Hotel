<?php
    /* TODO: Llamando Clases */
    require_once("../config/conexion.php");
    require_once("../models/Usuario.php");
    require_once("../models/Rol.php");
    /* TODO: Inicializando clase */
    $usuario = new Usuario();
    $rol = new Rol();

    switch($_GET["op"]){
        /* TODO: Guardar y editar, guardar cuando el ID este vacio, y Actualizar cuando se envie el ID */
        case "guardaryeditar":
            if(empty($_POST["usu_id"])){
                // Insertar nuevo usuario
                $usuario->insert_usuario($_POST["usu_nom"], $_POST["usu_ape"], $_POST["usu_dni"], $_POST["usu_correo"], $_POST["usu_pass"], $_POST["rol_id"]);
            }else{
                // Actualizar usuario existente
                
                /*
                 * FUNCIONALIDAD: PRESERVACIÓN DE CONTRASEÑA EN MODO EDICIÓN
                 * 
               */
                if(isset($_POST["usu_pass"]) && !empty($_POST["usu_pass"])){
                    // CASO 1: Se envió contraseña → Actualizar incluyendo la nueva contraseña
                    // Usado para: usuarios nuevos o cuando se quiere cambiar la contraseña
                    $usuario->update_usuario($_POST["usu_id"], $_POST["usu_nom"], $_POST["usu_ape"], $_POST["usu_dni"], $_POST["usu_correo"], $_POST["usu_pass"], $_POST["rol_id"]);
                } else {
                    // CASO 2: NO se envió contraseña → Actualizar SIN tocar la contraseña
                    // Usado para: edición de usuario donde se preserva la contraseña original
                    $usuario->update_usuario_sin_password($_POST["usu_id"], $_POST["usu_nom"], $_POST["usu_ape"], $_POST["usu_dni"], $_POST["usu_correo"], $_POST["rol_id"]);
                }
            }
            break;

        /* TODO: Listado de registros formato JSON para Datatable JS */
        case "listar":
            try {
                $current_user_id = $_SESSION["IdUsuario"];
                $datos = $usuario->get_usuario($current_user_id);
                $data = Array();
                
                if (is_array($datos) && count($datos) > 0) {
                    foreach($datos as $row){
                        $sub_array = array();
                        $sub_array[] = $row["USU_NOM"];
                        $sub_array[] = $row["USU_APE"];
                        $sub_array[] = $row["USU_DNI"];
                        $sub_array[] = $row["USU_CORREO"];
                        $sub_array[] = $row["ROL_NOM"];
                        $sub_array[] = $row["FECH_CREA"];
                        $sub_array[] = '<button type="button" onClick="editar('.$row["USU_ID"].');"  id="'.$row["USU_ID"].'" class="btn btn-outline-warning btn-icon waves-effect waves-light"><i class="ri-edit-line"></i></button>';
                        $sub_array[] = '<button type="button" onClick="eliminar('.$row["USU_ID"].');"  id="'.$row["USU_ID"].'" class="btn btn-outline-danger btn-icon waves-effect waves-light"><i class="ri-delete-bin-5-line"></i></button>';
                        $data[] = $sub_array;
                    }
                }

                $results = array(
                    "sEcho"=>1,
                    "iTotalRecords"=>count($data),
                    "iTotalDisplayRecords"=>count($data),
                    "aaData"=>$data);
                echo json_encode($results);
            } catch (Exception $e) {
                echo json_encode(array("error" => $e->getMessage()));
            }
            break;

        /* TODO:Mostrar informacion de registro segun su ID */
        case "mostrar":
            $datos=$usuario->get_usuario_x_usu_id($_POST["usu_id"]);
            if (is_array($datos)==true and count($datos)>0){
                foreach($datos as $row){
                    $output["USU_ID"] = $row["USU_ID"];
                    $output["USU_NOM"] = $row["USU_NOM"];
                    $output["USU_APE"] = $row["USU_APE"];
                    $output["USU_DNI"] = $row["USU_DNI"];
                    $output["USU_CORREO"] = $row["USU_CORREO"];
                    $output["USU_PASS"] = $row["USU_PASS"];
                    $output["ROL_ID"] = $row["ROL_ID"];
                }
                echo json_encode($output);
            }
            break;

        /* TODO: Cambiar Estado a 0 del Registro */
        case "eliminar":
            $usuario->delete_usuario($_POST["usu_id"]);
            break;

        /* TODO: Activar usuario (cambiar estado a 1) */
        case "activar":
            $usuario->activar_usuario($_POST["usu_id"]);
            break;

        /* TODO: Buscar usuarios */
        case "buscar":
            $datos=$usuario->buscar_usuario($_POST["buscar"]);
            $data=Array();
            foreach($datos as $row){
                $sub_array = array();
                $sub_array[] = $row["USU_NOM"];
                $sub_array[] = $row["USU_APE"];
                $sub_array[] = $row["USU_DNI"];
                $sub_array[] = $row["USU_CORREO"];
                $sub_array[] = $row["ROL_NOM"];
                $sub_array[] = $row["FECH_CREA"];
                $sub_array[] = '<button type="button" onClick="editar('.$row["USU_ID"].')" id="'.$row["USU_ID"].'" class="btn btn-warning btn-icon waves-effect waves-light"><i class="ri-edit-2-line"></i></button>';
                $sub_array[] = '<button type="button" onClick="eliminar('.$row["USU_ID"].')" id="'.$row["USU_ID"].'" class="btn btn-danger btn-icon waves-effect waves-light"><i class="ri-delete-bin-5-line"></i></button>';
                $data[] = $sub_array;
            }

            $results = array(
                "sEcho"=>1,
                "iTotalRecords"=>count($data),
                "iTotalDisplayRecords"=>count($data),
                "aaData"=>$data);
            echo json_encode($results);
            break;

        /* TODO: Actualizar contraseña */
        case "actualizar_password":
            $usuario->update_password($_POST["usu_id"], $_POST["usu_pass"]);
            break;

        /* TODO: Listar Combo de Usuarios */
        case "combo":
            $datos=$usuario->get_usuario_combo();
            if(is_array($datos)==true and count($datos)>0){
                $html="";
                $html.="<option selected>Seleccionar</option>";
                foreach($datos as $row){
                    $html.= "<option value='".$row["IDUSUARIO"]."'>".$row["NOMBRE"]." ".$row["APELLIDO"]."</option>";
                }
                echo $html;
            }
            break;

        /* TODO: Listar Combo de Roles */
        case "combo_rol":
            $datos=$rol->get_rol();
            if(is_array($datos)==true and count($datos)>0){
                $html="";
                $html.="<option selected>Seleccionar</option>";
                foreach($datos as $row){
                    $html.= "<option value='".$row["ROL_ID"]."'>".$row["ROL_NOM"]."</option>";
                }
                echo $html;
            }
            break;

    }
?>