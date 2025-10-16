<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class UtilidadesHotelTest extends TestCase
{
    /**
     * Test: Validación de formato de DNI peruano
     */
    public function testValidacionDNIPeruano()
    {
        // Función simulada para validar DNI peruano
        $validarDNI = function($dni) {
            return preg_match('/^\d{8}$/', $dni) === 1;
        };

        // DNIs válidos
        $dnisValidos = [
            '12345678',
            '87654321',
            '11111111',
            '99999999',
            '01234567'
        ];

        foreach ($dnisValidos as $dni) {
            $this->assertTrue(
                $validarDNI($dni),
                "El DNI {$dni} debería ser válido"
            );
        }

        // DNIs inválidos
        $dnisInvalidos = [
            '1234567',      // 7 dígitos
            '123456789',    // 9 dígitos
            '1234567a',     // contiene letra
            '',             // vacío
            '12-34-56-78',  // con guiones
            '12.345.678'    // con puntos
        ];

        foreach ($dnisInvalidos as $dni) {
            $this->assertFalse(
                $validarDNI($dni),
                "El DNI {$dni} debería ser inválido"
            );
        }
    }

    /**
     * Test: Validación de números de teléfono peruanos
     */
    public function testValidacionTelefonoPeruano()
    {
        // Función simulada para validar teléfonos peruanos
        $validarTelefono = function($telefono) {
            // Celular: 9 dígitos empezando con 9
            // Fijo Lima: 7 dígitos empezando con 01
            // Fijo provincias: 6-7 dígitos
            return preg_match('/^(9\d{8}|01\d{7}|\d{6,7})$/', $telefono) === 1;
        };

        // Teléfonos válidos
        $telefonosValidos = [
            '987654321',    // Celular
            '912345678',    // Celular
            '0112345678',   // Fijo Lima
            '0143256789',   // Fijo Lima
            '123456',       // Fijo provincia (6 dígitos)
            '1234567'       // Fijo provincia (7 dígitos)
        ];

        foreach ($telefonosValidos as $telefono) {
            $this->assertTrue(
                $validarTelefono($telefono),
                "El teléfono {$telefono} debería ser válido"
            );
        }

        // Teléfonos inválidos
        $telefonosInvalidos = [
            '12345',        // muy corto
            '123456789012', // muy largo
            '812345678',    // celular no empieza con 9
            '0212345678',   // fijo no válido
            'abc123456',    // contiene letras
            ''              // vacío
        ];

        foreach ($telefonosInvalidos as $telefono) {
            $this->assertFalse(
                $validarTelefono($telefono),
                "El teléfono {$telefono} debería ser inválido"
            );
        }
    }

    /**
     * Test: Cálculo de noches entre fechas
     */
    public function testCalculoNochesEntreFechas()
    {
        // Función para calcular noches
        $calcularNoches = function($fechaInicio, $fechaFin) {
            $inicio = new DateTime($fechaInicio);
            $fin = new DateTime($fechaFin);
            $diferencia = $inicio->diff($fin);
            return $diferencia->days;
        };

        // Casos de prueba
        $casosPrueba = [
            ['2024-01-01', '2024-01-02', 1],    // 1 noche
            ['2024-01-01', '2024-01-05', 4],    // 4 noches
            ['2024-01-15', '2024-01-20', 5],    // 5 noches
            ['2024-12-30', '2025-01-02', 3]     // Cambio de año
        ];

        foreach ($casosPrueba as [$inicio, $fin, $nochesEsperadas]) {
            $nochesCalculadas = $calcularNoches($inicio, $fin);
            $this->assertEquals(
                $nochesEsperadas,
                $nochesCalculadas,
                "Entre {$inicio} y {$fin} deberían ser {$nochesEsperadas} noches"
            );
        }
    }

    /**
     * Test: Validación de fechas de reserva
     */
    public function testValidacionFechasReserva()
    {
        // Función para validar fechas de reserva
        $validarFechasReserva = function($fechaInicio, $fechaFin) {
            $inicio = new DateTime($fechaInicio);
            $fin = new DateTime($fechaFin);
            $hoy = new DateTime();
            
            // La fecha de inicio debe ser hoy o posterior
            // La fecha de fin debe ser posterior a la de inicio
            return $inicio >= $hoy && $fin > $inicio;
        };

        $hoy = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));
        $pasadoManana = date('Y-m-d', strtotime('+2 days'));
        $ayer = date('Y-m-d', strtotime('-1 day'));

        // Fechas válidas
        $fechasValidas = [
            [$hoy, $manana],
            [$manana, $pasadoManana],
            [$hoy, $pasadoManana]
        ];

        foreach ($fechasValidas as [$inicio, $fin]) {
            $this->assertTrue(
                $validarFechasReserva($inicio, $fin),
                "Las fechas {$inicio} a {$fin} deberían ser válidas"
            );
        }

        // Fechas inválidas
        $fechasInvalidas = [
            [$ayer, $hoy],          // Inicio en el pasado
            [$manana, $hoy],        // Fin antes que inicio
            [$hoy, $hoy]            // Misma fecha (0 noches)
        ];

        foreach ($fechasInvalidas as [$inicio, $fin]) {
            $this->assertFalse(
                $validarFechasReserva($inicio, $fin),
                "Las fechas {$inicio} a {$fin} deberían ser inválidas"
            );
        }
    }

    /**
     * Test: Cálculo de precio con descuentos por estadía prolongada
     */
    public function testCalculoPrecioConDescuentos()
    {
        // Función para calcular precio con descuentos
        $calcularPrecioConDescuento = function($precioNoche, $noches) {
            $total = $precioNoche * $noches;
            
            // Descuentos por estadía prolongada
            if ($noches >= 7 && $noches < 14) {
                $total *= 0.9; // 10% descuento
            } elseif ($noches >= 14) {
                $total *= 0.85; // 15% descuento
            }
            
            return round($total, 2);
        };

        // Casos de prueba
        $this->assertEquals(100.0, $calcularPrecioConDescuento(100, 1));     // Sin descuento
        $this->assertEquals(500.0, $calcularPrecioConDescuento(100, 5));     // Sin descuento
        $this->assertEquals(630.0, $calcularPrecioConDescuento(100, 7));     // 10% descuento
        $this->assertEquals(1190.0, $calcularPrecioConDescuento(100, 14));   // 15% descuento
        $this->assertEquals(1700.0, $calcularPrecioConDescuento(100, 20));   // 15% descuento
    }

    /**
     * Test: Cálculo de IGV (18% en Perú)
     */
    public function testCalculoIGV()
    {
        // Función para calcular IGV
        $calcularIGV = function($subtotal) {
            return round($subtotal * 0.18, 2);
        };

        // Función para calcular total con IGV
        $calcularTotalConIGV = function($subtotal) {
            return round($subtotal * 1.18, 2);
        };

        // Casos de prueba
        $casosPrueba = [
            ['subtotal' => 100, 'igv' => 18, 'total' => 118],
            ['subtotal' => 250, 'igv' => 45, 'total' => 295],
            ['subtotal' => 500, 'igv' => 90, 'total' => 590],
            ['subtotal' => 1000, 'igv' => 180, 'total' => 1180]
        ];

        foreach ($casosPrueba as $caso) {
            $igvCalculado = $calcularIGV($caso['subtotal']);
            $totalCalculado = $calcularTotalConIGV($caso['subtotal']);

            $this->assertEquals(
                $caso['igv'],
                $igvCalculado,
                "IGV de {$caso['subtotal']} debería ser {$caso['igv']}"
            );

            $this->assertEquals(
                $caso['total'],
                $totalCalculado,
                "Total con IGV de {$caso['subtotal']} debería ser {$caso['total']}"
            );
        }
    }

    /**
     * Test: Formateo de moneda peruana (PEN)
     */
    public function testFormateoMonedaPeruana()
    {
        // Función para formatear moneda
        $formatearMoneda = function($cantidad) {
            return 'S/ ' . number_format($cantidad, 2, '.', ',');
        };

        // Casos de prueba
        $casosPrueba = [
            [100, 'S/ 100.00'],
            [1250.50, 'S/ 1,250.50'],
            [10000, 'S/ 10,000.00'],
            [0.99, 'S/ 0.99']
        ];

        foreach ($casosPrueba as [$cantidad, $formatoEsperado]) {
            $formatoCalculado = $formatearMoneda($cantidad);
            $this->assertEquals(
                $formatoEsperado,
                $formatoCalculado,
                "La cantidad {$cantidad} debería formatearse como {$formatoEsperado}"
            );
        }
    }

    /**
     * Test: Validación de códigos de habitación
     */
    public function testValidacionCodigosHabitacion()
    {
        // Función para validar códigos de habitación (formato: PISO + NUMERO)
        $validarCodigoHabitacion = function($codigo) {
            // Formato: 1-3 dígitos para piso + 2 dígitos para número
            return preg_match('/^[1-9]\d{0,2}[0-9]{2}$/', $codigo) === 1;
        };

        // Códigos válidos
        $codigosValidos = [
            '101',      // Piso 1, habitación 01
            '205',      // Piso 2, habitación 05
            '1015',     // Piso 10, habitación 15
            '2501'      // Piso 25, habitación 01
        ];

        foreach ($codigosValidos as $codigo) {
            $this->assertTrue(
                $validarCodigoHabitacion($codigo),
                "El código {$codigo} debería ser válido"
            );
        }

        // Códigos inválidos
        $codigosInvalidos = [
            '01',       // muy corto
            '0101',     // empieza con 0
            'A101',     // contiene letra
            '1001234',  // muy largo
            ''          // vacío
        ];

        foreach ($codigosInvalidos as $codigo) {
            $this->assertFalse(
                $validarCodigoHabitacion($codigo),
                "El código {$codigo} debería ser inválido"
            );
        }
    }

    /**
     * Test: Validación de capacidad de habitaciones
     */
    public function testValidacionCapacidadHabitaciones()
    {
        // Función para validar capacidad
        $validarCapacidad = function($capacidad) {
            return is_numeric($capacidad) && $capacidad >= 1 && $capacidad <= 10;
        };

        // Capacidades válidas
        $capacidadesValidas = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        foreach ($capacidadesValidas as $capacidad) {
            $this->assertTrue(
                $validarCapacidad($capacidad),
                "La capacidad {$capacidad} debería ser válida"
            );
        }

        // Capacidades inválidas
        $capacidadesInvalidas = [0, -1, 11, 'abc', '', null];

        foreach ($capacidadesInvalidas as $capacidad) {
            $this->assertFalse(
                $validarCapacidad($capacidad),
                "La capacidad {$capacidad} debería ser inválida"
            );
        }
    }
}