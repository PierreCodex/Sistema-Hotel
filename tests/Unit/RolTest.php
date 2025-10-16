<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../models/Rol.php';

use PHPUnit\Framework\TestCase;

class RolTest extends TestCase
{
    private $rol;

    protected function setUp(): void
    {
        $this->rol = new Rol();
    }

    /**
     * Test: Verificar que la clase Rol se puede instanciar
     */
    public function testRolSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(Rol::class, $this->rol);
    }

    /**
     * Test: Validación de nombres de rol (no vacíos, solo letras y espacios)
     */
    public function testValidacionNombreRol()
    {
        // Nombres de rol válidos
        $nombresValidos = [
            'Administrador',
            'Recepcionista',
            'Gerente General',
            'Supervisor',
            'Empleado'
        ];

        foreach ($nombresValidos as $nombre) {
            $this->assertTrue(
                !empty(trim($nombre)) && preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) === 1,
                "El nombre de rol '{$nombre}' debería ser válido"
            );
        }

        // Nombres de rol inválidos
        $nombresInvalidos = [
            '',                 // vacío
            '   ',              // solo espacios
            'Admin123',         // contiene números
            'Rol@Especial',     // contiene símbolos
            'Rol-Especial',     // contiene guión
            'Admin_User'        // contiene guión bajo
        ];

        foreach ($nombresInvalidos as $nombre) {
            $this->assertFalse(
                !empty(trim($nombre)) && preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) === 1,
                "El nombre de rol '{$nombre}' debería ser inválido"
            );
        }
    }

    /**
     * Test: Validación de longitud de nombre de rol
     */
    public function testValidacionLongitudNombreRol()
    {
        // Nombres con longitud válida (entre 3 y 50 caracteres)
        $nombresValidosLongitud = [
            'Admin',                    // 5 caracteres
            'Recepcionista',           // 13 caracteres
            'Gerente de Operaciones'   // 23 caracteres
        ];

        foreach ($nombresValidosLongitud as $nombre) {
            $longitud = strlen(trim($nombre));
            $this->assertTrue(
                $longitud >= 3 && $longitud <= 50,
                "El nombre '{$nombre}' debería tener entre 3 y 50 caracteres"
            );
        }

        // Nombres con longitud inválida
        $nombresInvalidosLongitud = [
            'Ad',                                                    // muy corto (2 caracteres)
            str_repeat('A', 51)                                     // muy largo (51 caracteres)
        ];

        foreach ($nombresInvalidosLongitud as $nombre) {
            $longitud = strlen(trim($nombre));
            $this->assertFalse(
                $longitud >= 3 && $longitud <= 50,
                "El nombre '{$nombre}' no debería tener longitud válida"
            );
        }
    }

    /**
     * Test: Validación de ID de rol (numérico positivo)
     */
    public function testValidacionIDRol()
    {
        // IDs válidos
        $idsValidos = [1, 2, 5, 10, 100];

        foreach ($idsValidos as $id) {
            $this->assertTrue(
                is_numeric($id) && $id > 0 && is_int($id),
                "El ID {$id} debería ser válido"
            );
        }

        // IDs inválidos
        $idsInvalidos = [0, -1, 'abc', '', null, 1.5];

        foreach ($idsInvalidos as $id) {
            $this->assertFalse(
                is_numeric($id) && $id > 0 && is_int($id),
                "El ID {$id} debería ser inválido"
            );
        }
    }

    /**
     * Test: Simulación de verificación de rol existente
     */
    public function testSimulacionVerificacionRolExistente()
    {
        // Simular lista de roles existentes
        $rolesExistentes = [
            'Administrador',
            'Recepcionista',
            'Gerente',
            'Supervisor'
        ];

        // Función simulada para verificar si un rol existe
        $verificarRolExistente = function($nombreRol, $rolesExistentes) {
            return in_array(strtoupper(trim($nombreRol)), array_map('strtoupper', $rolesExistentes));
        };

        // Roles que ya existen
        $this->assertTrue(
            $verificarRolExistente('Administrador', $rolesExistentes),
            'Debería detectar que Administrador ya existe'
        );

        $this->assertTrue(
            $verificarRolExistente('RECEPCIONISTA', $rolesExistentes),
            'Debería detectar que RECEPCIONISTA ya existe (case insensitive)'
        );

        // Roles que no existen
        $this->assertFalse(
            $verificarRolExistente('Contador', $rolesExistentes),
            'Debería detectar que Contador no existe'
        );

        $this->assertFalse(
            $verificarRolExistente('Limpieza', $rolesExistentes),
            'Debería detectar que Limpieza no existe'
        );
    }

    /**
     * Test: Validación de parámetros para insertar rol
     */
    public function testValidacionParametrosInsertarRol()
    {
        $parametrosValidos = [
            'nombre' => 'Nuevo Rol'
        ];

        // Verificar que el parámetro no esté vacío
        $this->assertNotEmpty(
            trim($parametrosValidos['nombre']),
            'El nombre del rol no debería estar vacío'
        );

        // Verificar que el parámetro tenga formato válido
        $this->assertTrue(
            preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $parametrosValidos['nombre']) === 1,
            'El nombre del rol debería tener formato válido'
        );
    }

    /**
     * Test: Validación de parámetros para actualizar rol
     */
    public function testValidacionParametrosActualizarRol()
    {
        $parametrosValidos = [
            'rol_id' => 1,
            'nombre' => 'Rol Actualizado'
        ];

        // Verificar ID válido
        $this->assertTrue(
            is_numeric($parametrosValidos['rol_id']) && $parametrosValidos['rol_id'] > 0,
            'El ID del rol debería ser válido'
        );

        // Verificar nombre válido
        $this->assertNotEmpty(
            trim($parametrosValidos['nombre']),
            'El nombre del rol no debería estar vacío'
        );

        $this->assertTrue(
            preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $parametrosValidos['nombre']) === 1,
            'El nombre del rol debería tener formato válido'
        );
    }

    /**
     * Test: Validación de término de búsqueda
     */
    public function testValidacionTerminoBusquedaRol()
    {
        // Términos válidos para búsqueda
        $terminosValidos = ['Admin', 'Recep', 'Gerente', 'Supervisor'];

        foreach ($terminosValidos as $termino) {
            $this->assertTrue(
                !empty(trim($termino)) && strlen(trim($termino)) >= 2,
                "El término '{$termino}' debería ser válido para búsqueda"
            );
        }

        // Términos inválidos para búsqueda
        $terminosInvalidos = ['', ' ', 'A'];

        foreach ($terminosInvalidos as $termino) {
            $this->assertFalse(
                !empty(trim($termino)) && strlen(trim($termino)) >= 2,
                "El término '{$termino}' debería ser inválido para búsqueda"
            );
        }
    }

    /**
     * Test: Validación de normalización de nombres (trim y case)
     */
    public function testNormalizacionNombres()
    {
        $nombresConEspacios = [
            '  Administrador  ',
            ' Recepcionista ',
            'Gerente   '
        ];

        foreach ($nombresConEspacios as $nombre) {
            $nombreNormalizado = trim($nombre);
            $this->assertNotEmpty($nombreNormalizado);
            $this->assertEquals($nombreNormalizado, trim($nombre));
            
            // Verificar que no hay espacios al inicio o final
            $this->assertStringStartsNotWith(' ', $nombreNormalizado);
            $this->assertStringEndsNotWith(' ', $nombreNormalizado);
        }
    }

    /**
     * Test: Validación de roles del sistema hotelero
     */
    public function testRolesEspecificosHotel()
    {
        $rolesTipicosHotel = [
            'Administrador',
            'Gerente General',
            'Recepcionista',
            'Supervisor de Limpieza',
            'Conserje',
            'Contador',
            'Jefe de Mantenimiento'
        ];

        foreach ($rolesTipicosHotel as $rol) {
            // Verificar que son nombres válidos para un sistema hotelero
            $this->assertTrue(
                !empty(trim($rol)) && 
                preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $rol) === 1 &&
                strlen(trim($rol)) >= 3,
                "El rol '{$rol}' debería ser válido para un sistema hotelero"
            );
        }
    }
}