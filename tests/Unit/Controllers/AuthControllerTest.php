<?php

use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias para el controlador Auth
 * 
 * Estas pruebas se enfocan en la lógica del controlador de autenticación,
 * probando principalmente la validación de entrada y métodos públicos.
 */
class AuthControllerTest extends TestCase
{
    private $authController;

    protected function setUp(): void
    {
        // Crear instancia del controlador Auth
        $this->authController = new AuthController();
    }

    /**
     * Test: Verificar validación de entrada con datos válidos
     */
    public function testValidateLoginInputConDatosValidos()
    {
        $correo = 'test@example.com';
        $password = 'password123';
        
        // Usar reflexión para acceder al método privado
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->authController, $correo, $password);
        
        $this->assertTrue($resultado['valid']);
    }

    /**
     * Test: Verificar validación de entrada con correo vacío
     */
    public function testValidateLoginInputConCorreoVacio()
    {
        $correo = '';
        $password = 'password123';
        
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->authController, $correo, $password);
        
        $this->assertFalse($resultado['valid']);
        $this->assertEquals(3, $resultado['error_code']);
    }

    /**
     * Test: Verificar validación de entrada con password vacío
     */
    public function testValidateLoginInputConPasswordVacio()
    {
        $correo = 'test@example.com';
        $password = '';
        
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->authController, $correo, $password);
        
        $this->assertFalse($resultado['valid']);
        $this->assertEquals(4, $resultado['error_code']);
    }

    /**
     * Test: Verificar validación de entrada con ambos campos vacíos
     */
    public function testValidateLoginInputConAmbosVacios()
    {
        $correo = '';
        $password = '';
        
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->authController, $correo, $password);
        
        $this->assertFalse($resultado['valid']);
        $this->assertEquals(2, $resultado['error_code']);
    }

    /**
     * Test: Verificar validación de formato de correo inválido
     */
    public function testValidateLoginInputConCorreoInvalido()
    {
        $correo = 'correo-invalido';
        $password = 'password123';
        
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->authController, $correo, $password);
        
        $this->assertFalse($resultado['valid']);
        $this->assertEquals(5, $resultado['error_code']);
    }

    /**
     * Test: Verificar que isAuthenticated funciona sin sesión
     */
    public function testIsAuthenticatedSinSesion()
    {
        // Limpiar cualquier sesión existente
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        $resultado = $this->authController->isAuthenticated();
        
        $this->assertFalse($resultado);
    }

    /**
     * Test: Verificar que getCurrentUser retorna null sin sesión
     */
    public function testGetCurrentUserSinSesion()
    {
        // Limpiar cualquier sesión existente
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        $resultado = $this->authController->getCurrentUser();
        
        $this->assertNull($resultado);
    }

    /**
     * Test: Verificar que el constructor inicializa correctamente
     */
    public function testConstructorInicializaCorrectamente()
    {
        $controller = new AuthController();
        
        $this->assertInstanceOf(AuthController::class, $controller);
    }

    /**
     * Test: Verificar que el método login existe y es callable
     */
    public function testMetodoLoginExiste()
    {
        $this->assertTrue(method_exists($this->authController, 'login'));
        $this->assertTrue(is_callable([$this->authController, 'login']));
    }

    /**
     * Test: Verificar que el método logout existe y es callable
     */
    public function testMetodoLogoutExiste()
    {
        $this->assertTrue(method_exists($this->authController, 'logout'));
        $this->assertTrue(is_callable([$this->authController, 'logout']));
    }

    /**
     * Test: Verificar que validateLoginInput maneja correctamente null values
     */
    public function testValidateLoginInputConValoresNull()
    {
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->authController, null, null);
        
        $this->assertFalse($resultado['valid']);
        $this->assertEquals(2, $resultado['error_code']);
    }

    /**
     * Test: Verificar que validateLoginInput maneja espacios en blanco
     */
    public function testValidateLoginInputConEspaciosEnBlanco()
    {
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->authController, '   ', '   ');
        
        $this->assertFalse($resultado['valid']);
    }

    /**
     * Test: Verificar que validateLoginInput acepta correos válidos complejos
     */
    public function testValidateLoginInputConCorreosValidosComplejos()
    {
        $correosValidos = [
            'test.email@example.com',
            'user+tag@domain.co.uk',
            'firstname.lastname@company.org'
        ];
        
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        foreach ($correosValidos as $correo) {
            $resultado = $method->invoke($this->authController, $correo, 'password123');
            $this->assertTrue($resultado['valid'], "Falló para el correo: $correo");
        }
    }

    /**
     * Test: Verificar que validateLoginInput rechaza correos inválidos
     */
    public function testValidateLoginInputConCorreosInvalidos()
    {
        $correosInvalidos = [
            'correo-sin-arroba',
            '@domain.com',
            'correo@',
            'correo..doble@domain.com',
            'correo@domain',
            ''
        ];
        
        $reflection = new ReflectionClass($this->authController);
        $method = $reflection->getMethod('validateLoginInput');
        $method->setAccessible(true);
        
        foreach ($correosInvalidos as $correo) {
            $resultado = $method->invoke($this->authController, $correo, 'password123');
            $this->assertFalse($resultado['valid'], "No falló para el correo inválido: $correo");
        }
    }
}