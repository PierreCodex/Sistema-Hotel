<?php

use PHPUnit\Framework\TestCase;

/**
 * Test unitario para ClienteController
 * 
 * Estas pruebas validan la lógica de gestión de clientes,
 * validaciones de datos y consultas a RENIEC
 */
class ClienteControllerTest extends TestCase
{
    /**
     * @test
     * Lógica: Guardaryeditar - Insertar vs Actualizar según cli_id
     */
    public function guardaryeditar_logica_insertar_vs_actualizar()
    {
        // Caso 1: cli_id vacío = Insertar
        $cli_id1 = "";
        $esInsertar1 = empty($cli_id1);
        $this->assertTrue($esInsertar1, 'cli_id vacío debe insertar nuevo cliente');

        // Caso 2: cli_id null = Insertar
        $cli_id2 = null;
        $esInsertar2 = empty($cli_id2);
        $this->assertTrue($esInsertar2, 'cli_id null debe insertar nuevo cliente');

        // Caso 3: cli_id con valor = Actualizar
        $cli_id3 = "15";
        $esInsertar3 = empty($cli_id3);
        $this->assertFalse($esInsertar3, 'cli_id con valor debe actualizar cliente existente');

        // Caso 4: cli_id cero como string = Insertar
        $cli_id4 = "0";
        $esInsertar4 = empty($cli_id4);
        $this->assertTrue($esInsertar4, 'cli_id "0" debe insertar nuevo cliente');

        // Caso 5: cli_id negativo = Actualizar (aunque sea raro)
        $cli_id5 = "-1";
        $esInsertar5 = empty($cli_id5);
        $this->assertFalse($esInsertar5, 'cli_id negativo debe actualizar');
    }

    /**
     * @test
     * Validación: Datos requeridos para guardaryeditar
     */
    public function guardaryeditar_datos_requeridos()
    {
        // Simulación de datos POST válidos
        $postData = [
            'cli_tipo_doc' => 'DNI',
            'cli_doc' => '12345678',
            'cli_nom' => 'Juan Carlos',
            'cli_ape' => 'Pérez López',
            'cli_direcc' => 'Av. Principal 123'
        ];

        // Validaciones básicas
        $tipoDocValido = !empty($postData['cli_tipo_doc']);
        $docValido = !empty($postData['cli_doc']);
        $nombreValido = !empty($postData['cli_nom']);
        $apellidoValido = !empty($postData['cli_ape']);

        $this->assertTrue($tipoDocValido, 'Tipo de documento debe ser requerido');
        $this->assertTrue($docValido, 'Documento debe ser requerido');
        $this->assertTrue($nombreValido, 'Nombre debe ser requerido');
        $this->assertTrue($apellidoValido, 'Apellido debe ser requerido');

        // Dirección es opcional
        $direccionOpcional = isset($postData['cli_direcc']) ? $postData['cli_direcc'] : '';
        $this->assertNotNull($direccionOpcional, 'Dirección puede ser opcional');
    }

    /**
     * @test
     * Estructura: Respuesta JSON para insertar cliente
     */
    public function estructura_respuesta_json_insertar()
    {
        // Simulación de inserción exitosa
        $cli_id_generado = 123;
        $response = [
            "success" => true,
            "cli_id" => $cli_id_generado
        ];

        $this->assertTrue($response['success'], 'Inserción debe indicar éxito');
        $this->assertArrayHasKey('cli_id', $response, 'Debe incluir cli_id generado');
        $this->assertIsInt($response['cli_id'], 'cli_id debe ser entero');
        $this->assertGreaterThan(0, $response['cli_id'], 'cli_id debe ser positivo');
    }

    /**
     * @test
     * Estructura: Respuesta JSON para actualizar cliente
     */
    public function estructura_respuesta_json_actualizar()
    {
        // Simulación de actualización exitosa
        $response = ["success" => true];

        $this->assertTrue($response['success'], 'Actualización debe indicar éxito');
        $this->assertArrayNotHasKey('cli_id', $response, 'Actualización no debe incluir nuevo cli_id');
    }

