<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/Rol.php';

class RolModelTest extends TestCase
{
    private $rol;

    protected function setUp(): void
    {
        $this->rol = new Rol();
    }

    /** @test */
    public function validar_longitud_acepta_nombre_valido()
    {
        $this->assertTrue($this->rol->validarLongitud("Administrador"));
    }

    /** @test */
    public function validar_longitud_rechaza_nombre_muy_corto()
    {
        $this->assertFalse($this->rol->validarLongitud("AB"));
    }

    /** @test */
    public function validar_longitud_rechaza_nombre_demasiado_largo()
    {
        $nombreLargo = str_repeat("A", 60);
        $this->assertFalse($this->rol->validarLongitud($nombreLargo));
    }
}
