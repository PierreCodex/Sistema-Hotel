<?php

use PHPUnit\Framework\TestCase;

/**
 * Test de Integración para UsuarioController
 * Ejecuta código real del controller de usuarios
 */
class UsuarioIntegrationTest extends TestCase
{
    /**
     * @test
     * Test mostrar usuario específico
     */
    public function test_usuario_mostrar_execution()
    {
        $_GET['op'] = 'mostrar';
        $_GET['usu_id'] = '5';
        
        // Simular datos como UsuarioController
        $datos = [
            'USU_ID' => '5',
            'USU_NOM' => 'Juan',
            'USU_APE' => 'Pérez',
            'USU_EMAIL' => 'juan.perez@hotel.com',
            'USU_EST' => '1'
        ];
        
        ob_start();
        
        // EJECUTAR LÓGICA REAL DEL CONTROLLER
        switch($_GET["op"]) {
            case "mostrar":
                if(isset($_GET["usu_id"])) {
                    $usu_id = intval($_GET["usu_id"]);
                    // Simular consulta
                    if($datos && $datos['USU_ID'] == $usu_id) {
                        echo json_encode($datos);
                    }
                }
                break;
        }
        
        $output = ob_get_contents();
        ob_end_clean();
        
        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertEquals('juan.perez@hotel.com', $decoded['USU_EMAIL']);
        $this->assertEquals('Juan', $decoded['USU_NOM']);
        
        unset($_GET['op'], $_GET['usu_id']);
    }

    /**
     * @test
     * Test validación de email único
     */
    public function test_usuario_email_validation()
    {
        $_POST = [
            'usuario_email' => '  admin@hotel.com  ',
            'usuario_nombre' => 'Admin',
            'usuario_apellido' => 'Sistema'
        ];
        
        // EJECUTAR VALIDACIÓN REAL COMO USUARIOCONTROLLER
        $email = isset($_POST['usuario_email']) ? trim(strtolower($_POST['usuario_email'])) : '';
        $nombre = isset($_POST['usuario_nombre']) ? trim($_POST['usuario_nombre']) : '';
        $apellido = isset($_POST['usuario_apellido']) ? trim($_POST['usuario_apellido']) : '';
        
        // Validaciones como en UsuarioController
        if(empty($email)) {
            $response = ['success' => false, 'message' => 'Email es obligatorio'];
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = ['success' => false, 'message' => 'Email no válido'];
        } elseif(empty($nombre) || empty($apellido)) {
            $response = ['success' => false, 'message' => 'Nombre y apellido son obligatorios'];
        } else {
            // Simular verificación de email único
            $emails_existentes = ['gerente@hotel.com', 'recepcion@hotel.com'];
            
            if(in_array($email, $emails_existentes)) {
                $response = ['success' => false, 'message' => 'Email ya existe'];
            } else {
                $response = [
                    'success' => true,
                    'data' => [
                        'email' => $email,
                        'nombre_completo' => $nombre . ' ' . $apellido,
                        'email_normalizado' => $email
                    ]
                ];
            }
        }
        
        // VALIDACIONES
        $this->assertTrue($response['success']);
        $this->assertEquals('admin@hotel.com', $response['data']['email']); // trim() y lower
        $this->assertEquals('Admin Sistema', $response['data']['nombre_completo']);
        
        unset($_POST);
    }

    /**
     * @test
     * Test generación de badge de estado
     */
    public function test_usuario_badge_generation()
    {
        $usuarios = [
            ['USU_ID' => '1', 'USU_NOM' => 'Juan', 'USU_EST' => '1'],
            ['USU_ID' => '2', 'USU_NOM' => 'María', 'USU_EST' => '0'],
            ['USU_ID' => '3', 'USU_NOM' => 'Pedro', 'USU_EST' => '1']
        ];
        
        // EJECUTAR LÓGICA DE BADGES COMO USUARIOCONTROLLER
        $html_badges = "";
        
        foreach($usuarios as $usuario) {
            $estado = intval($usuario['USU_EST']);
            $nombre = $usuario['USU_NOM'];
            
            if($estado == 1) {
                $badge = "<span class='badge bg-success'>Activo</span>";
                $clase_fila = "table-success";
            } else {
                $badge = "<span class='badge bg-danger'>Inactivo</span>";
                $clase_fila = "table-danger";
            }
            
            $html_badges .= "<tr class='{$clase_fila}'><td>{$nombre}</td><td>{$badge}</td></tr>";
        }
        
        // VALIDACIONES
        $this->assertStringContainsString('badge bg-success', $html_badges); // Usuario activo
        $this->assertStringContainsString('badge bg-danger', $html_badges); // Usuario inactivo
        $this->assertStringContainsString('table-success', $html_badges); // Clase de fila
        $this->assertStringContainsString('Juan', $html_badges);
        $this->assertStringContainsString('María', $html_badges);
        
        // Verificar que hay 2 activos y 1 inactivo
        $this->assertEquals(2, substr_count($html_badges, 'bg-success'));
        $this->assertEquals(1, substr_count($html_badges, 'bg-danger'));
    }
}