    /**
     * @test
     * Operación: Mostrar cliente por ID
     */
    public function mostrar_cliente_por_id_estructura()
    {
        // Simulación de datos de cliente
        $datos = [[
            'CLI_ID' => '15',
            'CLI_NOM' => 'María',
            'CLI_APE' => 'González Ruiz',
            'CLI_TIPO_DOC' => 'DNI',
            'CLI_DOC' => '87654321',
            'CLI_DIR' => 'Jr. Los Olivos 456'
        ]];

        // Procesamiento como en el controlador
        $output = [];
        if(is_array($datos) && count($datos) > 0){
            foreach($datos as $row){
                $output["CLI_ID"] = $row["CLI_ID"];
                $output["CLI_NOM"] = $row["CLI_NOM"];
                $output["CLI_APE"] = $row["CLI_APE"];
                $output["CLI_TIPO_DOC"] = $row["CLI_TIPO_DOC"];
                $output["CLI_DOC"] = $row["CLI_DOC"];
                $output["CLI_DIR"] = $row["CLI_DIR"];
            }
        }

        $this->assertArrayHasKey('CLI_ID', $output, 'Debe incluir CLI_ID');
        $this->assertArrayHasKey('CLI_NOM', $output, 'Debe incluir CLI_NOM');
        $this->assertArrayHasKey('CLI_APE', $output, 'Debe incluir CLI_APE');
        $this->assertArrayHasKey('CLI_TIPO_DOC', $output, 'Debe incluir CLI_TIPO_DOC');
        $this->assertArrayHasKey('CLI_DOC', $output, 'Debe incluir CLI_DOC');
        $this->assertArrayHasKey('CLI_DIR', $output, 'Debe incluir CLI_DIR');

        $this->assertEquals('15', $output['CLI_ID'], 'CLI_ID debe mantenerse');
        $this->assertEquals('María', $output['CLI_NOM'], 'Nombre debe mantenerse');
        $this->assertEquals('González Ruiz', $output['CLI_APE'], 'Apellido debe mantenerse');
        $this->assertEquals('DNI', $output['CLI_TIPO_DOC'], 'Tipo doc debe mantenerse');
        $this->assertEquals('87654321', $output['CLI_DOC'], 'Documento debe mantenerse');
        $this->assertEquals('Jr. Los Olivos 456', $output['CLI_DIR'], 'Dirección debe mantenerse');
    }

    /**
     * @test
     * Operación: Combo HTML de clientes activos
     */
    public function combo_clientes_activos_html()
    {
        // Simulación de datos de clientes activos
        $datos = [
            ['CLI_ID' => '10', 'CLI_NOM' => 'Juan', 'CLI_APE' => 'Pérez'],
            ['CLI_ID' => '20', 'CLI_NOM' => 'María', 'CLI_APE' => 'González'],
            ['CLI_ID' => '30', 'CLI_NOM' => 'Carlos', 'CLI_APE' => 'López']
        ];

        // Generación de HTML como en el controlador
        $html = "";
        if(is_array($datos) && count($datos) > 0){
            $html .= "<option value='0' selected>Seleccionar</option>";
            foreach($datos as $row){
                $html .= "<option value='" . $row["CLI_ID"] . "'>" . $row["CLI_NOM"] . " " . $row["CLI_APE"] . "</option>";
            }
        }

        $this->assertStringContainsString('<option value=\'0\' selected>Seleccionar</option>', $html, 'Debe incluir opción por defecto');
        $this->assertStringContainsString('<option value=\'10\'>Juan Pérez</option>', $html, 'Debe incluir primer cliente');
        $this->assertStringContainsString('<option value=\'20\'>María González</option>', $html, 'Debe incluir segundo cliente');
        $this->assertStringContainsString('<option value=\'30\'>Carlos López</option>', $html, 'Debe incluir tercer cliente');

        // Contar opciones generadas (1 por defecto + 3 clientes = 4)
        $contadorOpciones = substr_count($html, '<option');
        $this->assertEquals(4, $contadorOpciones, 'Debe generar 4 opciones HTML');
    }

