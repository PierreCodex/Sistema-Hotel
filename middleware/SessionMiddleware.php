<?php
/**
 * Middleware para verificación de sesiones
 * Maneja la verificación automática de expiración de sesiones
 */

require_once(__DIR__ . "/../config/conexion.php");
require_once(__DIR__ . "/../config/session.php");

class SessionMiddleware {
    
    /**
     * Verifica si el usuario está autenticado y su sesión es válida
     * @return array
     */
    public static function checkAuthentication() {
        // Verificar estado de la sesión
        $sessionStatus = SessionManager::checkSession();
        
        // Si la sesión expiró, limpiar datos de usuario
        if ($sessionStatus['status'] === 'expired') {
            self::clearUserSession();
            return [
                'authenticated' => false,
                'session_status' => $sessionStatus,
                'redirect_to_login' => true
            ];
        }
        
        // Verificar si hay datos de usuario en la sesión
        if (!isset($_SESSION["IdUsuario"]) || empty($_SESSION["IdUsuario"])) {
            return [
                'authenticated' => false,
                'session_status' => $sessionStatus,
                'redirect_to_login' => true
            ];
        }
        
        return [
            'authenticated' => true,
            'session_status' => $sessionStatus,
            'user_data' => [
                'IdUsuario' => $_SESSION["IdUsuario"],
                'Nombre' => $_SESSION["Nombre"],
                'Apellido' => $_SESSION["Apellido"],
                'IdRol' => $_SESSION["IdRol"],
                'Correo' => $_SESSION["Correo"]
            ]
        ];
    }
    
    /**
     * Middleware para proteger páginas que requieren autenticación
     * @param string $redirectUrl URL de redirección si no está autenticado
     */
    public static function requireAuth($redirectUrl = null) {
        $authCheck = self::checkAuthentication();
        
        if (!$authCheck['authenticated']) {
            if ($redirectUrl === null) {
                $redirectUrl = Conectar::ruta() . "index.php?m=6"; // Sesión expirada
            }
            
            header("Location: " . $redirectUrl);
            exit();
        }
        
        return $authCheck;
    }
    
    /**
     * Endpoint AJAX para verificar estado de sesión
     */
    public static function ajaxCheckSession() {
        header('Content-Type: application/json');
        
        $authCheck = self::checkAuthentication();
        
        echo json_encode([
            'authenticated' => $authCheck['authenticated'],
            'session_info' => SessionManager::getSessionInfo(),
            'status' => $authCheck['session_status']['status'],
            'message' => $authCheck['session_status']['message'],
            'time_left' => $authCheck['session_status']['time_left']
        ]);
        exit();
    }
    
    /**
     * Endpoint AJAX para renovar sesión
     */
    public static function ajaxRenewSession() {
        header('Content-Type: application/json');
        
        $authCheck = self::checkAuthentication();
        
        if ($authCheck['authenticated']) {
            SessionManager::renewSession();
            
            echo json_encode([
                'success' => true,
                'message' => 'Sesión renovada exitosamente',
                'session_info' => SessionManager::getSessionInfo()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede renovar la sesión. Por favor, inicie sesión nuevamente.',
                'redirect_to_login' => true
            ]);
        }
        exit();
    }
    
    /**
     * Limpia los datos de usuario de la sesión
     */
    private static function clearUserSession() {
        unset($_SESSION["IdUsuario"]);
        unset($_SESSION["Nombre"]);
        unset($_SESSION["Apellido"]);
        unset($_SESSION["IdRol"]);
        unset($_SESSION["Correo"]);
    }
    
    /**
     * Obtiene información de la sesión para JavaScript
     * @return string JSON con información de sesión
     */
    public static function getSessionInfoForJS() {
        $authCheck = self::checkAuthentication();
        
        return json_encode([
            'authenticated' => $authCheck['authenticated'],
            'session_info' => SessionManager::getSessionInfo(),
            'base_url' => Conectar::ruta()
        ]);
    }
    
    /**
     * Maneja las peticiones AJAX relacionadas con sesión
     */
    public static function handleAjaxRequest() {
        if (!isset($_POST['action'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Acción no especificada']);
            exit();
        }
        
        switch ($_POST['action']) {
            case 'check_session':
                self::ajaxCheckSession();
                break;
                
            case 'renew_session':
                self::ajaxRenewSession();
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida']);
                exit();
        }
    }
}

// Manejar peticiones AJAX si se accede directamente a este archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    SessionMiddleware::handleAjaxRequest();
}

?>