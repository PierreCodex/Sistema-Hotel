<?php
/* TODO: Llamando Clases */
require_once("../config/conexion.php");
require_once("../models/Cliente.php");
// Configuración externa (token de APIs)
if (file_exists("../config/external.php")) {
    require_once("../config/external.php");
}
/* TODO: Inicializando clase */
$cliente = new Cliente();

switch ($_GET["op"]) {
    /* TODO: Guardar y editar, guardar cuando el ID este vacio, y Actualizar cuando se envie el ID */
    case "guardaryeditar":
        header('Content-Type: application/json');
        if (empty($_POST["cli_id"])) {
            $cli_id = $cliente->insert_cliente(
                $_POST["cli_tipo_doc"],
                $_POST["cli_doc"],
                $_POST["cli_nom"],
                $_POST["cli_ape"],
                $_POST["cli_direcc"]
            );
            echo json_encode(["success" => true, "cli_id" => $cli_id]);
        } else {
            $cliente->update_cliente(
                $_POST["cli_id"],
                $_POST["cli_tipo_doc"],
                $_POST["cli_doc"],
                $_POST["cli_nom"],
                $_POST["cli_ape"],
                $_POST["cli_direcc"]
            );
            echo json_encode(["success" => true]);
        }
        break;

    /* TODO: Listado de registros formato JSON para Datatable JS */
    case "listar":
        $datos = $cliente->get_cliente_activo();
        $data = [];
        foreach ($datos as $row) {
            $sub_array = [];
            $sub_array[] = $row["CLI_TIPO_DOC"];
            $sub_array[] = $row["CLI_DOC"];
            $sub_array[] = $row["CLI_NOM"];
            $sub_array[] = $row["CLI_APE"];
            $sub_array[] = $row["CLI_DIR"];
            $sub_array[] = $row["EST"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            if ($row["EST"] == 1) {
                $sub_array[] = '<button type="button" onClick="editar(' . $row["CLI_ID"] . ')" id="' . $row["CLI_ID"] . '" class="btn btn-warning btn-icon waves-effect waves-light" title="Editar rol"><i class="ri-edit-2-line"></i></button>';
            } else {
                $sub_array[] = '<button type="button" class="btn btn-warning btn-icon waves-effect waves-light" disabled title="Para editar, primero active el rol"><i class="ri-edit-2-line"></i></button>';
            }
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

    /* Verificar si el documento ya existe */
    case "verificar_documento":
        header('Content-Type: application/json');
        $cli_doc = isset($_POST['cli_doc']) ? trim($_POST['cli_doc']) : '';
        $cli_id = isset($_POST['cli_id']) ? intval($_POST['cli_id']) : null;
        
        if (empty($cli_doc)) {
            echo json_encode(['success' => false, 'message' => 'Documento vacío']);
            break;
        }
        
        $resultado = $cliente->verificar_documento_existe($cli_doc, $cli_id);
        echo json_encode(['success' => true, 'data' => $resultado]);
        break;

    /* TODO:Mostrar informacion de registro segun su ID */
    case "mostrar":
        $datos = $cliente->get_cliente_x_cli_id($_POST["cli_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["CLI_ID"] = $row["CLI_ID"];
                $output["CLI_NOM"] = $row["CLI_NOM"];
                $output["CLI_APE"] = $row["CLI_APE"];
                $output["CLI_TIPO_DOC"] = $row["CLI_TIPO_DOC"];
                $output["CLI_DOC"] = $row["CLI_DOC"];
                $output["CLI_DIR"] = $row["CLI_DIR"];
            }
            echo json_encode($output);
        }
        break;

    /* TODO: Cambiar Estado a 0 del Registro */
    case "eliminar";

        break;

    /* TODO: Combo de Listado de Clientes */
    case "combo";
        $datos = $cliente->get_cliente_activo();
        if (is_array($datos) == true and count($datos) > 0) {
            $html = "";
            $html .= "<option value='0' selected>Seleccionar</option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row["CLI_ID"] . "'>" . $row["CLI_NOM"] . " " . $row["CLI_APE"] . "</option>";
            }
            echo $html;
        }
        break;

    case "consultar_reniec":
        header('Content-Type: application/json');
        $dni = isset($_GET["numero"]) ? preg_replace('/\D/', '', $_GET["numero"]) : "";
        if (empty($dni) || strlen($dni) !== 8) {
            echo json_encode(["success" => false, "message" => "DNI inválido. Debe tener 8 dígitos."]);
            break;
        }

        if (!defined('CODART_TOKEN') || empty(CODART_TOKEN)) {
            echo json_encode(["success" => false, "message" => "Token no configurado. Configure CODART_TOKEN en config/external.php"]);
            break;
        }

        $url = "https://api.codart.cgrt.net/api/v1/consultas/reniec/dni/" . urlencode($dni);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . CODART_TOKEN
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(["success" => false, "message" => "Error al consultar RENIEC: " . $curlErr]);
            break;
        }

        $data = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && is_array($data) && isset($data["result"])) {
            $info = $data["result"];
            echo json_encode([
                "success" => true,
                "first_name" => $info["first_name"] ?? "",
                "first_last_name" => $info["first_last_name"] ?? "",
                "second_last_name" => $info["second_last_name"] ?? "",
                "full_name" => $info["full_name"] ?? "",
                "document_number" => $info["document_number"] ?? $dni
            ]);
        } else {
            $msg = $data["message"] ?? "Respuesta inválida de RENIEC";
            echo json_encode(["success" => false, "message" => $msg, "status" => $httpCode]);
        }
        break;

    case "consultar_ruc":
        header('Content-Type: application/json');
        $ruc = isset($_GET["numero"]) ? preg_replace('/\D/', '', $_GET["numero"]) : "";
        if (empty($ruc) || strlen($ruc) !== 11) {
            echo json_encode(["success" => false, "message" => "RUC inválido. Debe tener 11 dígitos."]);
            break;
        }

        if (!defined('CODART_TOKEN') || empty(CODART_TOKEN)) {
            echo json_encode(["success" => false, "message" => "Token no configurado. Configure CODART_TOKEN en config/external.php"]);
            break;
        }

        $url = "https://api.codart.cgrt.net/api/v1/consultas/sunat/ruc/" . urlencode($ruc);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . CODART_TOKEN
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(["success" => false, "message" => "Error al consultar SUNAT: " . $curlErr]);
            break;
        }

        $data = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && is_array($data) && isset($data["result"])) {
            $info = $data["result"];
            echo json_encode([
                "success" => true,
                "ruc" => $info["numero_documento"] ?? $ruc,
                "razon_social" => $info["razon_social"] ?? "",
                "estado" => $info["estado"] ?? "",
                "condicion" => $info["condicion"] ?? "",
                "direccion" => $info["direccion"] ?? "",
                "ubigeo" => $info["ubigeo"] ?? "",
                "departamento" => $info["departamento"] ?? "",
                "provincia" => $info["provincia"] ?? "",
                "distrito" => $info["distrito"] ?? "",
                "tipo" => $info["tipo"] ?? "",
                "actividad_economica" => $info["actividad_economica"] ?? "",
                "es_agente_retencion" => $info["es_agente_retencion"] ?? false,
                "es_buen_contribuyente" => $info["es_buen_contribuyente"] ?? false
            ]);
        } else {
            $msg = $data["message"] ?? "Respuesta inválida de SUNAT";
            echo json_encode(["success" => false, "message" => $msg, "status" => $httpCode]);
        }
        break;
}