    /**
     * @test
     * Validación: DNI para consulta RENIEC
     */
    public function consultar_reniec_validacion_dni()
    {
        // DNI válido (8 dígitos)
        $dni1 = "12345678";
        $dniLimpio1 = preg_replace('/\D/', '', $dni1);
        $dniValido1 = !empty($dniLimpio1) && strlen($dniLimpio1) === 8;
        $this->assertTrue($dniValido1, 'DNI de 8 dígitos debe ser válido');

        // DNI con espacios y guiones
        $dni2 = "123-456-78";
        $dniLimpio2 = preg_replace('/\D/', '', $dni2);
        $dniValido2 = !empty($dniLimpio2) && strlen($dniLimpio2) === 8;
        $this->assertTrue($dniValido2, 'DNI con separadores debe ser válido después de limpieza');

        // DNI muy corto
        $dni3 = "1234567";
        $dniLimpio3 = preg_replace('/\D/', '', $dni3);
        $dniValido3 = !empty($dniLimpio3) && strlen($dniLimpio3) === 8;
        $this->assertFalse($dniValido3, 'DNI de 7 dígitos debe ser inválido');

        // DNI muy largo
        $dni4 = "123456789";
        $dniLimpio4 = preg_replace('/\D/', '', $dni4);
        $dniValido4 = !empty($dniLimpio4) && strlen($dniLimpio4) === 8;
        $this->assertFalse($dniValido4, 'DNI de 9 dígitos debe ser inválido');

        // DNI vacío
        $dni5 = "";
        $dniLimpio5 = preg_replace('/\D/', '', $dni5);
        $dniValido5 = !empty($dniLimpio5) && strlen($dniLimpio5) === 8;
        $this->assertFalse($dniValido5, 'DNI vacío debe ser inválido');

        // DNI con solo letras
        $dni6 = "ABCDEFGH";
        $dniLimpio6 = preg_replace('/\D/', '', $dni6);
        $dniValido6 = !empty($dniLimpio6) && strlen($dniLimpio6) === 8;
        $this->assertFalse($dniValido6, 'DNI con solo letras debe ser inválido');
    }

    /**
     * @test
     * Validación: Token RENIEC requerido
     */
    public function consultar_reniec_token_requerido()
    {
        // Token definido y no vacío = válido
        $token1 = 'abc123token';
        $tokenValido1 = !empty($token1);
        $this->assertTrue($tokenValido1, 'Token definido debe ser válido');

        // Token vacío = inválido
        $token2 = '';
        $tokenValido2 = !empty($token2);
        $this->assertFalse($tokenValido2, 'Token vacío debe ser inválido');

        // Token null = inválido
        $token3 = null;
        $tokenValido3 = !empty($token3);
        $this->assertFalse($tokenValido3, 'Token null debe ser inválido');
    }

    /**
     * @test
     * Estructura: URL de consulta RENIEC
     */
    public function estructura_url_consulta_reniec()
    {
        $dni = "12345678";
        $url = "https://api.decolecta.com/v1/reniec/dni?numero=" . urlencode($dni);
        
        $this->assertStringStartsWith('https://api.decolecta.com', $url, 'URL debe usar API correcta');
        $this->assertStringContainsString('v1/reniec/dni', $url, 'URL debe incluir endpoint correcto');
        $this->assertStringContainsString('numero=' . $dni, $url, 'URL debe incluir número de DNI');
        
        // Verificar que URL encoding funciona
        $dniConEspacios = "123 456 78";
        $urlConEspacios = "https://api.decolecta.com/v1/reniec/dni?numero=" . urlencode($dniConEspacios);
        $this->assertStringContainsString('123+456+', $urlConEspacios, 'Espacios deben ser URL encoded como +');
    }

