<?php
/**
 * Modelo para Reportes
 * Maneja todas las consultas de reportes del sistema usando Stored Procedures
 * Refactorizado: 29/11/2025
 */
class Reporte extends Conectar {
    
    // =====================================================
    // REPORTES DE VENTAS
    // =====================================================
    
    /**
     * Obtiene el reporte completo de ventas
     */
    public function obtenerReporteVentas($fecha_inicio, $fecha_fin, $estado = '') {
        try {
            return [
                'success' => true,
                'resumen' => $this->obtenerResumenVentas($fecha_inicio, $fecha_fin, $estado),
                'ventas' => $this->obtenerListaVentas($fecha_inicio, $fecha_fin, $estado),
                'grafico' => $this->obtenerDatosGrafico($fecha_inicio, $fecha_fin, $estado, 'mensual'),
                'top_productos' => $this->obtenerTopProductos($fecha_inicio, $fecha_fin, $estado),
                'ventas_empleado' => $this->obtenerVentasPorEmpleado($fecha_inicio, $fecha_fin, $estado)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'mensaje' => 'Error al obtener reporte: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtiene el resumen estadístico de ventas
     */
    private function obtenerResumenVentas($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        // Obtener resumen principal
        $sql = "CALL SP_R_VENTAS_RESUMEN(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        // Calcular variación con período anterior
        $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400 + 1;
        $fecha_inicio_ant = date('Y-m-d', strtotime($fecha_inicio . ' - ' . $dias . ' days'));
        $fecha_fin_ant = date('Y-m-d', strtotime($fecha_fin . ' - ' . $dias . ' days'));
        
        $sql2 = "CALL SP_R_VENTAS_VARIACION(?, ?)";
        $stmt2 = $conectar->prepare($sql2);
        $stmt2->bindValue(1, $fecha_inicio_ant);
        $stmt2->bindValue(2, $fecha_fin_ant);
        $stmt2->execute();
        $anterior = $stmt2->fetch(PDO::FETCH_ASSOC);
        $stmt2->closeCursor();
        
        $total_anterior = floatval($anterior['total_anterior']);
        $total_actual = floatval($resumen['total_ventas']);
        
        if ($total_anterior > 0) {
            $resumen['variacion'] = (($total_actual - $total_anterior) / $total_anterior) * 100;
        } else {
            $resumen['variacion'] = $total_actual > 0 ? 100 : 0;
        }
        
        return $resumen;
    }
    
    /**
     * Obtiene la lista detallada de ventas
     */
    private function obtenerListaVentas($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_VENTAS_LISTA(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Obtiene datos para el gráfico de ventas
     */
    public function obtenerDatosGrafico($fecha_inicio, $fecha_fin, $estado = '', $vista = 'mensual') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        // Seleccionar SP según la vista
        switch ($vista) {
            case 'diario':
                $sp = "CALL SP_R_VENTAS_GRAFICO_DIARIO(?, ?, ?)";
                break;
            case 'semanal':
                $sp = "CALL SP_R_VENTAS_GRAFICO_SEMANAL(?, ?, ?)";
                break;
            case 'mensual':
            default:
                $sp = "CALL SP_R_VENTAS_GRAFICO_MENSUAL(?, ?, ?)";
                break;
        }
        
        $stmt = $conectar->prepare($sp);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Obtiene los productos más vendidos
     */
    private function obtenerTopProductos($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_VENTAS_TOP_PRODUCTOS(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Obtiene ventas agrupadas por empleado
     */
    private function obtenerVentasPorEmpleado($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_VENTAS_POR_EMPLEADO(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Exporta el reporte a Excel (CSV)
     */
    public function exportarExcel($fecha_inicio, $fecha_fin, $estado = '') {
        $ventas = $this->obtenerListaVentas($fecha_inicio, $fecha_fin, $estado);
        
        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d') . '.csv"');
        
        // Crear output
        $output = fopen('php://output', 'w');
        
        // BOM para Excel reconozca UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, ['ID', 'Fecha', 'Habitación', 'Cliente', 'Productos', 'Total', 'Estado', 'Empleado'], ';');
        
        // Datos
        foreach ($ventas as $venta) {
            fputcsv($output, [
                $venta['IdVenta'],
                $venta['FechaVenta'],
                $venta['NumeroHabitacion'],
                $venta['NombreCliente'],
                $venta['CantidadProductos'],
                $venta['Total'],
                $venta['Estado'],
                $venta['NombreEmpleado']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exporta el reporte a PDF
     */
    public function exportarPDF($fecha_inicio, $fecha_fin, $estado = '') {
        require_once(__DIR__ . '/../vendor/autoload.php');
        
        $ventas = $this->obtenerListaVentas($fecha_inicio, $fecha_fin, $estado);
        $resumen = $this->obtenerResumenVentas($fecha_inicio, $fecha_fin, $estado);
        
        // Crear HTML para el PDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #405189; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .resumen { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .resumen-item { background: #f8f9fa; padding: 10px; border-radius: 5px; text-align: center; width: 23%; display: inline-block; }
                .resumen-item h3 { margin: 0; color: #405189; }
                .resumen-item p { margin: 5px 0 0 0; color: #666; font-size: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #405189; color: white; padding: 8px; text-align: left; font-size: 10px; }
                td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 10px; }
                tr:nth-child(even) { background: #f8f9fa; }
                .estado-pagado { color: #0ab39c; font-weight: bold; }
                .estado-pendiente { color: #f7b84b; font-weight: bold; }
                .estado-anulado { color: #f06548; font-weight: bold; }
                .footer { margin-top: 20px; text-align: center; color: #666; font-size: 10px; }
                .total-row { background: #e3f2fd !important; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>HOTEL LAS PALMERAS</h1>
                <p>Reporte de Ventas</p>
                <p>Período: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>
            </div>
            
            <div class="resumen">
                <div class="resumen-item">
                    <h3>S/ ' . number_format($resumen['total_ventas'], 2) . '</h3>
                    <p>Total Ventas</p>
                </div>
                <div class="resumen-item">
                    <h3>' . $resumen['cantidad_ventas'] . '</h3>
                    <p>Transacciones</p>
                </div>
                <div class="resumen-item">
                    <h3>' . $resumen['productos_vendidos'] . '</h3>
                    <p>Productos Vendidos</p>
                </div>
                <div class="resumen-item">
                    <h3>S/ ' . number_format($resumen['ticket_promedio'], 2) . '</h3>
                    <p>Ticket Promedio</p>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Habitación</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Empleado</th>
                    </tr>
                </thead>
                <tbody>';
        
        $totalGeneral = 0;
        foreach ($ventas as $venta) {
            $estadoClass = 'estado-' . strtolower($venta['Estado']);
            $totalGeneral += floatval($venta['Total']);
            
            $html .= '
                    <tr>
                        <td>' . $venta['IdVenta'] . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($venta['FechaVenta'])) . '</td>
                        <td>' . ($venta['NumeroHabitacion'] ?? 'N/A') . '</td>
                        <td>' . ($venta['NombreCliente'] ?? 'Sin cliente') . '</td>
                        <td>' . $venta['CantidadProductos'] . '</td>
                        <td>S/ ' . number_format($venta['Total'], 2) . '</td>
                        <td class="' . $estadoClass . '">' . $venta['Estado'] . '</td>
                        <td>' . ($venta['NombreEmpleado'] ?? 'No registrado') . '</td>
                    </tr>';
        }
        
        $html .= '
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right;">TOTAL GENERAL:</td>
                        <td>S/ ' . number_format($totalGeneral, 2) . '</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="footer">
                <p>Reporte generado el ' . date('d/m/Y H:i:s') . '</p>
                <p>Sistema de Gestión Hotelera - Hotel Las Palmeras</p>
            </div>
        </body>
        </html>';
        
        // Generar PDF con dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // Enviar al navegador
        $dompdf->stream('reporte_ventas_' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
        exit;
    }
    
    // ========================= REPORTE DE RECEPCIONES =========================
    
    /**
     * Obtiene el reporte completo de recepciones
     */
    public function obtenerReporteRecepciones($fecha_inicio, $fecha_fin, $estado = '', $vista = 'mensual') {
        try {
            return [
                'success' => true,
                'resumen' => $this->obtenerResumenRecepciones($fecha_inicio, $fecha_fin, $estado),
                'lista' => $this->obtenerListaRecepciones($fecha_inicio, $fecha_fin, $estado),
                'grafico_ocupacion' => $this->obtenerGraficoOcupacion($fecha_inicio, $fecha_fin, $estado, $vista),
                'grafico_pisos' => $this->obtenerGraficoPisos($fecha_inicio, $fecha_fin, $estado),
                'habitaciones_top' => $this->obtenerHabitacionesMasSolicitadas($fecha_inicio, $fecha_fin, $estado),
                'tarifas' => $this->obtenerIngresosPorTarifa($fecha_inicio, $fecha_fin, $estado),
                'clientes_frecuentes' => $this->obtenerClientesFrecuentes($fecha_inicio, $fecha_fin),
                'pisos' => $this->obtenerOcupacionPorPiso($fecha_inicio, $fecha_fin, $estado)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'mensaje' => 'Error al obtener reporte: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtiene el resumen estadístico de recepciones
     */
    public function obtenerResumenRecepciones($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_RECEPCIONES_RESUMEN(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        // Calcular variación
        $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400 + 1;
        $fecha_inicio_ant = date('Y-m-d', strtotime($fecha_inicio . ' - ' . $dias . ' days'));
        $fecha_fin_ant = date('Y-m-d', strtotime($fecha_fin . ' - ' . $dias . ' days'));
        
        $sql2 = "CALL SP_R_RECEPCIONES_VARIACION(?, ?)";
        $stmt2 = $conectar->prepare($sql2);
        $stmt2->bindValue(1, $fecha_inicio_ant);
        $stmt2->bindValue(2, $fecha_fin_ant);
        $stmt2->execute();
        $anterior = $stmt2->fetch(PDO::FETCH_ASSOC);
        $stmt2->closeCursor();
        
        $total_anterior = intval($anterior['total_anterior']);
        $total_actual = intval($resumen['total_recepciones']);
        
        if ($total_anterior > 0) {
            $resumen['variacion'] = round((($total_actual - $total_anterior) / $total_anterior) * 100, 1);
        } else {
            $resumen['variacion'] = $total_actual > 0 ? 100 : 0;
        }
        
        return $resumen;
    }
    
    /**
     * Obtiene la lista detallada de recepciones
     */
    private function obtenerListaRecepciones($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_RECEPCIONES_LISTA(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Obtiene datos para el gráfico de ocupación
     */
    public function obtenerGraficoOcupacion($fecha_inicio, $fecha_fin, $estado = '', $vista = 'mensual') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        switch ($vista) {
            case 'diario':
                $sp = "CALL SP_R_RECEPCIONES_GRAFICO_DIARIO(?, ?, ?)";
                break;
            case 'semanal':
                $sp = "CALL SP_R_RECEPCIONES_GRAFICO_SEMANAL(?, ?, ?)";
                break;
            case 'mensual':
            default:
                $sp = "CALL SP_R_RECEPCIONES_GRAFICO_MENSUAL(?, ?, ?)";
                break;
        }
        
        $stmt = $conectar->prepare($sp);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return [
            'etiquetas' => array_column($datos, 'periodo'),
            'recepciones' => array_map('intval', array_column($datos, 'recepciones')),
            'ingresos' => array_map('floatval', array_column($datos, 'ingresos'))
        ];
    }
    
    /**
     * Obtiene datos para el gráfico de pisos (dona)
     */
    public function obtenerGraficoPisos($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_RECEPCIONES_GRAFICO_PISOS(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return [
            'etiquetas' => array_column($datos, 'piso'),
            'valores' => array_map('intval', array_column($datos, 'cantidad'))
        ];
    }
    
    /**
     * Obtiene las habitaciones más solicitadas
     */
    private function obtenerHabitacionesMasSolicitadas($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_RECEPCIONES_HABITACIONES_TOP(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Obtiene ingresos por tarifa
     */
    private function obtenerIngresosPorTarifa($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_RECEPCIONES_POR_TARIFA(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Obtiene clientes frecuentes
     */
    private function obtenerClientesFrecuentes($fecha_inicio, $fecha_fin) {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_RECEPCIONES_CLIENTES_FRECUENTES(?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Obtiene ocupación por piso
     */
    private function obtenerOcupacionPorPiso($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "CALL SP_R_RECEPCIONES_POR_PISO(?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->bindValue(3, $estado);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $resultado;
    }
    
    /**
     * Exporta reporte de recepciones a Excel (CSV)
     */
    public function exportarExcelRecepciones($fecha_inicio, $fecha_fin, $estado = '') {
        $recepciones = $this->obtenerListaRecepciones($fecha_inicio, $fecha_fin, $estado);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_recepciones_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['ID', 'Cliente', 'Habitación', 'Piso', 'Check-In', 'Check-Out', 'Tarifa', 'Total Pagado', 'Estado'], ';');
        
        foreach ($recepciones as $rec) {
            $checkOut = $rec['FechaSalidaConfirmacion'] ?? $rec['FechaSalida'] ?? '-';
            $estadoTexto = $rec['Estado'] == 1 ? 'En Curso' : 'Finalizada';
            
            fputcsv($output, [
                $rec['IdRecepcion'],
                $rec['NombreCliente'],
                $rec['NumeroHabitacion'],
                $rec['NombrePiso'],
                $rec['FechaEntrada'],
                $checkOut,
                $rec['NombreTarifa'],
                $rec['TotalPagado'],
                $estadoTexto
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exporta reporte de recepciones a PDF
     */
    public function exportarPDFRecepciones($fecha_inicio, $fecha_fin, $estado = '') {
        require_once(__DIR__ . '/../vendor/autoload.php');
        
        $recepciones = $this->obtenerListaRecepciones($fecha_inicio, $fecha_fin, $estado);
        $resumen = $this->obtenerResumenRecepciones($fecha_inicio, $fecha_fin, $estado);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #405189; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .resumen-item { background: #f8f9fa; padding: 10px; border-radius: 5px; text-align: center; width: 23%; display: inline-block; margin: 0 1%; }
                .resumen-item h3 { margin: 0; color: #405189; }
                .resumen-item p { margin: 5px 0 0 0; color: #666; font-size: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #405189; color: white; padding: 8px; text-align: left; font-size: 10px; }
                td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 10px; }
                tr:nth-child(even) { background: #f8f9fa; }
                .estado-activa { color: #f7b84b; font-weight: bold; }
                .estado-finalizada { color: #0ab39c; font-weight: bold; }
                .footer { margin-top: 20px; text-align: center; color: #666; font-size: 10px; }
                .total-row { background: #e3f2fd !important; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>HOTEL LAS PALMERAS</h1>
                <p>Reporte de Recepciones</p>
                <p>Período: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <div class="resumen-item">
                    <h3>' . $resumen['total_recepciones'] . '</h3>
                    <p>Total Recepciones</p>
                </div>
                <div class="resumen-item">
                    <h3>' . $resumen['recepciones_activas'] . '</h3>
                    <p>Activas</p>
                </div>
                <div class="resumen-item">
                    <h3>S/ ' . number_format($resumen['ingresos_hospedaje'], 2) . '</h3>
                    <p>Ingresos</p>
                </div>
                <div class="resumen-item">
                    <h3>' . $resumen['estancia_promedio'] . ' hrs</h3>
                    <p>Estancia Promedio</p>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Habitación</th>
                        <th>Piso</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Tarifa</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
        
        $totalGeneral = 0;
        foreach ($recepciones as $rec) {
            $checkOut = $rec['FechaSalidaConfirmacion'] ?? $rec['FechaSalida'] ?? '-';
            $estadoClass = $rec['Estado'] == 1 ? 'estado-activa' : 'estado-finalizada';
            $estadoTexto = $rec['Estado'] == 1 ? 'En Curso' : 'Finalizada';
            $totalGeneral += floatval($rec['TotalPagado']);
            
            $html .= '
                    <tr>
                        <td>' . $rec['IdRecepcion'] . '</td>
                        <td>' . $rec['NombreCliente'] . '</td>
                        <td>' . $rec['NumeroHabitacion'] . '</td>
                        <td>' . $rec['NombrePiso'] . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($rec['FechaEntrada'])) . '</td>
                        <td>' . ($checkOut != '-' ? date('d/m/Y H:i', strtotime($checkOut)) : '-') . '</td>
                        <td>' . $rec['NombreTarifa'] . '</td>
                        <td>S/ ' . number_format($rec['TotalPagado'], 2) . '</td>
                        <td class="' . $estadoClass . '">' . $estadoTexto . '</td>
                    </tr>';
        }
        
        $html .= '
                    <tr class="total-row">
                        <td colspan="7" style="text-align: right;">TOTAL RECAUDADO:</td>
                        <td>S/ ' . number_format($totalGeneral, 2) . '</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="footer">
                <p>Reporte generado el ' . date('d/m/Y H:i:s') . '</p>
                <p>Sistema de Gestión Hotelera - Hotel Las Palmeras</p>
            </div>
        </body>
        </html>';
        
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $dompdf->stream('reporte_recepciones_' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
        exit;
    }
}
?>
