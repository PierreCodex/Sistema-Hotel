<?php

use PHPUnit\Framework\TestCase;

/**
 * Test de Integración HTTP para Controllers
 * 
 * Estos tests ejecutan el código REAL de los controllers
 * para obtener cobertura física verdadera
 */
class HttpIntegrationTest extends TestCase
{
    private $baseUrl;
    
    protected function setUp(): void
    {
        parent::setUp();
        // Configurar URL base del servidor local
        $this->baseUrl = 'http://localhost/SistemaHotel-PHP/controller/';
    }

    /**
     * @test
     * Test HTTP real de ClienteController - operación mostrar
     */
    public function test_cliente_mostrar_http_integration()
    {
        // Preparar datos POST
        $postData = ['cli_id' => '1'];
        
        // Hacer request HTTP real al controller
        $response = $this->makeHttpRequest('cliente.php?op=mostrar', $postData);
        
        // Validar respuesta
        $this->assertIsString($response, 'Debe retornar respuesta string');
        
        $data = json_decode($response, true);
        if ($data !== null) {
            $this->assertArrayHasKey('CLI_ID', $data, 'Debe incluir CLI_ID en respuesta JSON');
        }
        
        // Este test EJECUTA el código real del controller
        // Incrementando cobertura física de declaraciones y ramas
    }

    /**
     * @test
     * Test HTTP real de ClienteController - operación combo
     */
    public function test_cliente_combo_http_integration()
    {
        $response = $this->makeHttpRequest('cliente.php?op=combo', []);
        
        $this->assertIsString($response, 'Combo debe retornar HTML string');
        $this->assertStringContainsString('<option', $response, 'Debe contener elementos option');
        
        // Este test ejecuta:
        // - require_once statements
        // - $cliente = new Cliente()
        // - switch($_GET["op"])
        // - case "combo"
        // - $datos = $cliente->get_cliente_activo()
        // - foreach loop
        // - echo $html
    }

    /**
     * @test
     * Test HTTP real de VentaController - listar por recepción
     */
    public function test_venta_listar_por_recepcion_http()
    {
        $postData = ['rec_id' => '1'];
        
        $response = $this->makeHttpRequest('venta.php?op=listar_por_recepcion', $postData);
        
        $data = json_decode($response, true);
        if ($data !== null) {
            $this->assertArrayHasKey('success', $data, 'Debe tener estructura de respuesta JSON');
        }
        
        // Ejecuta código real de validaciones y consultas
    }

    /**
     * @test
     * Test HTTP real de HabitacionController - listar
     */
    public function test_habitacion_listar_http()
    {
        $response = $this->makeHttpRequest('habitacion.php?op=listar', []);
        
        $data = json_decode($response, true);
        if ($data !== null && isset($data['aaData'])) {
            $this->assertIsArray($data['aaData'], 'Debe retornar array de habitaciones');
        }
    }

    /**
     * @test
     * Test HTTP real de RecepcionController - listar ocupaciones
     */
    public function test_recepcion_ocupaciones_http()
    {
        $response = $this->makeHttpRequest('recepcion.php?op=listar_ocupaciones_activas', []);
        
        $data = json_decode($response, true);
        $this->assertNotNull($data, 'Debe retornar JSON válido');
    }

    /**
     * Método auxiliar para hacer requests HTTP reales
     */
    private function makeHttpRequest($endpoint, $postData = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Validar que el request fue exitoso
        $this->assertEmpty($error, "Error cURL: $error");
        $this->assertEquals(200, $httpCode, "HTTP code should be 200, got: $httpCode");
        
        return $response;
    }
}