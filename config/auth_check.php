<?php
/**
 * Archivo de verificación de autenticación
 * Incluir este archivo en páginas que requieren autenticación
 */

require_once("conexion.php");
require_once("../middleware/SessionMiddleware.php");

// Verificar autenticación
$authCheck = SessionMiddleware::requireAuth();

// Hacer disponible la información del usuario
$currentUser = $authCheck['user_data'] ?? null;
$sessionStatus = $authCheck['session_status'] ?? null;

?>