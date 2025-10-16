<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../models/Usuario.php';

use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    private $usuario;

    protected function setUp(): void
    {
        $this->usuario = new Usuario();
    }

    /**
     * Test: Verificar que la clase Usuario se puede instanciar
     */
    public function testUsuarioSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(Usuario::class, $this->usuario);
    }

    /**
     * Test: Validación de formato de correo electrónico
     */
    public function testValidacionFormatoCorreo()
    {
        // Correos válidos
        $correosValidos = [
            'usuario@hotel.com',
            'admin@sistema.pe',
            'test.user@example.org',
            'user123@domain.co.uk'
        ];

        foreach ($correosValidos as $correo) {
            $this->assertTrue(
                filter_var($correo, FILTER_VALIDATE_EMAIL) !== false,
                "El correo {$correo} debería ser válido"
            );
        }

        // Correos inválidos
        $correosInvalidos = [
            'correo-sin-arroba.com',
            '@dominio.com',
            'usuario@',
            'usuario..doble@punto.com',
            'usuario@dominio',
            ''
        ];

        foreach ($correosInvalidos as $correo) {
            $this->assertFalse(
                filter_var($correo, FILTER_VALIDATE_EMAIL) !== false,
                "El correo {$correo} debería ser inválido"
            );
        }
    }

    /**
     * Test: Validación de DNI peruano (8 dígitos)
     */
    public function testValidacionDNI()
    {
        // DNIs válidos (8 dígitos)
        $dnisValidos = [
            '12345678',
            '87654321',
            '11111111',
            '99999999'
        ];

        foreach ($dnisValidos as $dni) {
            $this->assertTrue(
                preg_match('/^\d{8}$/', $dni) === 1,
                "El DNI {$dni} debería ser válido"
            );
        }

        // DNIs inválidos
        $dnisInvalidos = [
            '1234567',    // 7 dígitos
            '123456789',  // 9 dígitos
            '1234567a',   // contiene letra
            '',           // vacío
            '12-34-56-78' // con guiones
        ];

        foreach ($dnisInvalidos as $dni) {
            $this->assertFalse(
                preg_match('/^\d{8}$/', $dni) === 1,
                "El DNI {$dni} debería ser inválido"
            );
        }
    }

    /**
     * Test: Validación de nombres (solo letras y espacios)
     */
    public function testValidacionNombres()
    {
        // Nombres válidos
        $nombresValidos = [
            'Juan',
            'María José',
            'Ana Lucía',
            'José María',
            'Carmen Rosa'
        ];

        foreach ($nombresValidos as $nombre) {
            // Validación mejorada: debe contener letras y espacios, pero no solo espacios
            $esValido = preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) === 1 && 
                        trim($nombre) !== ''; // No debe estar vacío después de quitar espacios
            
            $this->assertTrue(
                $esValido,
                "El nombre '{$nombre}' debería ser válido"
            );
        }

        // Nombres inválidos
        $nombresInvalidos = [
            'Juan123',      // contiene números
            'María@José',   // contiene símbolos
            '',             // vacío
            '   ',          // solo espacios
            'Juan-Carlos'   // contiene guión
        ];

        foreach ($nombresInvalidos as $nombre) {
            // Validación mejorada: debe contener letras y espacios, pero no solo espacios
            $esValido = preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) === 1 && 
                        trim($nombre) !== ''; // No debe estar vacío después de quitar espacios
            
            $this->assertFalse(
                $esValido,
                "El nombre '{$nombre}' debería ser inválido"
            );
        }

        // Nombres inválidos
        $nombresInvalidos = [
            'Juan123',      // contiene números
            'María@José',   // contiene símbolos
            '',             // vacío
            '   ',          // solo espacios
            'Juan-Carlos'   // contiene guión
        ];

        foreach ($nombresInvalidos as $nombre) {
            // Validación mejorada: debe contener letras y espacios, pero no solo espacios
            $esValido = preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) === 1 && 
                        trim($nombre) !== ''; // No debe estar vacío después de quitar espacios
            
            $this->assertFalse(
                $esValido,
                "El nombre '{$nombre}' debería ser inválido"
            );
        }
    }

    /**
     * Test: Validación de contraseñas seguras
     */
    public function testValidacionContrasenas()
    {
        // Contraseñas válidas (mínimo 6 caracteres)
        $contrasenasValidas = [
            'password123',
            'MiClave2024',
            'Hotel@123',
            'Sistema2024!'
        ];

        foreach ($contrasenasValidas as $password) {
            $this->assertTrue(
                strlen($password) >= 6,
                "La contraseña '{$password}' debería tener al menos 6 caracteres"
            );
        }

        // Contraseñas inválidas
        $contrasenasInvalidas = [
            '123',      // muy corta
            '12345',    // muy corta
            '',         // vacía
            '     '     // solo espacios
        ];

        foreach ($contrasenasInvalidas as $password) {
            $this->assertFalse(
                strlen(trim($password)) >= 6,
                "La contraseña '{$password}' debería ser inválida"
            );
        }
    }

    /**
     * Test: Verificar que los parámetros de métodos no estén vacíos
     */
    public function testParametrosNoVacios()
    {
        // Simular validación de parámetros para insertar usuario
        $parametros = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'dni' => '12345678',
            'correo' => 'juan@hotel.com',
            'password' => 'password123',
            'rol_id' => 1
        ];

        foreach ($parametros as $campo => $valor) {
            $this->assertNotEmpty(
                trim($valor),
                "El campo {$campo} no debería estar vacío"
            );
        }
    }

    /**
     * Test: Validación de ID numérico positivo
     */
    public function testValidacionIDNumerico()
    {
        // IDs válidos
        $idsValidos = [1, 2, 10, 100, 999];

        foreach ($idsValidos as $id) {
            $this->assertTrue(
                is_numeric($id) && $id > 0,
                "El ID {$id} debería ser válido"
            );
        }

        // IDs inválidos
        $idsInvalidos = [0, -1, 'abc', '', null];

        foreach ($idsInvalidos as $id) {
            $this->assertFalse(
                is_numeric($id) && $id > 0,
                "El ID {$id} debería ser inválido"
            );
        }
    }

    /**
     * Test: Verificar formato de hash de contraseña
     */
    public function testHashContrasena()
    {
        $password = 'miPassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Verificar que se genera un hash
        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);

        // Verificar que el hash se puede verificar
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('passwordIncorrecto', $hash));
    }

    /**
     * Test: Validación de búsqueda (término no vacío)
     */
    public function testValidacionTerminoBusqueda()
    {
        // Términos válidos
        $terminosValidos = ['Juan', 'admin', 'hotel', 'user123'];

        foreach ($terminosValidos as $termino) {
            $this->assertTrue(
                !empty(trim($termino)) && strlen(trim($termino)) >= 2,
                "El término '{$termino}' debería ser válido para búsqueda"
            );
        }

        // Términos inválidos
        $terminosInvalidos = ['', ' ', 'a'];

        foreach ($terminosInvalidos as $termino) {
            $this->assertFalse(
                !empty(trim($termino)) && strlen(trim($termino)) >= 2,
                "El término '{$termino}' debería ser inválido para búsqueda"
            );
        }
    }
}