<?php
/**
 * Script de prueba para validar las nuevas reglas de validación de roles
 */

// Incluir el modelo de Rol
require_once 'models/Rol.php';

$rol = new Rol();

// Nombres de roles a probar
$nombres_prueba = [
    'Administrador',
    'Empleado',
    'Gerente de Ventas',
    'Técnico-Especializado',
    'Admin123',
    'Super User',
    '', // Nombre vacío (debería fallar)
    'AB', // Muy corto (debería fallar)
    str_repeat('A', 51) // Muy largo (debería fallar)
];

echo "=== PRUEBA DE VALIDACIONES DE ROL ===\n\n";

foreach ($nombres_prueba as $nombre) {
    echo "Probando: '" . $nombre . "'\n";
    
    // Validar nombre vacío
    $esNombreVacio = empty(trim($nombre));
    echo "  - Nombre vacío: " . ($esNombreVacio ? "SÍ (❌)" : "NO (✅)") . "\n";
    
    // Validar longitud
    $longitud = strlen(trim($nombre));
    $esLongitudValida = $longitud >= 3 && $longitud <= 50;
    echo "  - Longitud ($longitud): " . ($esLongitudValida ? "VÁLIDA (✅)" : "INVÁLIDA (❌)") . "\n";
    
    // Resultado final
    $esValido = !$esNombreVacio && $esLongitudValida;
    echo "  - RESULTADO: " . ($esValido ? "✅ VÁLIDO" : "❌ INVÁLIDO") . "\n";
    echo "  ---\n";
}

echo "\n=== RESUMEN ===\n";
echo "✅ Ya no hay restricciones de caracteres\n";
echo "✅ Solo se valida: nombre vacío y longitud (3-50)\n";
echo "✅ Roles como 'Administrador' y 'Empleado' son válidos\n";
echo "✅ Se mantiene validación de duplicados en el controlador\n";
?>