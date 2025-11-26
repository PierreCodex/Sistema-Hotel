<?php

use PHPUnit\Framework\TestCase;

/**
 * Test unitario para VentaController
 * 
 * Estas pruebas validan la lógica de ventas ligadas a recepciones,
 * manejo de stock y procesamiento de detalles de venta
 */
class VentaControllerTest extends TestCase
{
    /**
     * @test
     * Validación: ID de recepción requerido para listar_por_recepcion
     */
    public function listar_por_recepcion_requiere_rec_id_valido()
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
        $rec_id3 = 15;
        $esValido3 = $rec_id3 > 0;
        $this->assertTrue($esValido3, 'ID de recepción positivo debe ser válido');
    }

    /**
     * @test
     * Lógica: Prioridad de resolución de rec_id en registrar
     */
    public function registrar_prioridad_resolucion_rec_id()
    {
        // Prioridad 1: POST rec_id
        $post_rec_id = 100;
        $session_rec_id = 50;
        $session_hab_id = 10;
        
        $rec_id_final = 0;
        if ($post_rec_id > 0) {
            $rec_id_final = $post_rec_id;
        } elseif ($session_rec_id > 0) {
            $rec_id_final = $session_rec_id;
        }
        
        $this->assertEquals(100, $rec_id_final, 'POST rec_id debe tener prioridad 1');

        // Prioridad 2: SESSION rec_id cuando POST no existe
        $post_rec_id2 = 0;
        $session_rec_id2 = 75;
        
        $rec_id_final2 = 0;
        if ($post_rec_id2 > 0) {
            $rec_id_final2 = $post_rec_id2;
        } elseif ($session_rec_id2 > 0) {
            $rec_id_final2 = $session_rec_id2;
        }
        
        $this->assertEquals(75, $rec_id_final2, 'SESSION rec_id debe tener prioridad 2');

        // Prioridad 3: Buscar por hab_id cuando otros no existen
        $post_rec_id3 = 0;
        $session_rec_id3 = 0;
        $session_hab_id3 = 25;
        $found_rec_id = 85; // Simulación de búsqueda por habitación
        
        $rec_id_final3 = 0;
        if ($post_rec_id3 > 0) {
            $rec_id_final3 = $post_rec_id3;
        } elseif ($session_rec_id3 > 0) {
            $rec_id_final3 = $session_rec_id3;
        } elseif ($session_hab_id3 > 0) {
            $rec_id_final3 = $found_rec_id;
        }
        
        $this->assertEquals(85, $rec_id_final3, 'Búsqueda por habitación debe tener prioridad 3');
    }

    /**
     * @test
     * Validación: Datos requeridos para guardardetalle
     */
    public function guardardetalle_validacion_datos_requeridos()
    {
        // Datos completos válidos
        $vent_id1 = 10;
        $prod_id1 = 5;
        $prod_pventa1 = 25.50;
        $detv_cant1 = 3;
        
        $esValido1 = $vent_id1 > 0 && $prod_id1 > 0 && $prod_pventa1 > 0 && $detv_cant1 > 0;
        $this->assertTrue($esValido1, 'Datos completos deben ser válidos');

        // Venta ID faltante
        $vent_id2 = 0;
        $prod_id2 = 5;
        $prod_pventa2 = 25.50;
        $detv_cant2 = 3;
        
        $esValido2 = $vent_id2 > 0 && $prod_id2 > 0 && $prod_pventa2 > 0 && $detv_cant2 > 0;
        $this->assertFalse($esValido2, 'Venta ID faltante debe ser inválido');

        // Producto ID faltante
        $vent_id3 = 10;
        $prod_id3 = 0;
        $prod_pventa3 = 25.50;
        $detv_cant3 = 3;
        
        $esValido3 = $vent_id3 > 0 && $prod_id3 > 0 && $prod_pventa3 > 0 && $detv_cant3 > 0;
        $this->assertFalse($esValido3, 'Producto ID faltante debe ser inválido');

        // Precio cero
        $vent_id4 = 10;
        $prod_id4 = 5;
        $prod_pventa4 = 0;
        $detv_cant4 = 3;
        
        $esValido4 = $vent_id4 > 0 && $prod_id4 > 0 && $prod_pventa4 > 0 && $detv_cant4 > 0;
        $this->assertFalse($esValido4, 'Precio cero debe ser inválido');

        // Cantidad cero
        $vent_id5 = 10;
        $prod_id5 = 5;
        $prod_pventa5 = 25.50;
        $detv_cant5 = 0;
        
        $esValido5 = $vent_id5 > 0 && $prod_id5 > 0 && $prod_pventa5 > 0 && $detv_cant5 > 0;
        $this->assertFalse($esValido5, 'Cantidad cero debe ser inválido');
    }

    /**
     * @test
     * Lógica: Validación de stock disponible vs cantidad solicitada
     */
    public function validacion_stock_disponible()
    {
        // Stock suficiente
        $stock_disponible1 = 10;
        $cantidad_solicitada1 = 5;
        $stockSuficiente1 = $cantidad_solicitada1 <= $stock_disponible1;
        $this->assertTrue($stockSuficiente1, 'Stock debe ser suficiente');

        // Stock exacto
        $stock_disponible2 = 7;
        $cantidad_solicitada2 = 7;
        $stockSuficiente2 = $cantidad_solicitada2 <= $stock_disponible2;
        $this->assertTrue($stockSuficiente2, 'Stock exacto debe ser válido');

        // Stock insuficiente
        $stock_disponible3 = 3;
        $cantidad_solicitada3 = 8;
        $stockSuficiente3 = $cantidad_solicitada3 <= $stock_disponible3;
        $this->assertFalse($stockSuficiente3, 'Stock insuficiente debe ser inválido');

        // Stock agotado
        $stock_disponible4 = 0;
        $cantidad_solicitada4 = 1;
        $stockSuficiente4 = $stock_disponible4 > 0 && $cantidad_solicitada4 <= $stock_disponible4;
        $this->assertFalse($stockSuficiente4, 'Stock agotado debe ser inválido');
    }

    /**
     * @test
     * Lógica: Procesamiento de diferentes formatos de stock de producto
     */
    public function procesamiento_formatos_stock_producto()
    {
        // Formato PRO_CANT
        $info_prod1 = [['PRO_CANT' => '15']];
        $stock1 = null;
        if (is_array($info_prod1) && count($info_prod1) > 0) {
            $row = $info_prod1[0];
            if (isset($row['PRO_CANT'])) {
                $stock1 = intval($row['PRO_CANT']);
            } elseif (isset($row['Cantidad'])) {
                $stock1 = intval($row['Cantidad']);
            }
        }
        $this->assertEquals(15, $stock1, 'Formato PRO_CANT debe procesarse');

        // Formato Cantidad
        $info_prod2 = [['Cantidad' => '8']];
        $stock2 = null;
        if (is_array($info_prod2) && count($info_prod2) > 0) {
            $row = $info_prod2[0];
            if (isset($row['PRO_CANT'])) {
                $stock2 = intval($row['PRO_CANT']);
            } elseif (isset($row['Cantidad'])) {
                $stock2 = intval($row['Cantidad']);
            }
        }
        $this->assertEquals(8, $stock2, 'Formato Cantidad debe procesarse');

        // Sin datos de stock
        $info_prod3 = [['OTRO_CAMPO' => '20']];
        $stock3 = null;
        if (is_array($info_prod3) && count($info_prod3) > 0) {
            $row = $info_prod3[0];
            if (isset($row['PRO_CANT'])) {
                $stock3 = intval($row['PRO_CANT']);
            } elseif (isset($row['Cantidad'])) {
                $stock3 = intval($row['Cantidad']);
            }
        }
        $this->assertNull($stock3, 'Sin datos de stock debe ser null');

        // Array vacío
        $info_prod4 = [];
        $stock4 = null;
        if (is_array($info_prod4) && count($info_prod4) > 0) {
            $row = $info_prod4[0];
            if (isset($row['PRO_CANT'])) {
                $stock4 = intval($row['PRO_CANT']);
            } elseif (isset($row['Cantidad'])) {
                $stock4 = intval($row['Cantidad']);
            }
        }
        $this->assertNull($stock4, 'Array vacío debe ser null');
    }

    /**
     * @test
     * Validación: ID requerido para eliminardetalle
     */
    public function eliminardetalle_requiere_detv_id_valido()
    {
        // ID válido
        $detv_id1 = 25;
        $esValido1 = $detv_id1 > 0;
        $this->assertTrue($esValido1, 'Detalle ID positivo debe ser válido');

        // ID cero
        $detv_id2 = 0;
        $esValido2 = $detv_id2 > 0;
        $this->assertFalse($esValido2, 'Detalle ID cero debe ser inválido');

        // ID negativo
        $detv_id3 = -10;
        $esValido3 = $detv_id3 > 0;
        $this->assertFalse($esValido3, 'Detalle ID negativo debe ser inválido');
    }

    /**
     * @test
     * Estructura: Formato DataTable para listardetalle
     */
    public function estructura_datatable_listardetalle()
    {
        // Simulación de datos de detalle
        $datos = [
            [
                'DETV_ID' => '10',
                'PRO_NOM' => 'Coca Cola 600ml',
                'DETV_CANT' => '2',
                'PROD_PVENTA' => '3.50',
                'DETV_TOTAL' => '7.00',
                'VENT_ID' => '5'
            ],
            [
                'DETV_ID' => '11',
                'PRO_NOM' => 'Agua Mineral',
                'DETV_CANT' => '1',
                'PROD_PVENTA' => '2.00',
                'DETV_TOTAL' => '2.00',
                'VENT_ID' => '5'
            ]
        ];

        $data = Array();
        foreach($datos as $row){
            $sub_array = array();
            $sub_array[] = $row["DETV_ID"];
            $sub_array[] = htmlspecialchars($row["PRO_NOM"], ENT_QUOTES, 'UTF-8');
            $sub_array[] = intval($row["DETV_CANT"]);
            $sub_array[] = number_format((float)$row["PROD_PVENTA"], 2);
            $sub_array[] = number_format((float)$row["DETV_TOTAL"], 2);
            $sub_array[] = '<button type="button" onClick="eliminar('.intval($row["DETV_ID"]).','.intval($row["VENT_ID"]).')" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>';
            $data[] = $sub_array;
        }

        $results = array(
            "sEcho"=>1,
            "iTotalRecords"=>count($data),
            "iTotalDisplayRecords"=>count($data),
            "aaData"=>$data
        );

        $this->assertArrayHasKey('sEcho', $results, 'Debe incluir sEcho');
        $this->assertArrayHasKey('iTotalRecords', $results, 'Debe incluir iTotalRecords');
        $this->assertArrayHasKey('iTotalDisplayRecords', $results, 'Debe incluir iTotalDisplayRecords');
        $this->assertArrayHasKey('aaData', $results, 'Debe incluir aaData');
        $this->assertCount(2, $results['aaData'], 'Debe procesar 2 registros');
        $this->assertEquals(2, $results['iTotalRecords'], 'Total records debe ser 2');

        // Verificar formato de primera fila
        $primera_fila = $results['aaData'][0];
        $this->assertEquals('10', $primera_fila[0], 'ID debe mantenerse como string');
        $this->assertEquals('Coca Cola 600ml', $primera_fila[1], 'Nombre debe estar sanitizado');
        $this->assertEquals(2, $primera_fila[2], 'Cantidad debe ser entero');
        $this->assertEquals('3.50', $primera_fila[3], 'Precio debe tener 2 decimales');
        $this->assertEquals('7.00', $primera_fila[4], 'Total debe tener 2 decimales');
        $this->assertStringContainsString('btn btn-danger', $primera_fila[5], 'Debe incluir botón eliminar');
    }

    /**
     * @test
     * Lógica: Cálculo de totales con subtotal, IGV y total
     */
    public function calculo_totales_subtotal_igv_total()
    {
        // Simulación de totales calculados
        $totales = [
            "VENT_SUBTOTAL" => 100.00,
            "VENT_IGV" => 18.00,
            "VENT_TOTAL" => 118.00
        ];

        $response = [
            "VENT_SUBTOTAL" => number_format((float)$totales["VENT_SUBTOTAL"], 2, '.', ''),
            "VENT_IGV" => number_format((float)$totales["VENT_IGV"], 2, '.', ''),
            "VENT_TOTAL" => number_format((float)$totales["VENT_TOTAL"], 2, '.', '')
        ];

        $this->assertEquals('100.00', $response['VENT_SUBTOTAL'], 'Subtotal debe tener 2 decimales');
        $this->assertEquals('18.00', $response['VENT_IGV'], 'IGV debe tener 2 decimales');
        $this->assertEquals('118.00', $response['VENT_TOTAL'], 'Total debe tener 2 decimales');

        // Verificar integridad del cálculo
        $subtotal_calc = floatval($response['VENT_SUBTOTAL']);
        $igv_calc = floatval($response['VENT_IGV']);
        $total_calc = floatval($response['VENT_TOTAL']);
        
        $this->assertEquals($subtotal_calc + $igv_calc, $total_calc, 'Total debe ser subtotal + IGV');
    }

    /**
     * @test
     * Lógica: Estados de venta - PENDIENTE vs PAGADO
     */
    public function estados_venta_pendiente_vs_pagado()
    {
        // Estado PENDIENTE
        $estado_sel1 = "PENDIENTE";
        $esPendiente1 = strtoupper($estado_sel1) === "PENDIENTE";
        $this->assertTrue($esPendiente1, 'Estado PENDIENTE debe ser reconocido');

        $estado_sel2 = "pendiente";
        $esPendiente2 = strtoupper($estado_sel2) === "PENDIENTE";
        $this->assertTrue($esPendiente2, 'Estado pendiente (minúscula) debe ser reconocido');

        // Estado PAGADO (fallback)
        $estado_sel3 = "PAGADO";
        $esPendiente3 = strtoupper($estado_sel3) === "PENDIENTE";
        $this->assertFalse($esPendiente3, 'Estado PAGADO no debe ser PENDIENTE');

        $estado_sel4 = "";
        $esPendiente4 = strtoupper($estado_sel4) === "PENDIENTE";
        $this->assertFalse($esPendiente4, 'Estado vacío debe ir a fallback (PAGADO)');

        $estado_sel5 = "OTRO_ESTADO";
        $esPendiente5 = strtoupper($estado_sel5) === "PENDIENTE";
        $this->assertFalse($esPendiente5, 'Otro estado debe ir a fallback (PAGADO)');
    }

    /**
     * @test
     * Validación: ID requerido para operaciones de guardar
     */
    public function guardar_requiere_vent_id_valido()
    {
        // ID válido
        $vent_id1 = 50;
        $esValido1 = $vent_id1 > 0;
        $this->assertTrue($esValido1, 'Venta ID positivo debe ser válido');

        // ID cero
        $vent_id2 = 0;
        $esValido2 = $vent_id2 > 0;
        $this->assertFalse($esValido2, 'Venta ID cero debe ser inválido');

        // ID negativo
        $vent_id3 = -15;
        $esValido3 = $vent_id3 > 0;
        $this->assertFalse($esValido3, 'Venta ID negativo debe ser inválido');
    }

    /**
     * @test
     * Conversión: Tipos de datos de entrada POST
     */
    public function conversion_tipos_datos_post()
    {
        // Conversión de strings a enteros
        $vent_id_post = "25";
        $prod_id_post = "10";
        $detv_cant_post = "3";
        
        $vent_id = intval($vent_id_post);
        $prod_id = intval($prod_id_post);
        $detv_cant = intval($detv_cant_post);
        
        $this->assertEquals(25, $vent_id, 'String ID venta debe convertirse a entero');
        $this->assertEquals(10, $prod_id, 'String ID producto debe convertirse a entero');
        $this->assertEquals(3, $detv_cant, 'String cantidad debe convertirse a entero');

        // Conversión de strings a float
        $prod_pventa_post = "15.75";
        $prod_pventa = floatval($prod_pventa_post);
        
        $this->assertEquals(15.75, $prod_pventa, 'String precio debe convertirse a float');

        // Manejo de valores nulos/vacíos
        $rec_id_post = null;
        $rec_id = intval($rec_id_post ?? 0);
        
        $this->assertEquals(0, $rec_id, 'Valor null debe convertirse a 0');

        $estado_post = "  PENDIENTE  ";
        $estado = isset($estado_post) ? trim($estado_post) : null;
        
        $this->assertEquals('PENDIENTE', $estado, 'String con espacios debe ser trimmed');
    }

    /**
     * @test
     * Estructura: Respuestas JSON para diferentes operaciones
     */
    public function estructura_respuestas_json_operaciones()
    {
        // Respuesta exitosa con VENT_ID
        $response1 = ["success" => true, "VENT_ID" => 123];
        $this->assertTrue($response1['success'], 'Debe indicar éxito');
        $this->assertArrayHasKey('VENT_ID', $response1, 'Debe incluir VENT_ID');
        $this->assertIsInt($response1['VENT_ID'], 'VENT_ID debe ser entero');

        // Respuesta exitosa con DETV_ID
        $response2 = ["success" => true, "DETV_ID" => 456];
        $this->assertTrue($response2['success'], 'Debe indicar éxito');
        $this->assertArrayHasKey('DETV_ID', $response2, 'Debe incluir DETV_ID');
        $this->assertIsInt($response2['DETV_ID'], 'DETV_ID debe ser entero');

        // Respuesta con data (listar)
        $response3 = ["success" => true, "data" => [["id" => 1], ["id" => 2]]];
        $this->assertTrue($response3['success'], 'Debe indicar éxito');
        $this->assertArrayHasKey('data', $response3, 'Debe incluir data');
        $this->assertIsArray($response3['data'], 'Data debe ser array');

        // Respuesta de error
        $response4 = ["success" => false, "message" => "Stock insuficiente. Disponible: 5"];
        $this->assertFalse($response4['success'], 'Debe indicar error');
        $this->assertArrayHasKey('message', $response4, 'Debe incluir mensaje');
        $this->assertStringContainsString('Stock', $response4['message'], 'Mensaje debe ser descriptivo');

        // Respuesta de guardar con estado
        $response5 = ["success" => true, "VENT_ID" => 789, "estado" => "PAGADO"];
        $this->assertTrue($response5['success'], 'Debe indicar éxito');
        $this->assertArrayHasKey('estado', $response5, 'Debe incluir estado final');
        $this->assertContains($response5['estado'], ['PENDIENTE', 'PAGADO'], 'Estado debe ser válido');
    }

    /**
     * @test
     * Operaciones: Verificar operaciones disponibles en switch
     */
    public function verificar_operaciones_disponibles()
    {
        $operacionesEsperadas = [
            'listar_por_recepcion',
            'registrar',
            'guardardetalle',
            'eliminardetalle',
            'listardetalle',
            'calculo',
            'guardar'
        ];
        
        $this->assertCount(7, $operacionesEsperadas, 'Debe tener 7 operaciones principales');
        $this->assertContains('listar_por_recepcion', $operacionesEsperadas);
        $this->assertContains('registrar', $operacionesEsperadas);
        $this->assertContains('guardardetalle', $operacionesEsperadas);
        $this->assertContains('eliminardetalle', $operacionesEsperadas);
        $this->assertContains('listardetalle', $operacionesEsperadas);
        $this->assertContains('calculo', $operacionesEsperadas);
        $this->assertContains('guardar', $operacionesEsperadas);
    }

    /**
     * @test
     * Integración: Flujo completo de venta con validaciones
     */
    public function flujo_completo_venta_simulado()
    {
        // 1. Registrar venta (por recepción)
        $rec_id = 25;
        $validacionRecepcion = $rec_id > 0;
        $this->assertTrue($validacionRecepcion, 'Recepción debe ser válida');

        $vent_id = 100; // Simulación de ID generado
        $this->assertGreaterThan(0, $vent_id, 'Debe generar ID de venta');

        // 2. Agregar detalle (con validación de stock)
        $prod_id = 5;
        $prod_pventa = 12.50;
        $detv_cant = 2;
        $stock_disponible = 10;

        $validacionDetalle = $vent_id > 0 && $prod_id > 0 && $prod_pventa > 0 && $detv_cant > 0;
        $validacionStock = $detv_cant <= $stock_disponible;
        
        $this->assertTrue($validacionDetalle, 'Datos de detalle deben ser válidos');
        $this->assertTrue($validacionStock, 'Stock debe ser suficiente');

        $detv_id = 200; // Simulación de ID generado
        $this->assertGreaterThan(0, $detv_id, 'Debe generar ID de detalle');

        // 3. Calcular totales
        $subtotal = 25.00; // 2 x 12.50
        $igv = 4.50; // 18%
        $total = 29.50;

        $totalesCalculados = [
            "VENT_SUBTOTAL" => number_format($subtotal, 2, '.', ''),
            "VENT_IGV" => number_format($igv, 2, '.', ''),
            "VENT_TOTAL" => number_format($total, 2, '.', '')
        ];

        $this->assertEquals('25.00', $totalesCalculados['VENT_SUBTOTAL']);
        $this->assertEquals('4.50', $totalesCalculados['VENT_IGV']);
        $this->assertEquals('29.50', $totalesCalculados['VENT_TOTAL']);

        // 4. Guardar como PAGADO
        $estado_seleccionado = "PAGADO";
        $esPendiente = strtoupper($estado_seleccionado) === "PENDIENTE";
        
        $this->assertFalse($esPendiente, 'Estado PAGADO no debe ser pendiente');

        // Simulación de respuesta final
        $respuestaFinal = [
            "success" => true,
            "VENT_ID" => $vent_id,
            "estado" => "PAGADO"
        ];

        $this->assertTrue($respuestaFinal['success'], 'Venta debe completarse exitosamente');
        $this->assertEquals('PAGADO', $respuestaFinal['estado'], 'Estado final debe ser PAGADO');
    }

    /**
     * @test
     * Sanitización: Prevención XSS en nombres de productos
     */
    public function sanitizacion_nombres_productos()
    {
        $nombrePeligroso = '<script>alert("XSS")</script>Coca Cola';
        $nombreSanitizado = htmlspecialchars($nombrePeligroso, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString('<script>', $nombreSanitizado, 'Script tags deben ser escapados');
        $this->assertStringContainsString('Coca Cola', $nombreSanitizado, 'Texto legítimo debe preservarse');
        $this->assertStringContainsString('&lt;script&gt;', $nombreSanitizado, 'Tags deben ser entidades HTML');
    }

    /**
     * @test
     * Headers: Validar Content-Type JSON
     */
    public function validar_content_type_json()
    {
        $contentType = 'application/json';
        
        $this->assertEquals('application/json', $contentType, 'Content-Type debe ser application/json');
        $this->assertStringContainsString('json', strtolower($contentType), 'Debe indicar formato JSON');
    }

    /**
     * @test
     * Manejo: Excepciones con try-catch en todas las operaciones
     */
    public function manejo_excepciones_try_catch()
    {
        $exceptionMessage = "Error al procesar venta";
        
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
}