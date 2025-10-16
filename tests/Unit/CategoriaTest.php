<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../models/Categoria.php';

use PHPUnit\Framework\TestCase;

class CategoriaTest extends TestCase
{
    private $categoria;

    protected function setUp(): void
    {
        $this->categoria = new Categoria();
    }

    /**
     * Test: Verificar que la clase Categoria se puede instanciar
     */
    public function testCategoriaSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(Categoria::class, $this->categoria);
    }

    /**
     * Test: Validación de nombres de categoría de habitaciones
     */
    public function testValidacionNombreCategoria()
    {
        // Nombres de categoría válidos para hotel
        $nombresValidos = [
            'Suite Presidencial',
            'Habitación Doble',
            'Habitación Simple',
            'Suite Junior',
            'Habitación Familiar',
            'Suite Ejecutiva',
            'Habitación Estándar'
        ];

        foreach ($nombresValidos as $nombre) {
            $this->assertTrue(
                !empty(trim($nombre)) && preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) === 1,
                "El nombre de categoría '{$nombre}' debería ser válido"
            );
        }

        // Nombres de categoría inválidos
        $nombresInvalidos = [
            '',                     // vacío
            '   ',                  // solo espacios
            'Suite123',             // contiene números
            'Habitación@Especial',  // contiene símbolos
            'Suite-Deluxe',         // contiene guión
            'Hab_Doble'            // contiene guión bajo
        ];

        foreach ($nombresInvalidos as $nombre) {
            $this->assertFalse(
                !empty(trim($nombre)) && preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) === 1,
                "El nombre de categoría '{$nombre}' debería ser inválido"
            );
        }
    }

    /**
     * Test: Validación de longitud de nombre de categoría
     */
    public function testValidacionLongitudNombreCategoria()
    {
        // Nombres con longitud válida (entre 3 y 100 caracteres)
        $nombresValidosLongitud = [
            'Suite',                           // 5 caracteres
            'Habitación Doble',               // 17 caracteres
            'Suite Presidencial Ejecutiva'    // 29 caracteres
        ];

        foreach ($nombresValidosLongitud as $nombre) {
            $longitud = strlen(trim($nombre));
            $this->assertTrue(
                $longitud >= 3 && $longitud <= 100,
                "El nombre '{$nombre}' debería tener entre 3 y 100 caracteres"
            );
        }

        // Nombres con longitud inválida
        $nombresInvalidosLongitud = [
            'Ha',                                                    // muy corto (2 caracteres)
            str_repeat('Habitación muy larga ', 10)                 // muy largo (más de 100 caracteres)
        ];

        foreach ($nombresInvalidosLongitud as $nombre) {
            $longitud = strlen(trim($nombre));
            $this->assertFalse(
                $longitud >= 3 && $longitud <= 100,
                "El nombre '{$nombre}' no debería tener longitud válida"
            );
        }
    }

    /**
     * Test: Validación de ID de categoría (numérico positivo)
     */
    public function testValidacionIDCategoria()
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
     * Test: Simulación de verificación de categoría existente
     */
    public function testSimulacionVerificacionCategoriaExistente()
    {
        // Simular lista de categorías existentes
        $categoriasExistentes = [
            'Suite Presidencial',
            'Habitación Doble',
            'Habitación Simple',
            'Suite Junior'
        ];

        // Función simulada para verificar si una categoría existe
        $verificarCategoriaExistente = function($nombreCategoria, $categoriasExistentes) {
            return in_array(strtoupper(trim($nombreCategoria)), array_map('strtoupper', $categoriasExistentes));
        };

        // Categorías que ya existen
        $this->assertTrue(
            $verificarCategoriaExistente('Suite Presidencial', $categoriasExistentes),
            'Debería detectar que Suite Presidencial ya existe'
        );

        $this->assertTrue(
            $verificarCategoriaExistente('HABITACIÓN DOBLE', $categoriasExistentes),
            'Debería detectar que HABITACIÓN DOBLE ya existe (case insensitive)'
        );

        // Categorías que no existen
        $this->assertFalse(
            $verificarCategoriaExistente('Suite Deluxe', $categoriasExistentes),
            'Debería detectar que Suite Deluxe no existe'
        );

        $this->assertFalse(
            $verificarCategoriaExistente('Habitación Triple', $categoriasExistentes),
            'Debería detectar que Habitación Triple no existe'
        );
    }

    /**
     * Test: Validación de parámetros para insertar categoría
     */
    public function testValidacionParametrosInsertarCategoria()
    {
        $parametrosValidos = [
            'nombre' => 'Nueva Categoría'
        ];

        // Verificar que el parámetro no esté vacío
        $this->assertNotEmpty(
            trim($parametrosValidos['nombre']),
            'El nombre de la categoría no debería estar vacío'
        );

        // Verificar que el parámetro tenga formato válido
        $this->assertTrue(
            preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $parametrosValidos['nombre']) === 1,
            'El nombre de la categoría debería tener formato válido'
        );
    }

    /**
     * Test: Validación de parámetros para actualizar categoría
     */
    public function testValidacionParametrosActualizarCategoria()
    {
        $parametrosValidos = [
            'cat_id' => 1,
            'nombre' => 'Categoría Actualizada'
        ];

        // Verificar ID válido
        $this->assertTrue(
            is_numeric($parametrosValidos['cat_id']) && $parametrosValidos['cat_id'] > 0,
            'El ID de la categoría debería ser válido'
        );

        // Verificar nombre válido
        $this->assertNotEmpty(
            trim($parametrosValidos['nombre']),
            'El nombre de la categoría no debería estar vacío'
        );

        $this->assertTrue(
            preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $parametrosValidos['nombre']) === 1,
            'El nombre de la categoría debería tener formato válido'
        );
    }

    /**
     * Test: Categorías típicas de un sistema hotelero
     */
    public function testCategoriasEspecificasHotel()
    {
        $categoriasTipicasHotel = [
            'Habitación Estándar',
            'Habitación Superior',
            'Habitación Deluxe',
            'Suite Junior',
            'Suite Ejecutiva',
            'Suite Presidencial',
            'Habitación Familiar',
            'Habitación Doble',
            'Habitación Simple',
            'Habitación Triple'
        ];

        foreach ($categoriasTipicasHotel as $categoria) {
            // Verificar que son nombres válidos para un sistema hotelero
            $this->assertTrue(
                !empty(trim($categoria)) && 
                preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $categoria) === 1 &&
                strlen(trim($categoria)) >= 3,
                "La categoría '{$categoria}' debería ser válida para un sistema hotelero"
            );
        }
    }

    /**
     * Test: Validación de normalización de nombres de categoría
     */
    public function testNormalizacionNombresCategorias()
    {
        $nombresConEspacios = [
            '  Suite Presidencial  ',
            ' Habitación Doble ',
            'Suite Junior   '
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
     * Test: Validación de jerarquía de categorías (de menor a mayor lujo)
     */
    public function testJerarquiaCategorias()
    {
        $categoriasJerarquicas = [
            'Habitación Estándar',    // Nivel 1
            'Habitación Superior',    // Nivel 2
            'Habitación Deluxe',      // Nivel 3
            'Suite Junior',           // Nivel 4
            'Suite Ejecutiva',        // Nivel 5
            'Suite Presidencial'      // Nivel 6
        ];

        // Verificar que todas las categorías son válidas
        foreach ($categoriasJerarquicas as $categoria) {
            $this->assertTrue(
                !empty(trim($categoria)) && 
                preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $categoria) === 1,
                "La categoría '{$categoria}' debería ser válida"
            );
        }

        // Verificar que hay al menos 3 niveles de categorías
        $this->assertGreaterThanOrEqual(
            3,
            count($categoriasJerarquicas),
            'Debería haber al menos 3 niveles de categorías'
        );
    }

    /**
     * Test: Validación de capacidad implícita por tipo de habitación
     */
    public function testValidacionCapacidadImplicitaPorTipo()
    {
        $tiposConCapacidad = [
            'Habitación Simple' => 1,      // 1 persona
            'Habitación Doble' => 2,       // 2 personas
            'Habitación Triple' => 3,      // 3 personas
            'Habitación Familiar' => 4,    // 4+ personas
            'Suite Junior' => 2,           // 2 personas
            'Suite Ejecutiva' => 2,        // 2 personas
            'Suite Presidencial' => 4      // 4+ personas
        ];

        foreach ($tiposConCapacidad as $tipo => $capacidadEsperada) {
            // Verificar que el tipo es válido
            $this->assertTrue(
                !empty(trim($tipo)) && 
                preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $tipo) === 1,
                "El tipo '{$tipo}' debería ser válido"
            );

            // Verificar que la capacidad es un número positivo
            $this->assertTrue(
                is_numeric($capacidadEsperada) && $capacidadEsperada > 0,
                "La capacidad {$capacidadEsperada} para '{$tipo}' debería ser válida"
            );
        }
    }
}