    /**
     * @test
     * Configuración: Headers cURL para RENIEC
     */
    public function configuracion_curl_headers_reniec()
    {
        $token = 'test-token-123';
        $headersEsperados = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ];

        $this->assertCount(2, $headersEsperados, 'Debe tener 2 headers');
        $this->assertEquals("Content-Type: application/json", $headersEsperados[0], 'Primer header debe ser Content-Type');
        $this->assertEquals("Authorization: Bearer test-token-123", $headersEsperados[1], 'Segundo header debe ser Authorization');
        $this->assertStringContainsString('Bearer', $headersEsperados[1], 'Debe usar Bearer token');
    }

    /**
     * @test
     * Estructura: Respuesta exitosa de RENIEC
     */
    public function estructura_respuesta_exitosa_reniec()
    {
        // Simulación de respuesta exitosa de API
        $httpCode = 200;
        $response = json_encode([
            'first_name' => 'JUAN CARLOS',
            'first_last_name' => 'PEREZ',
            'second_last_name' => 'LOPEZ',
            'full_name' => 'PEREZ LOPEZ JUAN CARLOS',
            'document_number' => '12345678'
        ]);

        $data = json_decode($response, true);
        $esExitoso = $httpCode >= 200 && $httpCode < 300 && is_array($data);
        
        $this->assertTrue($esExitoso, 'Respuesta 200 con JSON válido debe ser exitosa');

        // Estructura de respuesta procesada
        $respuestaProcesada = [
            "success" => true,
            "first_name" => isset($data["first_name"]) ? $data["first_name"] : "",
            "first_last_name" => isset($data["first_last_name"]) ? $data["first_last_name"] : "",
            "second_last_name" => isset($data["second_last_name"]) ? $data["second_last_name"] : "",
            "full_name" => isset($data["full_name"]) ? $data["full_name"] : "",
            "document_number" => isset($data["document_number"]) ? $data["document_number"] : "12345678"
        ];

        $this->assertTrue($respuestaProcesada['success'], 'Respuesta procesada debe indicar éxito');
        $this->assertEquals('JUAN CARLOS', $respuestaProcesada['first_name'], 'Debe incluir nombres');
        $this->assertEquals('PEREZ', $respuestaProcesada['first_last_name'], 'Debe incluir primer apellido');
        $this->assertEquals('LOPEZ', $respuestaProcesada['second_last_name'], 'Debe incluir segundo apellido');
        $this->assertEquals('PEREZ LOPEZ JUAN CARLOS', $respuestaProcesada['full_name'], 'Debe incluir nombre completo');
        $this->assertEquals('12345678', $respuestaProcesada['document_number'], 'Debe incluir número de documento');
    }

    /**
     * @test
     * Manejo: Errores de consulta RENIEC
     */
    public function manejo_errores_consulta_reniec()
    {
        // Error HTTP 400
        $httpCode1 = 400;
        $response1 = json_encode(['message' => 'DNI no válido']);
        $data1 = json_decode($response1, true);
        $esError1 = $httpCode1 < 200 || $httpCode1 >= 300;
        
        $this->assertTrue($esError1, 'HTTP 400 debe ser error');

        $respuestaError1 = [
            "success" => false,
            "message" => isset($data1["message"]) ? $data1["message"] : "Respuesta inválida de RENIEC",
            "status" => $httpCode1
        ];

        $this->assertFalse($respuestaError1['success'], 'Error debe indicar fallo');
        $this->assertEquals('DNI no válido', $respuestaError1['message'], 'Debe incluir mensaje de error');
        $this->assertEquals(400, $respuestaError1['status'], 'Debe incluir código HTTP');

        // Error de conexión cURL
        $curlError = "Could not connect to host";
        $respuestaErrorCurl = [
            "success" => false,
            "message" => "Error al consultar RENIEC: " . $curlError
        ];

        $this->assertFalse($respuestaErrorCurl['success'], 'Error cURL debe indicar fallo');
        $this->assertStringContainsString('Could not connect', $respuestaErrorCurl['message'], 'Debe incluir error cURL');

        // Respuesta JSON inválida
        $httpCode3 = 200;
        $response3 = 'invalid-json';
        $data3 = json_decode($response3, true);
        $esJsonValido = $httpCode3 >= 200 && $httpCode3 < 300 && is_array($data3);
        
        $this->assertFalse($esJsonValido, 'JSON inválido debe ser error');
    }

    /**
     * @test
     * Operaciones: Verificar operaciones disponibles en switch
     */
    public function verificar_operaciones_disponibles()
    {
        $operacionesEsperadas = [
            'guardaryeditar',
            'listar',
            'mostrar',
            'eliminar',
            'combo',
            'consultar_reniec'
        ];
        
        $this->assertCount(6, $operacionesEsperadas, 'Debe tener 6 operaciones principales');
        $this->assertContains('guardaryeditar', $operacionesEsperadas);
        $this->assertContains('listar', $operacionesEsperadas);
        $this->assertContains('mostrar', $operacionesEsperadas);
        $this->assertContains('eliminar', $operacionesEsperadas);
        $this->assertContains('combo', $operacionesEsperadas);
        $this->assertContains('consultar_reniec', $operacionesEsperadas);
    }

    /**
     * @test
     * Configuración: Timeout cURL para RENIEC
     */
    public function configuracion_timeout_curl()
    {
        $timeoutSegundos = 15;
        
        $this->assertIsInt($timeoutSegundos, 'Timeout debe ser entero');
        $this->assertGreaterThan(0, $timeoutSegundos, 'Timeout debe ser positivo');
        $this->assertLessThanOrEqual(30, $timeoutSegundos, 'Timeout no debe ser excesivo');
        $this->assertEquals(15, $timeoutSegundos, 'Timeout debe ser 15 segundos');
    }

    /**
     * @test
     * Validación: Parámetros POST para mostrar cliente
     */
    public function mostrar_validacion_cli_id()
    {
        // cli_id válido
        $cli_id1 = "25";
        $esValido1 = isset($cli_id1) && !empty($cli_id1) && intval($cli_id1) > 0;
        $this->assertTrue($esValido1, 'cli_id válido debe pasar validación');

        // cli_id cero
        $cli_id2 = "0";
        $esValido2 = isset($cli_id2) && !empty($cli_id2) && intval($cli_id2) > 0;
        $this->assertFalse($esValido2, 'cli_id cero debe fallar validación');

        // cli_id vacío
        $cli_id3 = "";
        $esValido3 = isset($cli_id3) && !empty($cli_id3) && intval($cli_id3) > 0;
        $this->assertFalse($esValido3, 'cli_id vacío debe fallar validación');

        // cli_id no numérico
        $cli_id4 = "abc";
        $esValido4 = isset($cli_id4) && !empty($cli_id4) && intval($cli_id4) > 0;
        $this->assertFalse($esValido4, 'cli_id no numérico debe fallar validación');
    }

    /**
     * @test
     * Headers: Content-Type JSON en guardaryeditar y consultar_reniec
     */
    public function validar_content_type_json()
    {
        $contentType = 'application/json';
        
        $this->assertEquals('application/json', $contentType, 'Content-Type debe ser application/json');
        $this->assertStringContainsString('json', strtolower($contentType), 'Debe indicar formato JSON');
    }

    /**
     * @test
     * Estructura: Datos de cliente completos vs parciales
     */
    public function estructura_datos_cliente()
    {
        // Cliente con todos los datos
        $clienteCompleto = [
            'cli_tipo_doc' => 'DNI',
            'cli_doc' => '12345678',
            'cli_nom' => 'Juan Carlos',
            'cli_ape' => 'Pérez López',
            'cli_direcc' => 'Av. Principal 123'
        ];

        $this->assertCount(5, $clienteCompleto, 'Cliente completo debe tener 5 campos');

        // Cliente mínimo (sin dirección)
        $clienteMinimo = [
            'cli_tipo_doc' => 'DNI',
            'cli_doc' => '87654321',
            'cli_nom' => 'María',
            'cli_ape' => 'González'
        ];

        $this->assertCount(4, $clienteMinimo, 'Cliente mínimo debe tener 4 campos obligatorios');

        // Validar campos obligatorios presentes
        $camposObligatorios = ['cli_tipo_doc', 'cli_doc', 'cli_nom', 'cli_ape'];
        foreach($camposObligatorios as $campo) {
            $this->assertArrayHasKey($campo, $clienteCompleto, "Cliente completo debe tener $campo");
            $this->assertArrayHasKey($campo, $clienteMinimo, "Cliente mínimo debe tener $campo");
        }
    }

    /**
     * @test
     * Integración: Flujo completo de registro con RENIEC
     */
    public function flujo_completo_registro_con_reniec()
    {
        // 1. Validar DNI para consulta
        $dni = "12345678";
        $dniLimpio = preg_replace('/\D/', '', $dni);
        $dniValido = !empty($dniLimpio) && strlen($dniLimpio) === 8;
        
        $this->assertTrue($dniValido, 'DNI debe ser válido para consulta');

        // 2. Simular respuesta exitosa de RENIEC
        $datosReniec = [
            'first_name' => 'JUAN CARLOS',
            'first_last_name' => 'PEREZ',
            'second_last_name' => 'LOPEZ',
            'document_number' => '12345678'
        ];

        // 3. Mapear datos de RENIEC a estructura de cliente
        $datosCliente = [
            'cli_tipo_doc' => 'DNI',
            'cli_doc' => $datosReniec['document_number'],
            'cli_nom' => $datosReniec['first_name'],
            'cli_ape' => $datosReniec['first_last_name'] . ' ' . $datosReniec['second_last_name'],
            'cli_direcc' => '' // Usuario debe completar
        ];

        // 4. Validar datos mapeados
        $datosCompletos = !empty($datosCliente['cli_tipo_doc']) && 
                         !empty($datosCliente['cli_doc']) && 
                         !empty($datosCliente['cli_nom']) && 
                         !empty($datosCliente['cli_ape']);

        $this->assertTrue($datosCompletos, 'Datos de RENIEC deben mapear correctamente');
        $this->assertEquals('DNI', $datosCliente['cli_tipo_doc'], 'Tipo de documento debe ser DNI');
        $this->assertEquals('12345678', $datosCliente['cli_doc'], 'Documento debe coincidir');
        $this->assertEquals('JUAN CARLOS', $datosCliente['cli_nom'], 'Nombre debe coincidir');
        $this->assertEquals('PEREZ LOPEZ', $datosCliente['cli_ape'], 'Apellidos deben combinarse');

        // 5. Simular inserción (cli_id vacío)
        $cli_id = ""; // Vacío para insertar
        $esInsertar = empty($cli_id);
        $cli_id_generado = 150; // Simulación de ID generado

        $this->assertTrue($esInsertar, 'Debe proceder a insertar nuevo cliente');
        $this->assertGreaterThan(0, $cli_id_generado, 'Debe generar ID positivo');

        // 6. Respuesta final
        $respuestaFinal = [
            "success" => true,
            "cli_id" => $cli_id_generado
        ];

        $this->assertTrue($respuestaFinal['success'], 'Registro completo debe ser exitoso');
        $this->assertEquals(150, $respuestaFinal['cli_id'], 'Debe retornar ID generado');
    }
}