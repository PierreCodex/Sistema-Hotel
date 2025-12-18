<?php
/**
 * Middleware de verificación de sesión única
 * Verifica que el token de sesión del usuario coincida con el almacenado en BD
 * Si no coincide, significa que inició sesión desde otro dispositivo
 */

// Solo ejecutar si hay sesión activa
if (isset($_SESSION['IdUsuario']) && isset($_SESSION['session_token'])) {
    
    require_once(__DIR__ . "/../models/Usuario.php");
    
    $usuario = new Usuario();
    $usuario_id = $_SESSION['IdUsuario'];
    $session_token = $_SESSION['session_token'];
    
    // Verificar si el token de sesión coincide con el de la BD
    $token_valido = $usuario->verificar_session_token($usuario_id, $session_token);
    
    if (!$token_valido) {
        // Token no coincide - sesión cerrada desde otro dispositivo
        // Esta es la sesión ANTERIOR, establecer flag y cerrar
        $flag_sesion_cerrada = true;
        
        session_unset();
        session_destroy();
        
        // Iniciar nueva sesión temporal solo para el flag
        session_start();
        $_SESSION['previous_session_closed'] = $flag_sesion_cerrada;
        
        // Redirigir a login CON mensaje
        header("Location: " . Conectar::ruta() . "index.php");
        exit();
    }
}
?>
