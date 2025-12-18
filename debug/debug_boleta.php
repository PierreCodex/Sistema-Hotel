<?php
// Script de debug temporal para probar generación de boleta
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular la petición
$_POST['rec_id'] = isset($_GET['rec_id']) ? $_GET['rec_id'] : 1;
$_POST['tipo_doc'] = '03';
$_POST['metodo_pago'] = 'EFECTIVO';

echo "<h2>Debug: Generación de Boleta</h2>";
echo "<p>rec_id: " . $_POST['rec_id'] . "</p>";

try {
    require_once("../config/conexion.php");
    require_once("../models/Boleta.php");
    require_once("../models/Recepcion.php");
    require_once("../models/Cliente.php");
    require_once("../models/Venta.php");
    
    echo "<p>✅ Archivos cargados correctamente</p>";
    
    $boleta = new Boleta();
    $recepcion = new Recepcion();
    $cliente = new Cliente();
    $venta = new Venta();
    
    echo "<p>✅ Clases instanciadas correctamente</p>";
    
    // Verificar sesión
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Usuario ID: " . ($_SESSION['IdUsuario'] ?? 'NO DEFINIDO') . "</p>";
    
    $rec_id = intval($_POST['rec_id']);
    
    // Verificar boleta existente
    echo "<h3>Verificando boleta existente...</h3>";
    $boletaExistente = $boleta->verificarBoleta($rec_id);
    
    if ($boletaExistente) {
        echo "<p style='color:green'>✅ Boleta ya existe: " . $boletaExistente['bol_serie'] . "-" . $boletaExistente['bol_correlativo'] . "</p>";
    } else {
        echo "<p>❌ No existe boleta, se debe crear una nueva</p>";
        
        // Obtener recepción
        echo "<h3>Obteniendo datos de recepción...</h3>";
        $recData = $recepcion->get_recepcion_x_id($rec_id);
        
        if (!$recData) {
            echo "<p style='color:red'>❌ ERROR: Recepción no encontrada</p>";
        } else {
            echo "<pre>" . print_r($recData, true) . "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
