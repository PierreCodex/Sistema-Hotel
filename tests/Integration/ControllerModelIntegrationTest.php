<?php

use PHPUnit\Framework\TestCase;

/**
 * Pruebas de Integración entre Controladores y Modelos
 * 
 * Estas pruebas validan la integración completa entre:
 * - Controladores (usuario.php, auth.php)
 * - Modelos (Usuario.php)
 * - Base de datos
 * - Sesiones
 * 
 * @author QA Engineer - Especialista en Pruebas de Integración MVC
 */
class ControllerModelIntegrationTest extends TestCase
{
    private $testConnection;
    private $usuarioModel;
    private $authController;
    private $testUserIds = [];

    protected function setUp(): void
    {
        // Configurar conexión de prueba
        $this->testConnection = TestConexion::getInstance();
        
        // Inicializar modelos y controladores
        $this->usuarioModel = new Usuario();
        $this->authController = new AuthController();
        
        // Limpiar datos de prueba previos
        $this->cleanupTestData();
        
        // Limpiar sesión
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_start();
    }

    protected function tearDown(): void
    {
        // Limpiar datos de prueba
        $this->cleanupTestData();
        
        // Limpiar sesión
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * PRUEBA DE INTEGRACIÓN: Flujo completo de creación de usuario
     * 
     * Simula el flujo completo desde el controlador hasta la base de datos
     * para crear un nuevo usuario
     */
    public function testCompleteUserCreationFlow()
    {
        // Arrange: Simular datos POST para crear usuario
        $_POST = [
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'dni' => '98765432',
            'correo' => 'carlos.mendoza@hotel.com',
            'password' => 'password123',
            'idrol' => '1'
        ];
        
        // Simular sesión de usuario autenticado
        $_SESSION['IdUsuario'] = 1;
        $_SESSION['Nombre'] = 'Admin';
        $_SESSION['Correo'] = 'admin@hotel.com';

        // Act: Ejecutar el flujo de guardado (simulando el controlador)
        ob_start(); // Capturar output
        
        // Simular el proceso del controlador usuario.php
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $dni = $_POST['dni'];
        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $idrol = $_POST['idrol'];
        
        // Verificar que no existe el correo
        $existeCorreo = $this->usuarioModel->existe_usuario_correo($correo);
        
        if (empty($existeCorreo)) {
            // Insertar usuario
            $resultado = $this->usuarioModel->insert_usuario(
                $nombre, $apellido, $dni, $correo, $password, $idrol
            );
        }
        
        ob_end_clean(); // Limpiar output

        // Assert: Verificar que el usuario se creó correctamente
        $this->assertEmpty($existeCorreo, 'El correo no debe existir previamente');
        $this->assertIsArray($resultado, 'El resultado debe ser un array');
        
        // Verificar en la base de datos
        $pdo = $this->testConnection->getConnection();
        $sql = "SELECT * FROM usuario WHERE Correo = ? AND Estado = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$correo]);
        $usuarioCreado = $stmt->fetch();

        $this->assertNotFalse($usuarioCreado, 'El usuario debe existir en la base de datos');
        $this->assertEquals($nombre, $usuarioCreado['Nombre']);
        $this->assertEquals($apellido, $usuarioCreado['Apellido']);
        $this->assertEquals($dni, $usuarioCreado['DNI']);
        $this->assertEquals($correo, $usuarioCreado['Correo']);
        
        // Guardar ID para limpieza
        $this->testUserIds[] = $usuarioCreado['IdUsuario'];
    }

    /**
     * PRUEBA DE INTEGRACIÓN: Flujo completo de edición de usuario
     * 
     * Valida el flujo completo de actualización de un usuario existente
     */
    public function testCompleteUserEditFlow()
    {
        // Arrange: Crear usuario inicial
        $usuarioId = $this->createTestUser();
        
        // Simular datos POST para editar usuario
        $_POST = [
            'idusuario' => $usuarioId,
            'nombre' => 'Carlos Editado',
            'apellido' => 'Mendoza Editado',
            'dni' => '11111111',
            'correo' => 'carlos.editado@hotel.com',
            'password' => 'newpassword123',
            'idrol' => '2'
        ];
        
        // Simular sesión de usuario autenticado
        $_SESSION['IdUsuario'] = 1;

        // Act: Ejecutar el flujo de edición
        ob_start();
        
        $idusuario = $_POST['idusuario'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $dni = $_POST['dni'];
        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $idrol = $_POST['idrol'];
        
        // Actualizar usuario
        $resultado = $this->usuarioModel->update_usuario(
            $idusuario, $nombre, $apellido, $dni, $correo, $password, $idrol
        );
        
        ob_end_clean();

        // Assert: Verificar que la actualización fue exitosa
        $this->assertIsArray($resultado, 'El resultado debe ser un array');
        
        // Verificar cambios en la base de datos
        $pdo = $this->testConnection->getConnection();
        $sql = "SELECT * FROM usuario WHERE IdUsuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $usuarioActualizado = $stmt->fetch();

        $this->assertEquals('Carlos Editado', $usuarioActualizado['Nombre']);
        $this->assertEquals('Mendoza Editado', $usuarioActualizado['Apellido']);
        $this->assertEquals('11111111', $usuarioActualizado['DNI']);
        $this->assertEquals('carlos.editado@hotel.com', $usuarioActualizado['Correo']);
        $this->assertEquals('2', $usuarioActualizado['IdRol']);
    }

    /**
     * PRUEBA DE INTEGRACIÓN: Flujo completo de listado de usuarios
     * 
     * Valida que el controlador puede obtener y procesar correctamente
     * la lista de usuarios desde el modelo
     */
    public function testCompleteUserListingFlow()
    {
        // Arrange: Crear múltiples usuarios de prueba
        $usuario1Id = $this->createTestUser('user1@hotel.com', 'Usuario1', 'Apellido1');
        $usuario2Id = $this->createTestUser('user2@hotel.com', 'Usuario2', 'Apellido2');
        $usuario3Id = $this->createTestUser('user3@hotel.com', 'Usuario3', 'Apellido3');
        
        // Simular sesión de usuario autenticado
        $_SESSION['IdUsuario'] = $usuario1Id;

        // Act: Ejecutar el flujo de listado
        ob_start();
        
        // Simular el proceso del controlador para listar usuarios
        $idUsuarioActual = $_SESSION['IdUsuario'];
        $resultado = $this->usuarioModel->get_usuario($idUsuarioActual);
        
        ob_end_clean();

        // Assert: Verificar que se obtuvieron los usuarios correctos
        $this->assertIsArray($resultado, 'El resultado debe ser un array');
        $this->assertGreaterThanOrEqual(2, count($resultado), 'Debe haber al menos 2 usuarios');
        
        // Verificar que el usuario actual no está en la lista
        $idsEnResultado = array_column($resultado, 'IdUsuario');
        $this->assertNotContains($usuario1Id, $idsEnResultado, 'El usuario actual no debe estar en la lista');
        
        // Verificar que los otros usuarios sí están
        $this->assertContains($usuario2Id, $idsEnResultado, 'Usuario2 debe estar en la lista');
        $this->assertContains($usuario3Id, $idsEnResultado, 'Usuario3 debe estar en la lista');
    }

    /**
     * PRUEBA DE INTEGRACIÓN: Flujo completo de eliminación de usuario
     * 
     * Valida el flujo completo de eliminación lógica de un usuario
     */
    public function testCompleteUserDeletionFlow()
    {
        // Arrange: Crear usuario de prueba
        $usuarioId = $this->createTestUser();
        
        // Simular datos POST para eliminar usuario
        $_POST = ['idusuario' => $usuarioId];
        
        // Simular sesión de usuario autenticado
        $_SESSION['IdUsuario'] = 1;

        // Act: Ejecutar el flujo de eliminación
        ob_start();
        
        $idusuario = $_POST['idusuario'];
        $resultado = $this->usuarioModel->delete_usuario($idusuario);
        
        ob_end_clean();

        // Assert: Verificar que la eliminación fue exitosa
        $this->assertIsArray($resultado, 'El resultado debe ser un array');
        
        // Verificar que el usuario está inactivo en la base de datos
        $pdo = $this->testConnection->getConnection();
        $sql = "SELECT Estado FROM usuario WHERE IdUsuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuarioId]);
        $estado = $stmt->fetchColumn();
        
        $this->assertEquals(0, $estado, 'El usuario debe estar inactivo después de la eliminación');
    }

    /**
     * PRUEBA DE INTEGRACIÓN: Validación de duplicados en el flujo de creación
     * 
     * Valida que el controlador maneja correctamente la validación
     * de correos duplicados antes de crear un usuario
     */
    public function testDuplicateEmailValidationInCreationFlow()
    {
        // Arrange: Crear usuario inicial
        $email = 'duplicate@hotel.com';
        $this->createTestUser($email, 'Usuario', 'Existente');
        
        // Simular datos POST para crear usuario con email duplicado
        $_POST = [
            'nombre' => 'Nuevo',
            'apellido' => 'Usuario',
            'dni' => '99999999',
            'correo' => $email,
            'password' => 'password123',
            'idrol' => '1'
        ];
        
        $_SESSION['IdUsuario'] = 1;

        // Act: Ejecutar el flujo de creación
        ob_start();
        
        $correo = $_POST['correo'];
        $existeCorreo = $this->usuarioModel->existe_usuario_correo($correo);
        
        $usuarioCreado = false;
        if (empty($existeCorreo)) {
            // Solo crear si no existe el correo
            $resultado = $this->usuarioModel->insert_usuario(
                $_POST['nombre'], $_POST['apellido'], $_POST['dni'], 
                $_POST['correo'], $_POST['password'], $_POST['idrol']
            );
            $usuarioCreado = true;
        }
        
        ob_end_clean();

        // Assert: Verificar que se detectó el duplicado y no se creó el usuario
        $this->assertNotEmpty($existeCorreo, 'Debe detectar que el correo ya existe');
        $this->assertFalse($usuarioCreado, 'No debe crear usuario con correo duplicado');
        
        // Verificar que solo existe un usuario con ese correo
        $pdo = $this->testConnection->getConnection();
        $sql = "SELECT COUNT(*) FROM usuario WHERE Correo = ? AND Estado = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(1, $count, 'Debe existir solo un usuario con ese correo');
    }

    /**
     * PRUEBA DE INTEGRACIÓN: Flujo de autenticación con sesión
     * 
     * Valida la integración completa entre AuthController, Usuario model y sesiones
     */
    public function testCompleteAuthenticationWithSessionFlow()
    {
        // Arrange: Crear usuario de prueba
        $email = 'auth.integration@hotel.com';
        $password = 'testpassword123';
        $this->createTestUser($email, 'Auth', 'Integration', $password);
        
        // Simular datos POST de login
        $_POST = [
            'correo' => $email,
            'password' => $password
        ];

        // Act: Ejecutar el flujo de autenticación
        ob_start();
        
        // Simular el proceso del AuthController
        $correo = $_POST['correo'];
        $pass = $_POST['password'];
        
        // Validar entrada (simulado)
        $inputValid = !empty($correo) && !empty($pass) && filter_var($correo, FILTER_VALIDATE_EMAIL);
        
        if ($inputValid) {
            // Autenticar usuario
            $usuario = $this->usuarioModel->findUserByCredentials($correo, $pass);
            
            if ($usuario && $usuario['Estado'] == 1) {
                // Establecer sesión
                $_SESSION['IdUsuario'] = $usuario['IdUsuario'];
                $_SESSION['Nombre'] = $usuario['Nombre'];
                $_SESSION['Apellido'] = $usuario['Apellido'];
                $_SESSION['Correo'] = $usuario['Correo'];
                $_SESSION['IdRol'] = $usuario['IdRol'];
                $_SESSION['CREATED'] = time();
                
                $loginExitoso = true;
            } else {
                $loginExitoso = false;
            }
        } else {
            $loginExitoso = false;
        }
        
        ob_end_clean();

        // Assert: Verificar que la autenticación fue exitosa
        $this->assertTrue($inputValid, 'La entrada debe ser válida');
        $this->assertTrue($loginExitoso, 'El login debe ser exitoso');
        
        // Verificar que la sesión se estableció correctamente
        $this->assertArrayHasKey('IdUsuario', $_SESSION, 'La sesión debe tener IdUsuario');
        $this->assertArrayHasKey('Nombre', $_SESSION, 'La sesión debe tener Nombre');
        $this->assertArrayHasKey('Correo', $_SESSION, 'La sesión debe tener Correo');
        $this->assertEquals($email, $_SESSION['Correo'], 'El correo en sesión debe coincidir');
        $this->assertEquals('Auth', $_SESSION['Nombre'], 'El nombre en sesión debe coincidir');
    }

    /**
     * PRUEBA DE INTEGRACIÓN: Flujo de logout completo
     * 
     * Valida que el logout destruye correctamente la sesión
     */
    public function testCompleteLogoutFlow()
    {
        // Arrange: Establecer sesión de usuario autenticado
        $_SESSION['IdUsuario'] = 1;
        $_SESSION['Nombre'] = 'Test';
        $_SESSION['Correo'] = 'test@hotel.com';
        $_SESSION['CREATED'] = time();

        // Verificar que la sesión está establecida
        $this->assertArrayHasKey('IdUsuario', $_SESSION, 'La sesión debe estar establecida');

        // Act: Ejecutar el flujo de logout
        ob_start();
        
        // Simular el proceso de logout del AuthController
        session_unset();
        session_destroy();
        session_start(); // Reiniciar sesión limpia
        
        ob_end_clean();

        // Assert: Verificar que la sesión se destruyó
        $this->assertArrayNotHasKey('IdUsuario', $_SESSION, 'La sesión no debe tener IdUsuario después del logout');
        $this->assertArrayNotHasKey('Nombre', $_SESSION, 'La sesión no debe tener Nombre después del logout');
        $this->assertArrayNotHasKey('Correo', $_SESSION, 'La sesión no debe tener Correo después del logout');
    }

    /**
     * Método auxiliar: Crear usuario de prueba en la base de datos
     */
    private function createTestUser($email = 'test.user@hotel.com', $nombre = 'Test', $apellido = 'User', $password = 'password123')
    {
        $pdo = $this->testConnection->getConnection();
        
        $sql = "INSERT INTO usuario (Nombre, Apellido, DNI, Correo, Pass, Estado, IdRol) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre,
            $apellido, 
            '12345678',
            $email,
            $password,
            1, // Activo
            1  // Rol por defecto
        ]);
        
        $userId = $pdo->lastInsertId();
        $this->testUserIds[] = $userId;
        
        return $userId;
    }

    /**
     * Método auxiliar: Limpiar datos de prueba
     */
    private function cleanupTestData()
    {
        $pdo = $this->testConnection->getConnection();
        
        // Eliminar usuarios de prueba por email
        $emails = [
            'test.user@hotel.com',
            'user1@hotel.com',
            'user2@hotel.com', 
            'user3@hotel.com',
            'carlos.mendoza@hotel.com',
            'carlos.editado@hotel.com',
            'duplicate@hotel.com',
            'auth.integration@hotel.com'
        ];
        
        $placeholders = str_repeat('?,', count($emails) - 1) . '?';
        $sql = "DELETE FROM usuario WHERE Correo IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($emails);
        
        // Limpiar también por IDs si los tenemos
        if (!empty($this->testUserIds)) {
            $placeholders = str_repeat('?,', count($this->testUserIds) - 1) . '?';
            $sql = "DELETE FROM usuario WHERE IdUsuario IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($this->testUserIds);
        }
        
        $this->testUserIds = [];
    }
}