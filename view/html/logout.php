<?php 
    require_once("../../config/conexion.php"); 
    $conectar = new Conectar();
    
    // Destruir sesión usando SessionManager
    SessionManager::destroy();
    
    header("Location:" . $conectar->ruta()); 
    exit(); 
?>