<?php
// Script de depuración para probar findUserByCredentials

// Incluir archivos necesarios
require_once 'config/conexion.php';
require_once 'models/Usuario.php';

echo "=== DEBUG: Prueba de findUserByCredentials ===\n";

try {
    // Crear instancia del modelo Usuario
    $usuarioModel = new Usuario();
    
    // Probar conexión
    echo "1. Probando conexión a la base de datos...\n";
    $conectar = new Conectar();
    $pdo = $conectar->conexion();
    echo "   ✓ Conexión exitosa\n";
    
    // Verificar si existe el usuario de prueba
    echo "\n2. Verificando si existe el usuario de prueba...\n";
    $sql = "SELECT * FROM usuario WHERE Correo = 'test.integration@hotel.com'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "   ✓ Usuario encontrado: " . print_r($existingUser, true) . "\n";
    } else {
        echo "   ⚠ Usuario no encontrado. Creando usuario de prueba...\n";
        
        // Crear usuario de prueba
        $sql = "INSERT INTO usuario (Nombre, Apellido, DNI, Correo, Pass, Estado, IdRol) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'Test',
            'Integration', 
            '12345678',
            'test.integration@hotel.com',
            'password123',
            1, // Activo
            1  // Rol por defecto
        ]);
        
        if ($result) {
            echo "   ✓ Usuario de prueba creado exitosamente\n";
        } else {
            echo "   ✗ Error al crear usuario de prueba\n";
            print_r($stmt->errorInfo());
        }
    }
    
    // Probar findUserByCredentials
    echo "\n3. Probando findUserByCredentials...\n";
    $result = $usuarioModel->findUserByCredentials('test.integration@hotel.com', 'password123');
    
    echo "   Resultado: ";
    if ($result === false) {
        echo "FALSE (❌ PROBLEMA AQUÍ)\n";
    } else {
        echo "SUCCESS (✓)\n";
        echo "   Datos: " . print_r($result, true) . "\n";
    }
    
    // Verificar manualmente la consulta
    echo "\n4. Verificación manual de la consulta SQL...\n";
    $sql = "SELECT * FROM usuario WHERE Correo=? AND Pass=? AND Estado=1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, 'test.integration@hotel.com');
    $stmt->bindValue(2, 'password123');
    $stmt->execute();
    $manualResult = $stmt->fetch();
    
    echo "   Resultado manual: ";
    if ($manualResult === false) {
        echo "FALSE\n";
        
        // Verificar cada condición por separado
        echo "\n5. Verificando condiciones por separado...\n";
        
        // Solo por correo
        $sql = "SELECT * FROM usuario WHERE Correo=?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, 'test.integration@hotel.com');
        $stmt->execute();
        $emailResult = $stmt->fetch();
        echo "   Por correo: " . ($emailResult ? "ENCONTRADO" : "NO ENCONTRADO") . "\n";
        
        if ($emailResult) {
            echo "   Datos del usuario: " . print_r($emailResult, true) . "\n";
            echo "   Password en BD: '" . $emailResult['Pass'] . "'\n";
            echo "   Password buscado: 'password123'\n";
            echo "   ¿Coinciden?: " . ($emailResult['Pass'] === 'password123' ? "SÍ" : "NO") . "\n";
            echo "   Estado: " . $emailResult['Estado'] . "\n";
        }
    } else {
        echo "SUCCESS\n";
        echo "   Datos: " . print_r($manualResult, true) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DEBUG ===\n";
?>