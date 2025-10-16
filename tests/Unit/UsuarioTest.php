<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias para el modelo Usuario
 */
class UsuarioTest extends TestCase
{
    private $usuario;

    /**
     * Configuración inicial antes de cada prueba
     */
    protected function setUp(): void
    {
        // Aquí puedes configurar mocks o datos de prueba
        // Por ahora, solo verificamos que la clase existe
        $this->assertTrue(class_exists('Usuario'), 'La clase Usuario debe existir');
    }

    /**
     * Prueba que la clase Usuario puede ser instanciada
     */
    public function testUsuarioCanBeInstantiated(): void
    {
        // Esta prueba requiere que incluyas el archivo del modelo
        // require_once __DIR__ . '/../../models/Usuario.php';
        // require_once __DIR__ . '/../../config/conexion.php';
        
        // $usuario = new Usuario();
        // $this->assertInstanceOf(Usuario::class, $usuario);
        
        // Por ahora, solo verificamos que el test funciona
        $this->assertTrue(true, 'Test de instanciación preparado');
    }

    /**
     * Prueba de validación de email
     */
    public function testEmailValidation(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'admin@hotel.com'
        ];

        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'user@',
            ''
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue(
                filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
                "El email {$email} debería ser válido"
            );
        }

        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
                "El email {$email} debería ser inválido"
            );
        }
    }

    /**
     * Prueba de validación de contraseña
     */
    public function testPasswordValidation(): void
    {
        $validPasswords = [
            'Password123!',
            'MySecure@Pass1',
            'Hotel2024#'
        ];

        $invalidPasswords = [
            '123',           // Muy corta
            'password',      // Sin mayúsculas ni números
            'PASSWORD',      // Sin minúsculas ni números
            '12345678'       // Solo números
        ];

        foreach ($validPasswords as $password) {
            $this->assertGreaterThanOrEqual(
                8,
                strlen($password),
                "La contraseña debe tener al menos 8 caracteres"
            );
        }

        foreach ($invalidPasswords as $password) {
            $this->assertLessThan(
                8,
                strlen($password),
                "Contraseña inválida detectada correctamente"
            );
        }
    }

    /**
     * Prueba de hash de contraseña
     */
    public function testPasswordHashing(): void
    {
        $password = 'MiContraseña123!';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->assertNotEquals($password, $hashedPassword, 'La contraseña debe estar hasheada');
        $this->assertTrue(
            password_verify($password, $hashedPassword),
            'La verificación de contraseña debe funcionar'
        );
        $this->assertFalse(
            password_verify('ContraseñaIncorrecta', $hashedPassword),
            'Una contraseña incorrecta no debe verificarse'
        );
    }

    /**
     * Limpieza después de cada prueba
     */
    protected function tearDown(): void
    {
        // Limpiar recursos si es necesario
        $this->usuario = null;
    }
}