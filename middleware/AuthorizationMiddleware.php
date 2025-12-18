<?php
/**
 * Middleware para verificación de permisos de usuario
 * Valida que el usuario tenga acceso autorizado al módulo solicitado
 */

require_once(__DIR__ . "/../config/conexion.php");
require_once(__DIR__ . "/SessionMiddleware.php");
require_once(__DIR__ . "/../models/Rol.php");

class AuthorizationMiddleware {
    
    /**
     * Verifica si el usuario tiene permiso para acceder a un módulo específico
     * @param string $moduleIdentifier Identificador del módulo (MEN_IDENTI)
     * @param string $deniedRedirectUrl URL de redirección si no tiene permiso (opcional)
     * @return array Información de autorización
     */
    public static function checkPermission($moduleIdentifier, $deniedRedirectUrl = null) {
        // Primero verificar autenticación
        $authCheck = SessionMiddleware::checkAuthentication();
        
        if (!$authCheck['authenticated']) {
            return [
                'authorized' => false,
                'reason' => 'not_authenticated',
                'redirect_url' => Conectar::ruta() . "index.php"
            ];
        }
        
        // Obtener ID del usuario
        $userId = $_SESSION["IdUsuario"];
        
        // Validar permiso usando el modelo Rol
        $rolModel = new Rol();
        $permissionCheck = $rolModel->validar_acceso_rol($userId, $moduleIdentifier);
        
        // Si el resultado tiene datos, el usuario tiene permiso
        $hasPermission = is_array($permissionCheck) && count($permissionCheck) > 0;
        
        if (!$hasPermission) {
            return [
                'authorized' => false,
                'reason' => 'no_permission',
                'module' => $moduleIdentifier,
                'redirect_url' => $deniedRedirectUrl ?? Conectar::ruta() . "view/403/"
            ];
        }
        
        return [
            'authorized' => true,
            'module' => $moduleIdentifier,
            'user_data' => $authCheck['user_data'],
            'permission_data' => $permissionCheck[0]
        ];
    }
    
    /**
     * Middleware para proteger páginas que requieren permiso específico
     * Redirige automáticamente si no tiene acceso
     * @param string $moduleIdentifier Identificador del módulo (MEN_IDENTI)
     * @param string $deniedRedirectUrl URL de redirección si no tiene permiso (opcional)
     */
    public static function requirePermission($moduleIdentifier, $deniedRedirectUrl = null) {
        // PRIMERO: Verificar sesión única (solo en páginas, no en AJAX)
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            require_once(__DIR__ . "/../config/session_check.php");
        }
        
        // SEGUNDO: Verificar permisos
        $permissionCheck = self::checkPermission($moduleIdentifier, $deniedRedirectUrl);
        
        if (!$permissionCheck['authorized']) {
            // Registrar intento de acceso no autorizado (opcional - para auditoría)
            self::logUnauthorizedAccess($moduleIdentifier, $permissionCheck['reason']);
            
            // Redirigir según el motivo
            header("Location: " . $permissionCheck['redirect_url']);
            exit();
        }
        
        return $permissionCheck;
    }
    
    /**
     * Verifica múltiples permisos (el usuario debe tener AL MENOS UNO)
     * @param array $moduleIdentifiers Array de identificadores de módulos
     * @return bool True si tiene al menos un permiso
     */
    public static function hasAnyPermission($moduleIdentifiers) {
        // Verificar autenticación primero
        $authCheck = SessionMiddleware::checkAuthentication();
        if (!$authCheck['authenticated']) {
            return false;
        }
        
        $userId = $_SESSION["IdUsuario"];
        $rolModel = new Rol();
        
        foreach ($moduleIdentifiers as $moduleId) {
            $permissionCheck = $rolModel->validar_acceso_rol($userId, $moduleId);
            if (is_array($permissionCheck) && count($permissionCheck) > 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Verifica múltiples permisos (el usuario debe tener TODOS)
     * @param array $moduleIdentifiers Array de identificadores de módulos
     * @return bool True si tiene todos los permisos
     */
    public static function hasAllPermissions($moduleIdentifiers) {
        // Verificar autenticación primero
        $authCheck = SessionMiddleware::checkAuthentication();
        if (!$authCheck['authenticated']) {
            return false;
        }
        
        $userId = $_SESSION["IdUsuario"];
        $rolModel = new Rol();
        
        foreach ($moduleIdentifiers as $moduleId) {
            $permissionCheck = $rolModel->validar_acceso_rol($userId, $moduleId);
            if (!is_array($permissionCheck) || count($permissionCheck) === 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Endpoint AJAX para verificar permisos
     */
    public static function ajaxCheckPermission() {
        header('Content-Type: application/json');
        
        if (!isset($_POST['module'])) {
            echo json_encode([
                'error' => 'Módulo no especificado'
            ]);
            exit();
        }
        
        $permissionCheck = self::checkPermission($_POST['module']);
        
        echo json_encode([
            'authorized' => $permissionCheck['authorized'],
            'module' => $permissionCheck['module'] ?? null,
            'reason' => $permissionCheck['reason'] ?? null
        ]);
        exit();
    }
    
    /**
     * Registra intentos de acceso no autorizado para auditoría
     * @param string $moduleIdentifier Identificador del módulo
     * @param string $reason Razón del rechazo
     */
    private static function logUnauthorizedAccess($moduleIdentifier, $reason) {
        // Opcional: Implementar logging en base de datos o archivo
        // Por ahora solo registramos en error_log para desarrollo
        if (isset($_SESSION["IdUsuario"])) {
            $userId = $_SESSION["IdUsuario"];
            $userName = $_SESSION["Nombre"] . " " . $_SESSION["Apellido"];
            $logMessage = sprintf(
                "[ACCESO DENEGADO] Usuario: %s (ID: %d) | Módulo: %s | Razón: %s | IP: %s | Fecha: %s",
                $userName,
                $userId,
                $moduleIdentifier,
                $reason,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                date('Y-m-d H:i:s')
            );
            error_log($logMessage);
        }
    }
    
    /**
     * Obtiene lista de módulos a los que el usuario actual tiene acceso
     * @return array Lista de módulos con permisos
     */
    public static function getUserPermissions() {
        $authCheck = SessionMiddleware::checkAuthentication();
        
        if (!$authCheck['authenticated']) {
            return [];
        }
        
        $userId = $_SESSION["IdUsuario"];
        $rolId = $_SESSION["IdRol"];
        
        $rolModel = new Rol();
        
        try {
            $conectar = Conectar::conexion();
            Conectar::set_names();
            
            $sql = "CALL SP_L_MENU_01(?)";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $rolId);
            $stmt->execute();
            
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filtrar solo los que tienen permiso 'Si'
            return array_filter($permissions, function($perm) {
                return $perm['MEND_PERMI'] === 'Si';
            });
            
        } catch (Exception $e) {
            error_log("Error obteniendo permisos: " . $e->getMessage());
            return [];
        }
    }
}

// Manejar peticiones AJAX si se accede directamente a este archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_permission') {
    AuthorizationMiddleware::ajaxCheckPermission();
}

?>
