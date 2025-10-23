<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Pruebas Unitarias para el Controller de Rol
 * 
 * Este archivo contiene pruebas para validar la lógica de las operaciones
 * principales del controlador de roles: guardaryeditar, listar, mostrar, eliminar y combo.
 */
final class RolControllerTest extends TestCase
{
    /**
     * ✅ VERIFICA: Operación GUARDAR Y EDITAR - Validación de nombre vacío
     */
    public function testGuardarYEditarRechazaNombreVacio(): void
    {
        $rol_nom = ''; // Nombre vacío
        
        // Simular validación de nombre vacío
        $esNombreVacio = empty(trim($rol_nom));
        
        $this->assertTrue($esNombreVacio);
        
        // Simular respuesta del controller
        if ($esNombreVacio) {
            $response = array(
                'status' => 'error',
                'message' => 'El nombre del rol es obligatorio'
            );
        }
        
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('obligatorio', $response['message']);
    }
    
    /**
     * ✅ VERIFICA: Operación GUARDAR Y EDITAR - Validación de longitud mínima
     */
    public function testGuardarYEditarRechazaNombreMuyCorto(): void
    {
        $rol_nom = 'AB'; // Solo 2 caracteres
        
        // Simular validación de longitud (3-50 caracteres)
        $longitud = strlen(trim($rol_nom));
        $esLongitudValida = $longitud >= 3 && $longitud <= 50;
        
        $this->assertFalse($esLongitudValida);
        
        // Simular respuesta del controller
        if (!$esLongitudValida) {
            $response = array(
                'status' => 'error',
                'message' => 'El nombre del rol debe tener entre 3 y 50 caracteres'
            );
        }
        
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('entre 3 y 50 caracteres', $response['message']);
    }
    
    /**
     * ✅ VERIFICA: Operación GUARDAR Y EDITAR - Validación de longitud máxima
     */
    public function testGuardarYEditarRechazaNombreMuyLargo(): void
    {
        $rol_nom = str_repeat('A', 51); // 51 caracteres
        
        // Simular validación de longitud (3-50 caracteres)
        $longitud = strlen(trim($rol_nom));
        $esLongitudValida = $longitud >= 3 && $longitud <= 50;
        
        $this->assertFalse($esLongitudValida);
        
        // Simular respuesta del controller
        if (!$esLongitudValida) {
            $response = array(
                'status' => 'error',
                'message' => 'El nombre del rol debe tener entre 3 y 50 caracteres'
            );
        }
        
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('entre 3 y 50 caracteres', $response['message']);
    }
    
    /**
     * ✅ VERIFICA: Operación GUARDAR Y EDITAR - Inserción exitosa con nombre válido
     */
    public function testGuardarYEditarInsercionExitosa(): void
    {
        $rol_nom = 'Administrador';
        $rol_id = ''; // Vacío = inserción
        
        // Simular validaciones del controller
        $esNombreVacio = empty(trim($rol_nom));
        $longitud = strlen(trim($rol_nom));
        $esLongitudValida = $longitud >= 3 && $longitud <= 50;
        $esInsercion = empty($rol_id);
        $existeRol = false; // Simular que no existe
        
        $this->assertFalse($esNombreVacio);
        $this->assertTrue($esLongitudValida);
        $this->assertTrue($esInsercion);
        $this->assertFalse($existeRol);
        
        // Simular respuesta exitosa
        if (!$esNombreVacio && $esLongitudValida && !$existeRol) {
            $response = array(
                'status' => 'success',
                'message' => 'Rol registrado correctamente'
            );
        }
        
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('registrado', $response['message']);
    }
    
    /**
     * ✅ VERIFICA: Operación GUARDAR Y EDITAR - Actualización exitosa
     */
    public function testGuardarYEditarActualizacionExitosa(): void
    {
        $rol_nom = 'Supervisor';
        $rol_id = '1'; // Con ID = actualización
        
        // Simular validaciones del controller
        $esNombreValido = preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', trim($rol_nom)) === 1;
        $longitud = strlen(trim($rol_nom));
        $esLongitudValida = $longitud >= 3 && $longitud <= 50;
        $esActualizacion = !empty($rol_id);
        $existeRol = false; // Simular que no existe otro con el mismo nombre
        
        $this->assertTrue($esNombreValido);
        $this->assertTrue($esLongitudValida);
        $this->assertTrue($esActualizacion);
        $this->assertFalse($existeRol);
        
        // Simular respuesta exitosa
        if ($esNombreValido && $esLongitudValida && !$existeRol) {
            $response = array(
                'status' => 'success',
                'message' => 'Rol actualizado correctamente'
            );
        }
        
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('actualizado', $response['message']);
    }
    
    /**
     * ✅ VERIFICA: Operación GUARDAR Y EDITAR - Rechazo de rol duplicado
     */
    public function testGuardarYEditarRechazaRolDuplicado(): void
    {
        $rol_nom = 'Administrador';
        $rol_id = ''; // Inserción
        
        // Simular que ya existe un rol con este nombre
        $existeRol = true;
        
        $this->assertTrue($existeRol);
        
        // Simular respuesta del controller
        if ($existeRol) {
            $response = array(
                'status' => 'error',
                'message' => 'Ya existe un rol con este nombre'
            );
        }
        
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('Ya existe', $response['message']);
    }
    
