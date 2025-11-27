<?php
require_once("../config/conexion.php");
require_once("../models/Reporte.php");

$reporte = new Reporte();

// Manejar peticiones POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operacion = isset($_POST['operacion']) ? $_POST['operacion'] : '';
    
    switch ($operacion) {
        case 'reporte_ventas':
            $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');
            $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
            
            $resultado = $reporte->obtenerReporteVentas($fecha_inicio, $fecha_fin, $estado);
            echo json_encode($resultado);
            break;
            
        case 'grafico_ventas':
            $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');
            $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
            $vista = isset($_POST['vista']) ? $_POST['vista'] : 'mensual';
            
            $grafico = $reporte->obtenerDatosGrafico($fecha_inicio, $fecha_fin, $estado, $vista);
            echo json_encode(['success' => true, 'grafico' => $grafico]);
            break;
            
        // ===================== REPORTE DE RECEPCIONES =====================
        case 'obtener_reporte_recepciones':
            $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');
            $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
            $vista_grafico = isset($_POST['vista_grafico']) ? $_POST['vista_grafico'] : 'mensual';
            
            $resultado = $reporte->obtenerReporteRecepciones($fecha_inicio, $fecha_fin, $estado, $vista_grafico);
            
            if ($resultado['success']) {
                echo json_encode([
                    'status' => true,
                    'resumen' => $resultado['resumen'],
                    'lista' => $resultado['lista'],
                    'grafico_ocupacion' => $resultado['grafico_ocupacion'],
                    'grafico_pisos' => $resultado['grafico_pisos'],
                    'habitaciones_top' => $resultado['habitaciones_top'],
                    'tarifas' => $resultado['tarifas'],
                    'clientes_frecuentes' => $resultado['clientes_frecuentes'],
                    'pisos' => $resultado['pisos']
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => $resultado['mensaje']
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Operación no válida']);
            break;
    }
}

// Manejar peticiones GET (exportación)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $operacion = isset($_GET['operacion']) ? $_GET['operacion'] : '';
    
    switch ($operacion) {
        case 'exportar_excel':
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
            $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
            
            $reporte->exportarExcel($fecha_inicio, $fecha_fin, $estado);
            break;
            
        case 'exportar_pdf':
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
            $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
            
            $reporte->exportarPDF($fecha_inicio, $fecha_fin, $estado);
            break;
            
        // ===================== EXPORTAR RECEPCIONES =====================
        case 'exportar_excel_recepciones':
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
            $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
            
            $reporte->exportarExcelRecepciones($fecha_inicio, $fecha_fin, $estado);
            break;
            
        case 'exportar_pdf_recepciones':
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
            $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
            
            $reporte->exportarPDFRecepciones($fecha_inicio, $fecha_fin, $estado);
            break;
    }
}
