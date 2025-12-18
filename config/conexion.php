<?php
    // Incluir el manejador de errores PRIMERO
    require_once(__DIR__ . "/error_handler.php");
    
    // Configurar zona horaria de Perú
    date_default_timezone_set('America/Lima');
    
    // Incluir el manejador de sesiones
    require_once("session.php");
    
    // Inicializar el sistema de sesiones
    SessionManager::init();

    class Conectar{
        protected $dbh;

        protected function Conexion(){
            try {
				$this->dbh = new PDO("mysql:local=localhost;dbname=db-hotel","root","");
				// Configurar zona horaria de Perú en MySQL
				$this->dbh->exec("SET time_zone = '-05:00'");
				return $this->dbh;	
			} catch (Exception $e) {
				// Registrar el error en el log
				logError("Error de conexión a la base de datos", [
					'message' => $e->getMessage(),
					'code' => $e->getCode()
				]);
				
				// En producción, mostrar mensaje genérico
				if (ENVIRONMENT === 'production') {
					die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
				} else {
					// En desarrollo, mostrar detalles
					die("¡Error BD!: " . $e->getMessage() . "<br/>");
				}
			}
        }

        public function set_names(){	
			return $this->dbh->query("SET NAMES 'utf8'");
        }
        
        public static function ruta(){
			return "http://localhost/SistemaHotel-PHP/";
		}

    }
?>