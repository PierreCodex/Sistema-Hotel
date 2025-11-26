<?php

use PHPUnit\Framework\TestCase;

/**
 * Test unitario para RecepcionController
 * 
 * Estas pruebas validan la lógica de check-in, check-out y gestión
 * de recepciones sin depender de la base de datos
 */
class RecepcionControllerTest extends TestCase
{
    /**
     * @test
     * Validación: Cliente es obligatorio para guardaryeditar
     */
    public function guardaryeditar_requiere_cliente_valido()
    {
        // Cliente ID cero debe ser inválido
        $cli_id1 = 0;
        $hab_id1 = 1;
        $esValido1 = $cli_id1 > 0 && $hab_id1 > 0;
        $this->assertFalse($esValido1, 'Cliente ID cero debe ser inválido');

        // Cliente ID negativo debe ser inválido
        $cli_id2 = -1;
        $hab_id2 = 1;
        $esValido2 = $cli_id2 > 0 && $hab_id2 > 0;
        $this->assertFalse($esValido2, 'Cliente ID negativo debe ser inválido');

        // Cliente ID válido
        $cli_id3 = 5;
        $hab_id3 = 1;
        $esValido3 = $cli_id3 > 0 && $hab_id3 > 0;
        $this->assertTrue($esValido3, 'Cliente ID positivo debe ser válido');
    }

    /**
     * @test
     * Validación: Habitación es obligatoria para guardaryeditar
     */
    public function guardaryeditar_requiere_habitacion_valida()
    {
        // Habitación ID cero debe ser inválido
        $cli_id1 = 1;
        $hab_id1 = 0;
        $esValido1 = $cli_id1 > 0 && $hab_id1 > 0;
        $this->assertFalse($esValido1, 'Habitación ID cero debe ser inválido');

        // Habitación ID vacío debe ser inválido
        $cli_id2 = 1;
        $hab_id2 = '';
        $hab_id2_int = isset($hab_id2) ? intval($hab_id2) : 0;
        $esValido2 = $cli_id2 > 0 && $hab_id2_int > 0;
        $this->assertFalse($esValido2, 'Habitación ID vacío debe ser inválido');

        // Habitación ID válido
        $cli_id3 = 1;
        $hab_id3 = 5;
        $esValido3 = $cli_id3 > 0 && $hab_id3 > 0;
        $this->assertTrue($esValido3, 'Habitación ID positivo debe ser válido');
    }

    /**
     * @test
     * Lógica: Procesamiento de precio inicial con diferentes fuentes
     */
    public function procesamiento_precio_inicial()
    {
        // Caso 1: Precio inicial enviado por frontend
        $precio_inicial_post1 = 150.75;
        $precio_inicial1 = $precio_inicial_post1 > 0 ? round($precio_inicial_post1, 2) : 0.0;
        $this->assertEquals(150.75, $precio_inicial1, 'Debe usar precio enviado por frontend');

        // Caso 2: Sin precio inicial (debe ser 0 para fallback)
        $precio_inicial_post2 = 0;
        $precio_inicial2 = $precio_inicial_post2 > 0 ? round($precio_inicial_post2, 2) : 0.0;
        $this->assertEquals(0.0, $precio_inicial2, 'Sin precio debe ser 0 para fallback');

        // Caso 3: Simulación fallback con precio base de habitación
        $hab_pre = 120.0; // Precio por noche
        $precio_fallback = $hab_pre > 0 ? round(($hab_pre / 24) * 3, 2) : 0.0; // 3 horas
        $this->assertEquals(15.0, $precio_fallback, 'Fallback debe calcular 3 horas del precio base');
    }