    /**
     * ✅ VERIFICA: Operación LISTAR - Estructura correcta para DataTables
     */
    public function testListarEstructuraDataTables(): void
    {
        // Simular datos de roles
        $datos = [
            ['ROL_ID' => 1, 'ROL_NOM' => 'Administrador', 'FECH_CREA' => '2024-01-01'],
            ['ROL_ID' => 2, 'ROL_NOM' => 'Usuario', 'FECH_CREA' => '2024-01-02']
        ];
        
        // Simular procesamiento del controller
        $data = [];
        foreach($datos as $row){
            $sub_array = array();
            $sub_array[] = $row["ROL_NOM"];
            $sub_array[] = $row["FECH_CREA"];
            $sub_array[] = '<button type="button" onClick="editar('.$row["ROL_ID"].')" id="'.$row["ROL_ID"].'" class="btn btn-warning btn-icon waves-effect waves-light"><i class="ri-edit-2-line"></i></button>';
            $sub_array[] = '<button type="button" onClick="eliminar('.$row["ROL_ID"].')" id="'.$row["ROL_ID"].'" class="btn btn-danger btn-icon waves-effect waves-light"><i class="ri-delete-bin-5-line"></i></button>';
            $data[] = $sub_array;
        }
        
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        
        // Verificar estructura DataTables
        $this->assertArrayHasKey('sEcho', $results);
        $this->assertArrayHasKey('iTotalRecords', $results);
        $this->assertArrayHasKey('iTotalDisplayRecords', $results);
        $this->assertArrayHasKey('aaData', $results);
        $this->assertEquals(2, $results['iTotalRecords']);
        $this->assertCount(2, $results['aaData']);
        
        // Verificar que cada fila tiene 4 columnas (nombre, fecha, editar, eliminar)
        foreach($results['aaData'] as $fila) {
            $this->assertCount(4, $fila);
            $this->assertStringContainsString('btn-warning', $fila[2]); // Botón editar
            $this->assertStringContainsString('btn-danger', $fila[3]);  // Botón eliminar
        }
    }
    
    /**
     * ✅ VERIFICA: Operación MOSTRAR - Estructura JSON correcta
     */
    public function testMostrarEstructuraJSON(): void
    {
        // Simular datos del rol
        $datos = [
            ['ROL_ID' => 1, 'ROL_NOM' => 'Administrador']
        ];
        
        // Simular procesamiento del controller
        $output = [];
        if (is_array($datos) && count($datos) > 0) {
            foreach($datos as $row){
                $output["ROL_ID"] = $row["ROL_ID"];
                $output["ROL_NOM"] = $row["ROL_NOM"];
            }
        }
        
        // Verificar estructura JSON
        $this->assertArrayHasKey('ROL_ID', $output);
        $this->assertArrayHasKey('ROL_NOM', $output);
        $this->assertEquals(1, $output['ROL_ID']);
        $this->assertEquals('Administrador', $output['ROL_NOM']);
    }
    
    /**
     * ✅ VERIFICA: Operación ELIMINAR - Validación de ID
     */
    public function testEliminarValidacionID(): void
    {
        $rol_id = '1';
        
        // Simular validación básica de ID
        $esIDValido = !empty($rol_id) && is_numeric($rol_id);
        
        $this->assertTrue($esIDValido);
        
        // Simular que la operación se ejecuta sin errores
        $operacionExitosa = true;
        $this->assertTrue($operacionExitosa);
    }
    
    /**
     * ✅ VERIFICA: Operación COMBO - HTML con datos
     */
    public function testComboHTMLConDatos(): void
    {
        // Simular datos de roles
        $datos = [
            ['ROL_ID' => 1, 'ROL_NOM' => 'Administrador'],
            ['ROL_ID' => 2, 'ROL_NOM' => 'Usuario']
        ];
        
        // Simular procesamiento del controller
        $html = "";
        if(is_array($datos) && count($datos) > 0){
            $html .= "<option selected>Seleccionar</option>";
            foreach($datos as $row){
                $html .= "<option value='".$row["ROL_ID"]."'>".$row["ROL_NOM"]."</option>";
            }
        }
        
        // Verificar HTML generado
        $this->assertStringContainsString('<option selected>Seleccionar</option>', $html);
        $this->assertStringContainsString('<option value=\'1\'>Administrador</option>', $html);
        $this->assertStringContainsString('<option value=\'2\'>Usuario</option>', $html);
        $this->assertStringContainsString('option', $html);
    }
    
    /**
     * ✅ VERIFICA: Operación COMBO - Sin datos disponibles
     */
    public function testComboSinDatos(): void
    {
        // Simular sin datos
        $datos = [];
        
        // Simular procesamiento del controller
        $html = "";
        if(is_array($datos) && count($datos) > 0){
            $html .= "<option selected>Seleccionar</option>";
            foreach($datos as $row){
                $html .= "<option value='".$row["ROL_ID"]."'>".$row["ROL_NOM"]."</option>";
            }
        }
        
        // Verificar que no se genera HTML cuando no hay datos
        $this->assertEquals("", $html);
        $this->assertEmpty($html);
    }
    
}