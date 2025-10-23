<?php
/**
 * Bootstrap file para las pruebas PHPUnit
 */

// Incluir el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

<<<<<<< HEAD
// Configurar el entorno de pruebas ANTES de cargar otros archivos
define('TESTING', true);

// Incluir archivos necesarios del proyecto
require_once __DIR__ . '/../config/conexion.php';

// Incluir la clase de conexión para pruebas
require_once __DIR__ . '/TestConexion.php';

// Incluir modelos necesarios para las pruebas
require_once __DIR__ . '/../models/Usuario.php';
=======
// Incluir archivos necesarios del proyecto
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/TestConexion.php';

// Incluir modelos
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Categoria.php';

// Incluir controladores
require_once __DIR__ . '/../controller/auth.php';

// Configurar el entorno de pruebas
define('TESTING', true);
>>>>>>> desarrollo

// Configurar la zona horaria
date_default_timezone_set('America/Lima');

// Configurar el manejo de errores para pruebas
error_reporting(E_ALL);
ini_set('display_errors', 1);

<<<<<<< HEAD
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
=======
/**
 * Clase Categoria extendida para pruebas
 * Permite usar la conexión de prueba en lugar de la conexión normal
 */
class CategoriaTest extends Conectar {
    private $testConnection;

  

    protected function conexion() {
        return $this->testConnection->getConnection();
    }

    public function set_names() {
        return $this->testConnection->setNames();
    }

    // Métodos CRUD usando la conexión de prueba
    public function get_categoria() {
        $conectar = $this->conexion();
        $this->set_names();
        $sql = "SELECT * FROM categoria WHERE Estado = 1 ORDER BY Descripcion";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_categoria_x_cat_id($cat_id) {
        $conectar = $this->conexion();
        $this->set_names();
        $sql = "SELECT * FROM categoria WHERE IdCategoria = ? AND Estado = 1";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_id);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert_categoria($cat_nom) {
        $conectar = $this->conexion();
        $this->set_names();
        $sql = "INSERT INTO categoria (Descripcion, Estado, FechaCreacion) VALUES (?, 1, NOW())";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_nom);
        $sql->execute();
        return $this->testConnection->lastInsertId();
    }

    public function update_categoria($cat_id, $cat_nom) {
        $conectar = $this->conexion();
        $this->set_names();
        $sql = "UPDATE categoria SET Descripcion = ? WHERE IdCategoria = ? AND Estado = 1";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_nom);
        $sql->bindValue(2, $cat_id);
        return $sql->execute();
    }

    public function delete_categoria($cat_id) {
        $conectar = $this->conexion();
        $this->set_names();
        $sql = "UPDATE categoria SET Estado = 0 WHERE IdCategoria = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $cat_id);
        return $sql->execute();
    }

    public function verificar_categoria_existente($cat_nom, $cat_id = null) {
        $conectar = $this->conexion();
        $this->set_names();
        
        if($cat_id == null) {
            $sql = "SELECT COUNT(*) as total FROM categoria WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $cat_nom);
        } else {
            $sql = "SELECT COUNT(*) as total FROM categoria WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(?)) AND IdCategoria != ? AND Estado = 1";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $cat_nom);
            $sql->bindValue(2, $cat_id);
        }
        
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    // Métodos específicos para pruebas
    public function beginTransaction() {
        return $this->testConnection->beginTransaction();
    }

    public function rollback() {
        return $this->testConnection->rollback();
    }

    public function commit() {
        return $this->testConnection->commit();
    }

    public function limpiarDatosPrueba() {
        return $this->testConnection->limpiarTabla('categoria');
>>>>>>> desarrollo
    }
}