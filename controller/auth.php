<?php
require_once("config/conexion.php");
require_once("models/Usuario.php");

class AuthController {
    
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Procesa el login del usuario
     */
    public function login() {
        if (isset($_POST["enviar"])) {
            $correo = $_POST["Correo"] ?? '';
            $password = $_POST["Pass"] ?? '';
            
            // Validar entrada
            $validationResult = $this->validateLoginInput($correo, $password);
            if (!$validationResult['valid']) {
                $this->redirectWithError($validationResult['error_code']);
                return;
            }
            
            // Autenticar usuario
            $user = $this->authenticateUser($correo, $password);
            if ($user) {
                $this->setUserSession($user);
                $this->redirectToHome();
            } else {
                $this->redirectWithError(1); // Credenciales incorrectas
            }
        }
    }
    
    /**
     * Valida los datos de entrada del login
     * @param string $correo
     * @param string $password
     * @return array
     */
    private function validateLoginInput($correo, $password) {
        if (empty($correo) && empty($password)) {
            return ['valid' => false, 'error_code' => 2]; // Campos vacíos
        }
        
        if (empty($correo)) {
            return ['valid' => false, 'error_code' => 3]; // Email vacío
        }
        
        if (empty($password)) {
            return ['valid' => false, 'error_code' => 4]; // Password vacío
        }
        
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error_code' => 5]; // Email inválido
        }
        
        return ['valid' => true];
    }
    
    /**
     * Autentica al usuario con las credenciales proporcionadas
     * @param string $correo
     * @param string $password
     * @return array|false
     */
    private function authenticateUser($correo, $password) {
        $user = $this->usuarioModel->findUserByCredentials($correo, $password);
        
        if (is_array($user) && count($user) > 0) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Establece las variables de sesión del usuario
     * @param array $user
     */
    private function setUserSession($user) {
        $_SESSION["IdUsuario"] = $user["IdUsuario"];
        $_SESSION["Nombre"] = $user["Nombre"];
        $_SESSION["Apellido"] = $user["Apellido"];
        $_SESSION["IdRol"] = $user["IdRol"];
        $_SESSION["Correo"] = $user["Correo"];
    }
    
    /**
     * Redirige al usuario a la página de inicio
     */
    private function redirectToHome() {
        header("Location: " . Conectar::ruta() . "view/Home/");
        exit();
    }
    
    /**
     * Redirige con un código de error
     * @param int $errorCode
     */
    private function redirectWithError($errorCode) {
        header("Location: " . Conectar::ruta() . "index.php?m=" . $errorCode);
        exit();
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        session_destroy();
        header("Location: " . Conectar::ruta() . "index.php");
        exit();
    }
    
    /**
     * Verifica si el usuario está autenticado
     * @return bool
     */
    public function isAuthenticated() {
        return isset($_SESSION["IdUsuario"]) && !empty($_SESSION["IdUsuario"]);
    }
    
    /**
     * Obtiene los datos del usuario actual de la sesión
     * @return array|null
     */
    public function getCurrentUser() {
        if ($this->isAuthenticated()) {
            return [
                'IdUsuario' => $_SESSION["IdUsuario"],
                'Nombre' => $_SESSION["Nombre"],
                'Apellido' => $_SESSION["Apellido"],
                'IdRol' => $_SESSION["IdRol"],
                'Correo' => $_SESSION["Correo"]
            ];
        }
        return null;
    }
}

?>