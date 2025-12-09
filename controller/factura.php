<?php
/**
 * Controlador de Facturas Electrónicas
 * Maneja la emisión, consulta y PDF de facturas
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Factura.php';

class FacturaController
{
    private $factura;
    
    public function __construct()
    {
        $this->factura = new Factura();
    }
    
    /**
     * Emitir factura electrónica
     */
    public function emitir()
    {
        try {
            // Obtener datos del POST
            $rec_id = $_POST['rec_id'] ?? null;
            $usuario_id = $_POST['usuario_id'] ?? $_SESSION['IdUsuario'] ?? null;
            $metodo_pago = $_POST['metodo_pago'] ?? 'EFECTIVO';
            $forma_pago = $_POST['forma_pago'] ?? 'Contado';
            
            if (!$rec_id) {
                throw new Exception("ID de recepción no proporcionado");
            }
            
            // Obtener datos del cliente desde el POST o consultar BD
            $cliente_data = [
                'ruc' => $_POST['cliente_ruc'] ?? '',
                'razon_social' => $_POST['cliente_razon_social'] ?? '',
                'direccion' => $_POST['cliente_direccion'] ?? '',
                'ubigeo' => $_POST['cliente_ubigeo'] ?? '',
                'email' => $_POST['cliente_email'] ?? ''
            ];
            
            // Si no vienen datos del cliente, obtenerlos de la recepción
            if (empty($cliente_data['ruc'])) {
                $cliente_data = $this->factura->obtenerClienteDeRecepcion($rec_id);
            }
            
            // Validar RUC
            if (empty($cliente_data['ruc']) || strlen($cliente_data['ruc']) != 11) {
                throw new Exception("Se requiere RUC válido (11 dígitos) para emitir factura. El cliente registrado no tiene RUC.");
            }
            
            // Obtener detalles de la recepción
            $detalles = $this->factura->obtenerDetallesRecepcion($rec_id);
            
            // Configurar modelo
            $this->factura->setUsuarioId($usuario_id);
            $this->factura->setMetodoPago($metodo_pago);
            
            // Procesar cuotas si es crédito
            $cuotas = [];
            if ($forma_pago === 'Credito' && !empty($_POST['cuotas'])) {
                $cuotas = json_decode($_POST['cuotas'], true) ?: [];
            }
            
            // Generar factura
            $resultado = $this->factura->generarFactura($rec_id, $cliente_data, $detalles, $forma_pago, $cuotas);
            
            return json_encode($resultado);
            
        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }
    

    
    /**
     * Generar PDF de factura
     */
    public function pdf()
    {
        try {
            $rec_id = $_GET['rec_id'] ?? $_POST['rec_id'] ?? null;
            $tipo = $_GET['tipo'] ?? 'a4';
            
            if (!$rec_id) {
                throw new Exception("ID de recepción no proporcionado");
            }
            
            return $this->factura->generarPDF($rec_id, $tipo);
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    /**
     * Consultar factura por recepción
     */
    public function consultar()
    {
        try {
            $rec_id = $_GET['rec_id'] ?? $_POST['rec_id'] ?? null;
            
            if (!$rec_id) {
                throw new Exception("ID de recepción no proporcionado");
            }
            
            $factura = $this->factura->obtenerPorRecepcion($rec_id);
            
            if ($factura) {
                return json_encode([
                    'success' => true,
                    'factura' => $factura
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'mensaje' => 'No se encontró factura para esta recepción'
                ]);
            }
            
        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }
}

// Manejar peticiones
if (isset($_GET['op']) || isset($_POST['op'])) {
    $op = $_GET['op'] ?? $_POST['op'];
    $controller = new FacturaController();
    
    switch ($op) {
        case 'emitir':
            echo $controller->emitir();
            break;
            
        case 'pdf':
            $controller->pdf();
            break;
            
        case 'consultar':
            echo $controller->consultar();
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'mensaje' => 'Operación no válida'
            ]);
    }
}
