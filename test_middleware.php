<?php
// Archivo de prueba para verificar el middleware
header('Content-Type: application/json');

try {
    require_once("config/conexion.php");
    require_once("middleware/SessionMiddleware.php");
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'check_session':
                $authCheck = SessionMiddleware::checkAuthentication();
                echo json_encode([
                    'success' => true,
                    'authenticated' => $authCheck['authenticated'],
                    'session_status' => $authCheck['session_status'],
                    'message' => 'Middleware funcionando correctamente'
                ]);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'error' => 'Acción no válida'
                ]);
                break;
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido o acción no especificada'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en el middleware: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>