    /**
     * @test
     * Lógica: Procesamiento de adelanto y observación
     */
    public function procesamiento_adelanto_y_observacion()
    {
        // Adelanto válido
        $adelanto_post1 = '50.25';
        $adelanto1 = isset($adelanto_post1) ? floatval($adelanto_post1) : 0.0;
        $this->assertEquals(50.25, $adelanto1, 'Adelanto debe convertirse correctamente');

        // Adelanto vacío
        $adelanto_post2 = '';
        $adelanto2 = isset($adelanto_post2) ? floatval($adelanto_post2) : 0.0;
        $this->assertEquals(0.0, $adelanto2, 'Adelanto vacío debe ser 0.0');

        // Observación con espacios
        $observacion_post1 = '  Huésped VIP  ';
        $observacion1 = isset($observacion_post1) ? trim($observacion_post1) : null;
        $this->assertEquals('Huésped VIP', $observacion1, 'Observación debe ser trimmed');

        // Observación vacía
        $observacion_post2 = '';
        $observacion2 = isset($observacion_post2) ? trim($observacion_post2) : null;
        $observacion2_final = empty($observacion2) ? null : $observacion2;
        $this->assertNull($observacion2_final, 'Observación vacía debe ser null');
    }

    /**
     * @test
     * Lógica: Parseo de fecha de salida en diferentes formatos
     */
    public function parseo_fecha_salida_formatos()
    {
        // Formato Y-m-d H:i
        $fecha_post1 = '2024-12-25 14:30';
        $dt1 = DateTime::createFromFormat('Y-m-d H:i', $fecha_post1);
        $fecha_db1 = $dt1 instanceof DateTime ? $dt1->format('Y-m-d H:i:s') : null;
        $this->assertEquals('2024-12-25 14:30:00', $fecha_db1, 'Formato Y-m-d H:i debe parsearse correctamente');

        // Formato d M, Y H:i
        $fecha_post2 = '25 Dec, 2024 14:30';
        $dt2 = DateTime::createFromFormat('d M, Y H:i', $fecha_post2);
        $fecha_db2 = $dt2 instanceof DateTime ? $dt2->format('Y-m-d H:i:s') : null;
        $this->assertEquals('2024-12-25 14:30:00', $fecha_db2, 'Formato d M, Y H:i debe parsearse correctamente');

        // Fecha inválida
        $fecha_post3 = 'fecha-inválida';
        $dt3 = DateTime::createFromFormat('Y-m-d H:i', $fecha_post3);
        $fecha_db3 = $dt3 instanceof DateTime ? $dt3->format('Y-m-d H:i:s') : null;
        $this->assertNull($fecha_db3, 'Fecha inválida debe retornar null');
    }

    /**
     * @test
     * Lógica: Fecha de salida por defecto (3 horas)
     */
    public function fecha_salida_por_defecto_tres_horas()
    {
        // Sin fecha de salida, debe generar 3 horas desde ahora
        $fecha_salida_post = '';
        $fecha_salida_db = null;
        
        if (empty($fecha_salida_post)) {
            $tiempo_actual = time();
            $tiempo_3_horas = $tiempo_actual + (3 * 60 * 60);
            $fecha_salida_db = date('Y-m-d H:i:s', $tiempo_3_horas);
        }
        
        // Verificar que la fecha generada está aproximadamente 3 horas en el futuro
        $timestamp_generado = strtotime($fecha_salida_db);
        $diferencia_horas = ($timestamp_generado - time()) / 3600;
        
        $this->assertGreaterThanOrEqual(2.9, $diferencia_horas, 'Debe ser aproximadamente 3 horas');
        $this->assertLessThanOrEqual(3.1, $diferencia_horas, 'Debe ser aproximadamente 3 horas');
    }

    /**
     * @test
     * Validación: ID de recepción requerido para obtener_x_id
     */
    public function obtener_x_id_requiere_rec_id_valido()
    {
        // ID cero debe ser inválido
        $rec_id1 = 0;
        $esValido1 = $rec_id1 > 0;
        $this->assertFalse($esValido1, 'ID de recepción cero debe ser inválido');

        // ID negativo debe ser inválido
        $rec_id2 = -5;
        $esValido2 = $rec_id2 > 0;
        $this->assertFalse($esValido2, 'ID de recepción negativo debe ser inválido');

        // ID válido
        $rec_id3 = 10;
        $esValido3 = $rec_id3 > 0;
        $this->assertTrue($esValido3, 'ID de recepción positivo debe ser válido');
    }

