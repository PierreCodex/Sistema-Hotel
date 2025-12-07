<?php
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
				print "¡Error BD!: " . $e->getMessage() . "<br/>";
				die();	
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