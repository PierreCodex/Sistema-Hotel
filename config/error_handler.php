<?php
/**
 * Manejador Centralizado de Errores
 * Sistema Hotel Paris - Seguridad Mejorada
 * 
 * Este archivo configura el manejo de errores de forma segura
 * ocultando información sensible al usuario final
 */

// Determinar si estamos en producción o desarrollo
// Cambiar a 'production' cuando se despliegue en servidor real
define('ENVIRONMENT', 'development'); // Cambiar a 'production' en servidor real

// Configuración según el entorno
if (ENVIRONMENT === 'production') {
    // PRODUCCIÓN: Ocultar todos los errores al usuario
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    
    // Definir archivo de log de errores
    $log_path = __DIR__ . '/../logs/php_errors.log';
    
    // Crear directorio de logs si no existe
    if (!file_exists(dirname($log_path))) {
        mkdir(dirname($log_path), 0755, true);
    }
    
    ini_set('error_log', $log_path);
    
} else {
    // DESARROLLO: Mostrar errores para debugging
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    
    $log_path = __DIR__ . '/../logs/php_errors_dev.log';
    if (!file_exists(dirname($log_path))) {
        mkdir(dirname($log_path), 0755, true);
    }
    ini_set('error_log', $log_path);
}

/**
 * Manejador personalizado de errores
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // No procesar errores suprimidos con @
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    // Registrar el error en el log
    $error_message = sprintf(
        "[%s] Error %d: %s en %s línea %d",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    
    error_log($error_message);
    
    // En producción, mostrar mensaje genérico al usuario
    if (ENVIRONMENT === 'production') {
        // No mostrar detalles técnicos
        return true;
    }
    
    // En desarrollo, permitir que PHP maneje el error normalmente
    return false;
}

/**
 * Manejador de excepciones no capturadas
 */
function customExceptionHandler($exception) {
    // Registrar la excepción
    $error_message = sprintf(
        "[%s] Excepción no capturada: %s en %s línea %d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    error_log($error_message);
    
    // Mostrar página de error amigable en producción
    if (ENVIRONMENT === 'production') {
        http_response_code(500);
        include __DIR__ . '/error_page.php';
        exit();
    } else {
        // En desarrollo, mostrar el error completo
        echo "<h1>Excepción no capturada</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $exception->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    }
}

/**
 * Manejador de errores fatales
 */
function customShutdownHandler() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = sprintf(
            "[%s] Error Fatal: %s en %s línea %d",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        error_log($error_message);
        
        if (ENVIRONMENT === 'production') {
            http_response_code(500);
            include __DIR__ . '/error_page.php';
        }
    }
}

// Registrar los manejadores personalizados
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');
register_shutdown_function('customShutdownHandler');

/**
 * Función helper para registrar errores personalizados
 */
function logError($message, $context = []) {
    $log_message = sprintf(
        "[%s] %s | Context: %s",
        date('Y-m-d H:i:s'),
        $message,
        json_encode($context)
    );
    error_log($log_message);
}

/**
 * Función helper para registrar información de debugging
 */
function logDebug($message, $data = null) {
    if (ENVIRONMENT === 'development') {
        $log_message = sprintf(
            "[%s] DEBUG: %s",
            date('Y-m-d H:i:s'),
            $message
        );
        
        if ($data !== null) {
            $log_message .= " | Data: " . print_r($data, true);
        }
        
        error_log($log_message);
    }
}
