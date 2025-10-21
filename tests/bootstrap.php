<?php
/**
 * Bootstrap file para las pruebas PHPUnit
 */

// Incluir el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Configurar el entorno de pruebas ANTES de cargar otros archivos
define('TESTING', true);

// Incluir archivos necesarios del proyecto
require_once __DIR__ . '/../config/conexion.php';

// Incluir la clase de conexión para pruebas
require_once __DIR__ . '/TestConexion.php';

// Incluir modelos necesarios para las pruebas
require_once __DIR__ . '/../models/Usuario.php';

// Configurar la zona horaria
date_default_timezone_set('America/Lima');

// Configurar el manejo de errores para pruebas
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear una versión simplificada del AuthController para pruebas
if (!class_exists('AuthController')) {
    class AuthController {
        private $usuarioModel;
        
        public function __construct() {
            $this->usuarioModel = new Usuario();
        }
        
        public function login() {
            // Método vacío para pruebas
        }
        
        private function validateLoginInput($correo, $password) {
            if (empty($correo) && empty($password)) {
                return ['valid' => false, 'error_code' => 2];
            }
            
            if (empty($correo)) {
                return ['valid' => false, 'error_code' => 3];
            }
            
            if (empty($password)) {
                return ['valid' => false, 'error_code' => 4];
            }
            
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'error_code' => 5];
            }
            
            return ['valid' => true];
        }
        
        private function authenticateUser($correo, $password) {
            return $this->usuarioModel->findUserByCredentials($correo, $password);
        }
        
        private function setUserSession($user) {
            // Método vacío para pruebas
        }
        
        private function redirectToHome() {
            // Método vacío para pruebas
        }
        
        private function redirectWithError($errorCode) {
            // Método vacío para pruebas
        }
        
        public function logout() {
            // Método vacío para pruebas
        }
        
        public function isAuthenticated() {
            return isset($_SESSION['usu_id']);
        }
        
        public function getCurrentUser() {
            return $_SESSION['usu_id'] ?? null;
        }
    }
}