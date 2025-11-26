<?php

/**
 * Clase para manejar conexiones de base de datos en entorno de testing
 */
class TestConexion
{
    private $pdo;
    private static $instance = null;

    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        try {
            // Configuración para base de datos de test
            $host = 'localhost';
            $dbname = 'db-hotel-test';
            $username = 'root';
            $password = '';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            // Si no existe la BD de test, usar la de producción temporalmente
            try {
                $dsn = "mysql:host=$host;dbname=db-hotel;charset=utf8";
                $this->pdo = new PDO($dsn, $username, $password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                throw new Exception("No se pudo conectar a ninguna base de datos: " . $e2->getMessage());
            }
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function setNames()
    {
        return $this->pdo->query("SET NAMES 'utf8'");
    }

    /**
     * Ejecutar una consulta SQL
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Error en consulta SQL: " . $e->getMessage());
        }
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback()
    {
        return $this->pdo->rollback();
    }

    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Limpiar datos de test
     */
    public function cleanTestData()
    {
        try {
            // Limpiar tablas en orden para evitar problemas de FK
            $tables = ['usuario', 'categoria', 'rol'];
            
            foreach ($tables as $table) {
                $sql = "DELETE FROM $table WHERE 1=1";
                $this->pdo->exec($sql);
            }
            
        } catch (PDOException $e) {
            // Ignorar errores de limpieza
        }
    }

    /**
     * Crear datos básicos para pruebas
     */
    public function seedTestData()
    {
        try {
            // Crear roles básicos
            $roles = [
                ['Administrador', 1],
                ['Usuario', 1],
                ['Invitado', 1]
            ];

            foreach ($roles as $rol) {
                $sql = "INSERT INTO rol (RolNombre, Estado) VALUES (?, ?) ON DUPLICATE KEY UPDATE Estado = VALUES(Estado)";
                $this->query($sql, $rol);
            }

            // Crear categorías básicas
            $categorias = [
                ['Suite', 1],
                ['Estándar', 1],
                ['Deluxe', 1]
            ];

            foreach ($categorias as $categoria) {
                $sql = "INSERT INTO categoria (CategoriaNombre, Estado) VALUES (?, ?) ON DUPLICATE KEY UPDATE Estado = VALUES(Estado)";
                $this->query($sql, $categoria);
            }

        } catch (PDOException $e) {
            // Ignorar errores de seeding
        }
    }

    /**
     * Verificar si una tabla existe
     */
    public function tableExists($tableName)
    {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $stmt = $this->query($sql, [$tableName]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Crear estructura básica de tablas si no existen
     */
    public function createBasicStructure()
    {
        try {
            // Crear tabla rol si no existe
            if (!$this->tableExists('rol')) {
                $sql = "CREATE TABLE rol (
                    RolId INT AUTO_INCREMENT PRIMARY KEY,
                    RolNombre VARCHAR(50) NOT NULL,
                    Estado TINYINT DEFAULT 1,
                    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $this->pdo->exec($sql);
            }

            // Crear tabla categoria si no existe
            if (!$this->tableExists('categoria')) {
                $sql = "CREATE TABLE categoria (
                    CategoriaId INT AUTO_INCREMENT PRIMARY KEY,
                    CategoriaNombre VARCHAR(50) NOT NULL,
                    Estado TINYINT DEFAULT 1,
                    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $this->pdo->exec($sql);
            }

            // Crear tabla usuario si no existe
            if (!$this->tableExists('usuario')) {
                $sql = "CREATE TABLE usuario (
                    IdUsuario INT AUTO_INCREMENT PRIMARY KEY,
                    Nombre VARCHAR(50) NOT NULL,
                    Apellido VARCHAR(50) NOT NULL,
                    DNI VARCHAR(8) NOT NULL,
                    Correo VARCHAR(100) NOT NULL,
                    Pass VARCHAR(255) NOT NULL,
                    RolId INT,
                    Estado TINYINT DEFAULT 1,
                    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (RolId) REFERENCES rol(RolId)
                )";
                $this->pdo->exec($sql);
            }

        } catch (PDOException $e) {
            // Ignorar errores si las tablas ya existen
        }
    }
}