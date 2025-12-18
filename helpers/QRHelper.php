<?php
/**
 * Helper para generar código QR para comprobantes electrónicos
 * Usa la librería phpqrcode
 */

// Incluir la librería phpqrcode
require_once(__DIR__ . '/../vendor/phpqrcode/qrlib.php');

class QRHelper {
    
    // URL base para consulta de tickets (cambiar en producción)
    // LOCAL: http://localhost/SistemaHotel-PHP/ticket/
    // PRODUCCION: https://tudominio.com/ticket/
    private static $URL_BASE = 'http://localhost/SistemaHotel-PHP/ticket/';
    
    /**
     * Genera el código QR con la URL del ticket
     * El cliente escanea y ve su comprobante en: URL_BASE + SERIE-CORRELATIVO
     * Ejemplo: http://localhost/SistemaHotel-PHP/ticket/B001-00000001
     * 
     * @param array $datos Array con los datos del comprobante
     * @return string Ruta absoluta del archivo PNG del QR
     */
    public static function generarQRComprobante($datos) {
        // Construir URL del ticket
        $serie = $datos['serie'] ?? 'B001';
        $correlativo = $datos['correlativo'] ?? '00000001';
        
        // URL que el cliente verá al escanear
        $url = self::$URL_BASE . $serie . '-' . $correlativo;
        
        // Crear directorio temporal si no existe
        $tempDir = __DIR__ . '/../storage/temp/qr';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $filename = 'qr_' . $serie . '_' . $correlativo . '.png';
        $filepath = $tempDir . '/' . $filename;
        
        // Generar QR y guardar como archivo PNG
        QRcode::png($url, $filepath, QR_ECLEVEL_M, 4, 2);
        
        // Retornar la ruta absoluta del archivo
        return $filepath;
    }
    
    /**
     * Cambiar la URL base (útil para producción)
     */
    public static function setUrlBase($url) {
        self::$URL_BASE = $url;
    }
    
    /**
     * Genera QR como base64 para uso en HTML (no para dompdf)
     * 
     * @param array $datos Datos del comprobante
     * @return string Base64 de la imagen PNG
     */
    public static function generarQRBase64($datos) {
        // Construir cadena de texto para el QR
        $contenido = implode('|', [
            $datos['ruc_emisor'] ?? '20123456789',
            $datos['tipo_doc'] ?? '03',
            $datos['serie'] ?? 'B001',
            $datos['correlativo'] ?? '00000001',
            number_format($datos['igv'] ?? 0, 2, '.', ''),
            number_format($datos['total'] ?? 0, 2, '.', ''),
            $datos['fecha_emision'] ?? date('Y-m-d'),
            $datos['tipo_doc_cliente'] ?? '1',
            $datos['num_doc_cliente'] ?? '',
            $datos['hash'] ?? ''
        ]);
        
        // Generar QR como imagen PNG en memoria
        ob_start();
        QRcode::png($contenido, null, QR_ECLEVEL_M, 4, 2);
        $imageData = ob_get_clean();
        
        // Convertir a Base64
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Genera QR simple con texto personalizado
     * 
     * @param string $texto Texto para el QR
     * @return string Ruta absoluta del archivo PNG
     */
    public static function generarQRSimple($texto) {
        $tempDir = __DIR__ . '/../storage/temp/qr';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $filename = 'qr_' . md5($texto) . '.png';
        $filepath = $tempDir . '/' . $filename;
        
        QRcode::png($texto, $filepath, QR_ECLEVEL_M, 4, 2);
        
        return $filepath;
    }
    
    /**
     * Limpia archivos QR temporales antiguos (más de 1 hora)
     */
    public static function limpiarTemporales() {
        $tempDir = __DIR__ . '/../storage/temp/qr';
        if (!is_dir($tempDir)) return;
        
        $files = glob($tempDir . '/qr_*.png');
        $now = time();
        
        foreach ($files as $file) {
            if ($now - filemtime($file) > 3600) { // 1 hora
                unlink($file);
            }
        }
    }
}
