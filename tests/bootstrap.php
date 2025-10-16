<?php
/**
 * Bootstrap file para las pruebas PHPUnit
 */

// Incluir el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Incluir archivos necesarios del proyecto
require_once __DIR__ . '/../config/conexion.php';

// Configurar el entorno de pruebas
define('TESTING', true);

// Configurar la zona horaria
date_default_timezone_set('America/Lima');

// Configurar el manejo de errores para pruebas
error_reporting(E_ALL);
ini_set('display_errors', 1);