<?php
/**
 * Generador de PDF A4 para Boletas/Facturas
 * Uso: generar-pdf-a4.php?id=123
 */

// Definir ruta base del proyecto
define('BASE_PATH', dirname(dirname(__DIR__)));

require_once(BASE_PATH . "/config/conexion.php");
require_once(BASE_PATH . "/config/session.php");
require_once(BASE_PATH . "/vendor/autoload.php");
require_once(BASE_PATH . "/models/Boleta.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// Validar parámetros
$bol_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($bol_id <= 0) {
    die("ID de comprobante no válido");
}

// Obtener datos del comprobante
$boletaModel = new Boleta();
$comp = $boletaModel->obtenerComprobantePorId($bol_id);

if (!$comp) {
    die("Comprobante no encontrado");
}

$detalles = $boletaModel->obtenerDetallesComprobante($bol_id);

// Configurar datos de la empresa
$empresa = [
    'razon_social' => 'HOTEL LAS PALMERAS S.A.C.',
    'ruc' => '20123456789',
    'direccion' => 'Av. Principal 123, Lima - Perú',
    'telefono' => '(01) 123-4567',
    'email' => 'info@hotellaspalmeras.com',
    'web' => 'www.hotellaspalmeras.com',
    'logo' => '../../assets/images/logo-dark.png'
];

// Configurar datos del comprobante
$comprobante = [
    'tipo' => $comp['bol_tipo'],
    'tipo_nombre' => ($comp['bol_tipo'] == '01') ? 'FACTURA' : 'BOLETA DE VENTA',
    'serie' => $comp['bol_serie'],
    'correlativo' => $comp['bol_correlativo'],
    'fecha_emision' => date('d/m/Y', strtotime($comp['bol_fecha_emision'])),
    'hora_emision' => date('H:i:s', strtotime($comp['bol_fecha_emision'])),
    'moneda' => 'SOLES',
    'hash' => $comp['bol_hash'] ?? '-',
    'qr_code' => null,
    'monto_letras' => convertirNumeroALetras($comp['bol_total']),
    'observaciones' => $comp['bol_observaciones'] ?? ''
];

// Datos del cliente
$cliente = [
    'documento' => $comp['bol_cliente_num_doc'] ?? '-',
    'nombre' => $comp['bol_cliente_razon_social'] ?? 'CLIENTE GENERAL',
    'direccion' => $comp['bol_cliente_direccion'] ?? '-'
];

// Preparar items
$items = [];
foreach ($detalles as $det) {
    $items[] = [
        'codigo' => $det['bol_det_codigo'] ?? '-',
        'descripcion' => $det['bol_det_descripcion'],
        'unidad' => $det['bol_det_unidad'] ?? 'UND',
        'cantidad' => floatval($det['bol_det_cantidad']),
        'precio_unitario' => floatval($det['bol_det_precio_unitario']),
        'importe' => floatval($det['bol_det_total'] ?? ($det['bol_det_cantidad'] * $det['bol_det_precio_unitario']))
    ];
}

// Totales
$totales = [
    'gravadas' => floatval($comp['bol_subtotal']),
    'exoneradas' => 0,
    'inafectas' => 0,
    'descuento' => 0,
    'igv' => floatval($comp['bol_igv']),
    'total' => floatval($comp['bol_total'])
];

// Título del documento
$titulo = $comprobante['tipo_nombre'] . ' ' . $comprobante['serie'] . '-' . str_pad($comprobante['correlativo'], 8, '0', STR_PAD_LEFT);

// Capturar el HTML del template
ob_start();
include('boleta-a4.php');
$html = ob_get_clean();

// Configurar dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nombre del archivo
$filename = $comprobante['serie'] . '-' . str_pad($comprobante['correlativo'], 8, '0', STR_PAD_LEFT) . '.pdf';

// Output
$dompdf->stream($filename, ['Attachment' => false]);

/**
 * Convierte un número a letras (español)
 */
function convertirNumeroALetras($numero) {
    $numero = number_format($numero, 2, '.', '');
    $partes = explode('.', $numero);
    $entero = intval($partes[0]);
    $decimal = isset($partes[1]) ? $partes[1] : '00';
    
    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
    
    if ($entero == 0) {
        return "CERO CON $decimal/100 SOLES";
    }
    
    if ($entero == 100) {
        return "CIEN CON $decimal/100 SOLES";
    }
    
    $letras = '';
    
    // Miles
    if ($entero >= 1000) {
        $miles = intval($entero / 1000);
        if ($miles == 1) {
            $letras .= 'MIL ';
        } else {
            $letras .= convertirCentenas($miles, $unidades, $decenas, $especiales, $centenas) . ' MIL ';
        }
        $entero = $entero % 1000;
    }
    
    // Centenas
    if ($entero >= 100) {
        if ($entero == 100) {
            $letras .= 'CIEN ';
        } else {
            $letras .= $centenas[intval($entero / 100)] . ' ';
        }
        $entero = $entero % 100;
    }
    
    // Decenas y unidades
    if ($entero >= 11 && $entero <= 19) {
        $letras .= $especiales[$entero - 11];
    } elseif ($entero >= 21 && $entero <= 29) {
        $letras .= 'VEINTI' . $unidades[$entero - 20];
    } elseif ($entero >= 10) {
        $letras .= $decenas[intval($entero / 10)];
        if ($entero % 10 > 0) {
            $letras .= ' Y ' . $unidades[$entero % 10];
        }
    } elseif ($entero > 0) {
        $letras .= $unidades[$entero];
    }
    
    return trim($letras) . " CON $decimal/100 SOLES";
}

function convertirCentenas($numero, $unidades, $decenas, $especiales, $centenas) {
    $letras = '';
    
    if ($numero >= 100) {
        if ($numero == 100) {
            return 'CIEN';
        }
        $letras .= $centenas[intval($numero / 100)] . ' ';
        $numero = $numero % 100;
    }
    
    if ($numero >= 11 && $numero <= 19) {
        $letras .= $especiales[$numero - 11];
    } elseif ($numero >= 21 && $numero <= 29) {
        $letras .= 'VEINTI' . $unidades[$numero - 20];
    } elseif ($numero >= 10) {
        $letras .= $decenas[intval($numero / 10)];
        if ($numero % 10 > 0) {
            $letras .= ' Y ' . $unidades[$numero % 10];
        }
    } elseif ($numero > 0) {
        $letras .= $unidades[$numero];
    }
    
    return trim($letras);
}
