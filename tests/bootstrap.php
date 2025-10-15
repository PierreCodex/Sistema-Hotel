<?php

// Autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Configurar el entorno de pruebas
define('TESTING', true);

// Iniciar sesión para las pruebas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios del proyecto
require_once __DIR__ . '/../config/conexion.php';

// Función helper para limpiar la base de datos de pruebas
function cleanTestDatabase() {
    // Aquí puedes agregar lógica para limpiar la BD de pruebas
    if (isset($_SESSION)) {
        session_unset();
        session_destroy();
    }
}

// Función helper para crear datos de prueba
function createTestUser() {
    return [
        'usuario' => 'test_user',
        'password' => 'test_password',
        'idTipoPersona' => 1
    ];
}

// Configurar variables de entorno para pruebas
$_ENV['APP_ENV'] = 'testing';