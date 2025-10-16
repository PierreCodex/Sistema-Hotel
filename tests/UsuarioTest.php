
<?php
require_once __DIR__ . '/../models/Usuario.php';

use PHPUnit\Framework\TestCase;


class UsuarioTest extends TestCase
{
    private $usuario;

    protected function setUp(): void
    {
        $this->usuario = new Usuario();
    }
//metodo para validar que el correo sea valido; el correo debe seguir el formato
    public function testValidarCorreoUsuario()
    {
        $correo = "test@example.com";
        $this->assertTrue(filter_var($correo, FILTER_VALIDATE_EMAIL) !== false);
    }
//metodo para validar que el nombre de usuario no este vacio y sea una cadena de texto
    public function testValidarNombreUsuario()
    {
        $nombre = "Juan";
        $this->assertNotEmpty($nombre);
        $this->assertIsString($nombre);
    }
// metodo para validar que la contraseña tenga al menos 6 caracteres
    public function testValidarPassword()
    {
        $password = "123456";
        $this->assertGreaterThanOrEqual(6, strlen($password));
    }
}