<?php
/**
 * Clase de conexión específica para pruebas
 * Extiende la funcionalidad de Conectar para usar una base de datos de prueba
 */

require_once __DIR__ . '/../config/session.php';

class TestConexion {
    protected $dbh;
    private static $instance = null;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct() {
        $this->conectar();
    }

    /**
     * Obtener instancia única de la conexión de prueba
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer conexión a la base de datos de prueba
     */
    protected function conectar() {
        try {
            // Usar la misma base de datos pero con un prefijo de tabla diferente
            // o una base de datos específica para pruebas
            $this->dbh = new PDO("mysql:host=localhost;dbname=db-hotel-test", "root", "");
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setNames();
        } catch (Exception $e) {
            // Si no existe la BD de prueba, usar la principal con transacciones
            try {
                $this->dbh = new PDO("mysql:host=localhost;dbname=db-hotel", "root", "");
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->setNames();
            } catch (Exception $e2) {
                throw new Exception("Error conectando a la base de datos de prueba: " . $e2->getMessage());
            }
        }
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection() {
        return $this->dbh;
    }

    /**
     * Configurar charset UTF-8
     */
    public function setNames() {
        return $this->dbh->query("SET NAMES 'utf8'");
    }

    /**
     * Iniciar transacción para pruebas
     */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /**
     * Hacer rollback de la transacción
     */
    public function rollback() {
        return $this->dbh->rollback();
    }

    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->dbh->commit();
    }

    /**
     * Limpiar datos de prueba de una tabla específica
     */
    public function limpiarTabla($tabla) {
        $sql = "DELETE FROM {$tabla} WHERE Descripcion LIKE 'TEST_%'";
        return $this->dbh->exec($sql);
    }

    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}