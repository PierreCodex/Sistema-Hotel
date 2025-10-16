<?php
/**
 * Configuración y manejo de sesiones
 * Gestiona el tiempo de vida de las sesiones y su expiración automática
 */

class SessionManager {
    
    // Tiempo de vida de la sesión en segundos (5 minutos para pruebas)
    const SESSION_LIFETIME = 300; // 5 minutos
    
    // Tiempo de advertencia antes de expirar (1 minuto antes)
    const WARNING_TIME = 60; // 1 minuto
    
    /**
     * Inicializa la configuración de sesión
     */
    public static function init() {
        // Configurar parámetros de sesión
        ini_set('session.gc_maxlifetime', self::SESSION_LIFETIME);
        ini_set('session.cookie_lifetime', self::SESSION_LIFETIME);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        
        // Configurar cookie de sesión más segura
        session_set_cookie_params([
            'lifetime' => self::SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => false, // Cambiar a true en HTTPS
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        // Inicializar timestamps de sesión
        self::initSessionTimestamps();
    }
    
    /**
     * Inicializa los timestamps de la sesión
     */
    private static function initSessionTimestamps() {
        $currentTime = time();
        
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = $currentTime;
        }
        
        if (!isset($_SESSION['LAST_ACTIVITY'])) {
            $_SESSION['LAST_ACTIVITY'] = $currentTime;
        }
    }
    
    /**
     * Verifica si la sesión ha expirado
     * @return bool
     */
    public static function isExpired() {
        if (!isset($_SESSION['LAST_ACTIVITY'])) {
            return true;
        }
        
        return (time() - $_SESSION['LAST_ACTIVITY']) > self::SESSION_LIFETIME;
    }
    
    /**
     * Verifica si la sesión está cerca de expirar
     * @return bool
     */
    public static function isNearExpiration() {
        if (!isset($_SESSION['LAST_ACTIVITY'])) {
            return false;
        }
        
        $timeLeft = self::SESSION_LIFETIME - (time() - $_SESSION['LAST_ACTIVITY']);
        return $timeLeft <= self::WARNING_TIME && $timeLeft > 0;
    }
    
    /**
     * Obtiene el tiempo restante de la sesión en segundos
     * @return int
     */
    public static function getTimeLeft() {
        if (!isset($_SESSION['LAST_ACTIVITY'])) {
            return 0;
        }
        
        $timeLeft = self::SESSION_LIFETIME - (time() - $_SESSION['LAST_ACTIVITY']);
        return max(0, $timeLeft);
    }
    
    /**
     * Renueva la sesión actualizando el timestamp de última actividad
     */
    public static function renewSession() {
        $_SESSION['LAST_ACTIVITY'] = time();
        
        // Regenerar ID de sesión periódicamente por seguridad
        if (!isset($_SESSION['LAST_REGENERATION'])) {
            $_SESSION['LAST_REGENERATION'] = time();
        } else if (time() - $_SESSION['LAST_REGENERATION'] > 300) { // Cada 5 minutos
            session_regenerate_id(true);
            $_SESSION['LAST_REGENERATION'] = time();
        }
    }
    
    /**
     * Destruye la sesión completamente
     */
    public static function destroy() {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Eliminar cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir sesión
        session_destroy();
    }
    
    /**
     * Verifica y maneja la expiración de sesión
     * @return array Estado de la sesión
     */
    public static function checkSession() {
        if (self::isExpired()) {
            return [
                'status' => 'expired',
                'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.',
                'time_left' => 0
            ];
        }
        
        if (self::isNearExpiration()) {
            return [
                'status' => 'warning',
                'message' => 'Su sesión expirará pronto. ¿Desea extenderla?',
                'time_left' => self::getTimeLeft()
            ];
        }
        
        // Renovar sesión automáticamente en cada actividad
        self::renewSession();
        
        return [
            'status' => 'active',
            'message' => 'Sesión activa',
            'time_left' => self::getTimeLeft()
        ];
    }
    
    /**
     * Obtiene información de la sesión para JavaScript
     * @return array
     */
    public static function getSessionInfo() {
        return [
            'time_left' => self::getTimeLeft(),
            'warning_time' => self::WARNING_TIME,
            'session_lifetime' => self::SESSION_LIFETIME,
            'is_near_expiration' => self::isNearExpiration(),
            'is_expired' => self::isExpired()
        ];
    }
}

?>