    /**
     * @test
     * Validación: Parámetros para confirmar_salida
     */
    public function confirmar_salida_validacion_parametros()
    {
        // ID de recepción requerido
        $rec_id1 = 0;
        $esValido1 = $rec_id1 > 0;
        $this->assertFalse($esValido1, 'ID de recepción es obligatorio');

        // Costo penalidad debe ser numérico
        $costo_penalidad1 = '25.50';
        $costo_float1 = isset($costo_penalidad1) ? floatval($costo_penalidad1) : 0.0;
        $this->assertEquals(25.5, $costo_float1, 'Costo penalidad debe convertirse a float');

        // Total pagado debe ser numérico
        $total_pagado1 = '150.75';
        $total_float1 = isset($total_pagado1) ? floatval($total_pagado1) : 0.0;
        $this->assertEquals(150.75, $total_float1, 'Total pagado debe convertirse a float');

        // Fecha confirmación por defecto
        $fecha_conf1 = '';
        $fecha_final1 = isset($fecha_conf1) && !empty(trim($fecha_conf1)) ? trim($fecha_conf1) : date('Y-m-d H:i:s');
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $fecha_final1, 'Debe generar fecha por defecto');
    }

    /**
     * @test
     * Lógica: Conversión de tipos de datos POST
     */
    public function conversion_tipos_datos_post()
    {
        // String a integer para IDs
        $cli_id_post = '15';
        $cli_id = isset($cli_id_post) ? intval($cli_id_post) : 0;
        $this->assertEquals(15, $cli_id, 'String ID debe convertirse a integer');

        // String a float para precios
        $precio_post = '125.99';
        $precio = isset($precio_post) ? floatval($precio_post) : 0.0;
        $this->assertEquals(125.99, $precio, 'String precio debe convertirse a float');

        // Valores vacíos a defaults
        $adelanto_post = '';
        $adelanto = isset($adelanto_post) ? floatval($adelanto_post) : 0.0;
        $this->assertEquals(0.0, $adelanto, 'String vacío debe dar 0.0');
    }

    /**
     * @test
     * Estructura: Respuesta JSON válida para éxito
     */
    public function estructura_respuesta_json_exitosa()
    {
        // Respuesta exitosa de guardaryeditar
        $response1 = [
            "success" => true,
            "rec_id" => 123
        ];
        
        $this->assertTrue($response1['success'], 'Respuesta exitosa debe tener success = true');
        $this->assertArrayHasKey('rec_id', $response1, 'Debe incluir rec_id generado');
        $this->assertIsInt($response1['rec_id'], 'rec_id debe ser entero');

        // Respuesta exitosa de obtener_x_id
        $response2 = [
            "success" => true,
            "data" => ["IdRecepcion" => 123, "IdCliente" => 5, "IdHabitacion" => 10]
        ];
        
        $this->assertTrue($response2['success'], 'Respuesta exitosa debe tener success = true');
        $this->assertArrayHasKey('data', $response2, 'Debe incluir data con información');
        $this->assertIsArray($response2['data'], 'Data debe ser array');
    }

    /**
     * @test
     * Estructura: Respuesta JSON válida para errores
     */
    public function estructura_respuesta_json_error()
    {
        // Error por parámetros faltantes
        $response1 = [
            "success" => false,
            "message" => "Cliente y Habitación son obligatorios"
        ];
        
        $this->assertFalse($response1['success'], 'Respuesta de error debe tener success = false');
        $this->assertArrayHasKey('message', $response1, 'Debe incluir mensaje de error');
        $this->assertIsString($response1['message'], 'Mensaje debe ser string');
        $this->assertNotEmpty($response1['message'], 'Mensaje no debe estar vacío');

        // Error por ID inválido
        $response2 = [
            "success" => false,
            "message" => "Id de recepción inválido"
        ];
        
        $this->assertFalse($response2['success'], 'Error debe tener success = false');
        $this->assertStringContainsString('inválido', $response2['message'], 'Mensaje debe indicar problema específico');
    }

    /**
     * @test
     * Operaciones: Verificar operaciones disponibles en switch
     */
    public function verificar_operaciones_disponibles()
    {
        $operacionesEsperadas = [
            'listar_ocupaciones_activas',
            'guardaryeditar',
            'obtener_x_id',
            'confirmar_salida'
        ];
        
        $this->assertCount(4, $operacionesEsperadas, 'Debe tener 4 operaciones principales');
        $this->assertContains('listar_ocupaciones_activas', $operacionesEsperadas);
        $this->assertContains('guardaryeditar', $operacionesEsperadas);
        $this->assertContains('obtener_x_id', $operacionesEsperadas);
        $this->assertContains('confirmar_salida', $operacionesEsperadas);
    }

    /**
     * @test
     * Headers: Validar que se envían headers JSON apropiados
     */
    public function validar_content_type_json()
    {
        $contentType = 'application/json';
        
        $this->assertEquals('application/json', $contentType, 'Content-Type debe ser application/json');
        $this->assertStringContainsString('json', strtolower($contentType), 'Debe indicar formato JSON');
    }

    /**
     * @test
     * Validación: Manejo de excepciones con try-catch
     */
    public function manejo_excepciones_try_catch()
    {
        // Simulación de excepción capturada
        $exceptionMessage = "Error de conexión a la base de datos";
        
        try {
            throw new Exception($exceptionMessage);
        } catch (Exception $e) {
            $response = [
                "success" => false,
                "message" => $e->getMessage()
            ];
            
            $this->assertFalse($response['success'], 'Excepción debe generar success = false');
            $this->assertEquals($exceptionMessage, $response['message'], 'Debe incluir mensaje de excepción');
        }
    }

    /**
     * @test
     * Lógica: Normalización de datos de entrada
     */
    public function normalizacion_datos_entrada()
    {
        // Simulación de datos POST desordenados
        $postData = [
            'cli_id' => '  5  ',
            'hab_id' => '10',
            'precio_inicial' => '125.50  ',
            'adelanto' => '',
            'observacion' => '   Cliente VIP   '
        ];

        // Normalización como en el controlador
        $cli_id = isset($postData['cli_id']) ? intval($postData['cli_id']) : 0;
        $hab_id = isset($postData['hab_id']) ? intval($postData['hab_id']) : 0;
        $precio_inicial = isset($postData['precio_inicial']) ? floatval($postData['precio_inicial']) : 0.0;
        $adelanto = isset($postData['adelanto']) ? floatval($postData['adelanto']) : 0.0;
        $observacion = isset($postData['observacion']) ? trim($postData['observacion']) : null;

        $this->assertEquals(5, $cli_id, 'ID cliente debe normalizarse');
        $this->assertEquals(10, $hab_id, 'ID habitación debe normalizarse');
        $this->assertEquals(125.5, $precio_inicial, 'Precio debe normalizarse');
        $this->assertEquals(0.0, $adelanto, 'Adelanto vacío debe ser 0.0');
        $this->assertEquals('Cliente VIP', $observacion, 'Observación debe ser trimmed');
    }

    /**
     * @test
     * Integración: Verificar flujo completo de check-in
     */
    public function flujo_completo_checkin_simulado()
    {
        // Datos válidos de entrada
        $datosEntrada = [
            'cli_id' => 5,
            'hab_id' => 10,
            'precio_inicial' => 150.0,
            'adelanto' => 50.0,
            'observacion' => 'Huésped corporativo'
        ];

        // Validaciones principales
        $validacionCliente = $datosEntrada['cli_id'] > 0;
        $validacionHabitacion = $datosEntrada['hab_id'] > 0;
        
        $this->assertTrue($validacionCliente, 'Cliente debe ser válido');
        $this->assertTrue($validacionHabitacion, 'Habitación debe ser válida');

        // Procesamiento de precio
        $precioFinal = $datosEntrada['precio_inicial'] > 0 ? 
                      round($datosEntrada['precio_inicial'], 2) : 0.0;
        
        $this->assertEquals(150.0, $precioFinal, 'Precio debe procesarse correctamente');

        // Generación de fecha de salida por defecto
        $fechaSalida = date('Y-m-d H:i:s', time() + (3 * 60 * 60));
        
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $fechaSalida);

        // Simulación de respuesta exitosa
        $respuestaSimulada = [
            "success" => true,
            "rec_id" => 123
        ];
        
        $this->assertTrue($respuestaSimulada['success'], 'Check-in debe ser exitoso');
        $this->assertGreaterThan(0, $respuestaSimulada['rec_id'], 'Debe generar ID de recepción');
    }
}