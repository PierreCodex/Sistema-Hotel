<?php
/**
 * Modelo para Reportes
 * Maneja todas las consultas de reportes del sistema
 * Usa FechaCreacion de la tabla venta como fecha de venta
 */
class Reporte extends Conectar {
    
    /**
     * Obtiene el reporte completo de ventas
     * @param string $fecha_inicio Fecha de inicio (Y-m-d)
     * @param string $fecha_fin Fecha de fin (Y-m-d)
     * @param string $estado Estado de la venta (opcional)
     * @return array Datos del reporte
     */
    public function obtenerReporteVentas($fecha_inicio, $fecha_fin, $estado = '') {
        try {
            // Obtener resumen
            $resumen = $this->obtenerResumenVentas($fecha_inicio, $fecha_fin, $estado);
            
            // Obtener lista de ventas
            $ventas = $this->obtenerListaVentas($fecha_inicio, $fecha_fin, $estado);
            
            // Obtener datos para gráfico
            $grafico = $this->obtenerDatosGrafico($fecha_inicio, $fecha_fin, $estado, 'mensual');
            
            // Obtener top productos
            $top_productos = $this->obtenerTopProductos($fecha_inicio, $fecha_fin, $estado);
            
            // Obtener ventas por empleado
            $ventas_empleado = $this->obtenerVentasPorEmpleado($fecha_inicio, $fecha_fin, $estado);
            
            return [
                'success' => true,
                'resumen' => $resumen,
                'ventas' => $ventas,
                'grafico' => $grafico,
                'top_productos' => $top_productos,
                'ventas_empleado' => $ventas_empleado
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
        
        $where = "WHERE DATE(v.FechaCreacion) BETWEEN ? AND ? AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'";
        $params = [$fecha_inicio, $fecha_fin];
        
        if (!empty($estado)) {
            $where .= " AND v.Estado = ?";
            $params[] = $estado;
        }
        
        // Total de ventas y cantidad
        $sql = "SELECT 
                    COALESCE(SUM(v.Total), 0) AS total_ventas,
                    COUNT(v.IdVenta) AS cantidad_ventas,
                    COALESCE(AVG(v.Total), 0) AS ticket_promedio
                FROM venta v
                $where";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Productos vendidos
        $sql_productos = "SELECT COALESCE(SUM(dv.Cantidad), 0) AS productos_vendidos
                          FROM detalle_venta dv
                          INNER JOIN venta v ON dv.IdVenta = v.IdVenta
                          $where";
        
        $stmt2 = $conectar->prepare($sql_productos);
        foreach ($params as $i => $param) {
            $stmt2->bindValue($i + 1, $param);
        }
        $stmt2->execute();
        $productos = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        $resumen['productos_vendidos'] = $productos['productos_vendidos'];
        
        // Calcular variación (comparar con período anterior)
        $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400 + 1;
        $fecha_inicio_ant = date('Y-m-d', strtotime($fecha_inicio . ' - ' . $dias . ' days'));
        $fecha_fin_ant = date('Y-m-d', strtotime($fecha_fin . ' - ' . $dias . ' days'));
        
        $sql_ant = "SELECT COALESCE(SUM(v.Total), 0) AS total_anterior
                    FROM venta v
                    WHERE DATE(v.FechaCreacion) BETWEEN ? AND ? 
                    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'";
        
        $stmt3 = $conectar->prepare($sql_ant);
        $stmt3->bindValue(1, $fecha_inicio_ant);
        $stmt3->bindValue(2, $fecha_fin_ant);
        $stmt3->execute();
        $anterior = $stmt3->fetch(PDO::FETCH_ASSOC);
        
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
        
        $where = "WHERE DATE(v.FechaCreacion) BETWEEN ? AND ? AND v.Estado != 'BORRADOR'";
        $params = [$fecha_inicio, $fecha_fin];
        
        if (!empty($estado)) {
            $where .= " AND v.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    v.IdVenta,
                    v.FechaCreacion AS FechaVenta,
                    v.Total,
                    v.Estado,
                    h.Numero AS NumeroHabitacion,
                    CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente,
                    CONCAT(u.Nombre, ' ', u.Apellido) AS NombreEmpleado,
                    (SELECT COUNT(*) FROM detalle_venta WHERE IdVenta = v.IdVenta) AS CantidadProductos
                FROM venta v
                INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                LEFT JOIN cliente c ON r.IdCliente = c.IdCliente
                LEFT JOIN boleta b ON r.IdRecepcion = b.rec_id
                LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
                $where
                ORDER BY v.FechaCreacion DESC";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene datos para el gráfico de ventas
     * @param string $vista diario, semanal o mensual
     */
    public function obtenerDatosGrafico($fecha_inicio, $fecha_fin, $estado = '', $vista = 'mensual') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $where = "WHERE DATE(v.FechaCreacion) BETWEEN ? AND ? AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'";
        $params = [$fecha_inicio, $fecha_fin];
        
        if (!empty($estado)) {
            $where .= " AND v.Estado = ?";
            $params[] = $estado;
        }
        
        // Definir agrupación según vista
        switch ($vista) {
            case 'diario':
                $groupBy = "DATE(v.FechaCreacion)";
                $selectPeriodo = "DATE_FORMAT(v.FechaCreacion, '%d/%m') AS periodo";
                break;
            case 'semanal':
                $groupBy = "YEARWEEK(v.FechaCreacion, 1)";
                $selectPeriodo = "CONCAT('Sem ', WEEK(v.FechaCreacion, 1)) AS periodo";
                break;
            case 'mensual':
            default:
                $groupBy = "DATE_FORMAT(v.FechaCreacion, '%Y-%m')";
                $selectPeriodo = "DATE_FORMAT(v.FechaCreacion, '%b %Y') AS periodo";
                break;
        }
        
        $sql = "SELECT 
                    $selectPeriodo,
                    COALESCE(SUM(v.Total), 0) AS total,
                    COUNT(v.IdVenta) AS cantidad
                FROM venta v
                $where
                GROUP BY $groupBy
                ORDER BY MIN(v.FechaCreacion) ASC";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los productos más vendidos
     */
    private function obtenerTopProductos($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $where = "WHERE DATE(v.FechaCreacion) BETWEEN ? AND ? AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'";
        $params = [$fecha_inicio, $fecha_fin];
        
        if (!empty($estado)) {
            $where .= " AND v.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    p.Nombre AS NombreProducto,
                    SUM(dv.Cantidad) AS CantidadTotal,
                    SUM(dv.SubTotal) AS TotalVendido
                FROM detalle_venta dv
                INNER JOIN producto p ON dv.IdProducto = p.IdProducto
                INNER JOIN venta v ON dv.IdVenta = v.IdVenta
                $where
                GROUP BY p.IdProducto, p.Nombre
                ORDER BY CantidadTotal DESC
                LIMIT 10";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene ventas agrupadas por empleado
     */
    private function obtenerVentasPorEmpleado($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $where = "WHERE DATE(v.FechaCreacion) BETWEEN ? AND ? AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'";
        $params = [$fecha_inicio, $fecha_fin];
        
        if (!empty($estado)) {
            $where .= " AND v.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    COALESCE(CONCAT(u.Nombre, ' ', u.Apellido), 'No registrado') AS NombreEmpleado,
                    COUNT(v.IdVenta) AS CantidadVentas,
                    COALESCE(SUM(v.Total), 0) AS TotalVentas
                FROM venta v
                INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
                LEFT JOIN boleta b ON r.IdRecepcion = b.rec_id
                LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
                $where
                GROUP BY u.IdUsuario
                ORDER BY TotalVentas DESC";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    private function obtenerResumenRecepciones($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $where = "WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($estado !== '') {
            $where .= " AND r.Estado = ?";
            $params[] = $estado;
        }
        
        // Total de recepciones
        $sql = "SELECT 
                    COUNT(r.IdRecepcion) AS total_recepciones,
                    SUM(CASE WHEN r.Estado = 1 THEN 1 ELSE 0 END) AS recepciones_activas,
                    COALESCE(SUM(r.TotalPagado), 0) AS ingresos_hospedaje
                FROM recepcion r
                $where";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Estancia promedio en horas (solo de recepciones finalizadas)
        $sql_estancia = "SELECT 
                            AVG(TIMESTAMPDIFF(HOUR, r.FechaEntrada, COALESCE(r.FechaSalidaConfirmacion, r.FechaSalida, NOW()))) AS estancia_promedio
                         FROM recepcion r
                         WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        
        $stmt2 = $conectar->prepare($sql_estancia);
        $stmt2->bindValue(1, $fecha_inicio);
        $stmt2->bindValue(2, $fecha_fin);
        $stmt2->execute();
        $estancia = $stmt2->fetch(PDO::FETCH_ASSOC);
        $resumen['estancia_promedio'] = round($estancia['estancia_promedio'] ?? 0, 1);
        
        // Calcular variación con período anterior
        $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400 + 1;
        $fecha_inicio_ant = date('Y-m-d', strtotime($fecha_inicio . ' - ' . $dias . ' days'));
        $fecha_fin_ant = date('Y-m-d', strtotime($fecha_fin . ' - ' . $dias . ' days'));
        
        $sql_ant = "SELECT COUNT(*) AS total_anterior FROM recepcion WHERE DATE(FechaEntrada) BETWEEN ? AND ?";
        $stmt3 = $conectar->prepare($sql_ant);
        $stmt3->bindValue(1, $fecha_inicio_ant);
        $stmt3->bindValue(2, $fecha_fin_ant);
        $stmt3->execute();
        $anterior = $stmt3->fetch(PDO::FETCH_ASSOC);
        
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
        
        $where = "WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($estado !== '') {
            $where .= " AND r.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    r.IdRecepcion,
                    CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente,
                    h.Numero AS NumeroHabitacion,
                    p.Descripcion AS NombrePiso,
                    r.FechaEntrada,
                    r.FechaSalida,
                    r.FechaSalidaConfirmacion,
                    COALESCE(t.Descripcion, 'Sin tarifa') AS NombreTarifa,
                    r.TotalPagado,
                    r.Estado
                FROM recepcion r
                INNER JOIN cliente c ON r.IdCliente = c.IdCliente
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                INNER JOIN piso p ON h.IdPiso = p.IdPiso
                LEFT JOIN tarifa t ON r.IdTarifa = t.IdTarifa
                $where
                ORDER BY r.FechaEntrada DESC";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene datos para el gráfico de ocupación
     */
    public function obtenerGraficoOcupacion($fecha_inicio, $fecha_fin, $estado = '', $vista = 'mensual') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $where = "WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($estado !== '') {
            $where .= " AND r.Estado = ?";
            $params[] = $estado;
        }
        
        // Definir agrupación según vista
        switch ($vista) {
            case 'diario':
                $groupBy = "DATE(r.FechaEntrada)";
                $selectPeriodo = "DATE_FORMAT(r.FechaEntrada, '%d/%m') AS periodo";
                break;
            case 'semanal':
                $groupBy = "YEARWEEK(r.FechaEntrada, 1)";
                $selectPeriodo = "CONCAT('Sem ', WEEK(r.FechaEntrada, 1)) AS periodo";
                break;
            case 'mensual':
            default:
                $groupBy = "DATE_FORMAT(r.FechaEntrada, '%Y-%m')";
                $selectPeriodo = "DATE_FORMAT(r.FechaEntrada, '%b %Y') AS periodo";
                break;
        }
        
        $sql = "SELECT 
                    $selectPeriodo,
                    COUNT(r.IdRecepcion) AS recepciones,
                    COALESCE(SUM(r.TotalPagado), 0) AS ingresos
                FROM recepcion r
                $where
                GROUP BY $groupBy
                ORDER BY MIN(r.FechaEntrada) ASC";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        $where = "WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($estado !== '') {
            $where .= " AND r.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    p.Descripcion AS piso,
                    COUNT(r.IdRecepcion) AS cantidad
                FROM recepcion r
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                INNER JOIN piso p ON h.IdPiso = p.IdPiso
                $where
                GROUP BY p.IdPiso, p.Descripcion
                ORDER BY cantidad DESC";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        $where = "WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($estado !== '') {
            $where .= " AND r.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    h.Numero AS NumeroHabitacion,
                    cat.Descripcion AS Categoria,
                    COUNT(r.IdRecepcion) AS TotalRecepciones,
                    COALESCE(SUM(r.TotalPagado), 0) AS Ingresos
                FROM recepcion r
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                LEFT JOIN categoria cat ON h.IdCategoria = cat.IdCategoria
                $where
                GROUP BY h.IdHabitacion, h.Numero, cat.Descripcion
                ORDER BY TotalRecepciones DESC
                LIMIT 10";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene ingresos por tarifa
     */
    private function obtenerIngresosPorTarifa($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $where = "WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($estado !== '') {
            $where .= " AND r.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    COALESCE(t.Descripcion, 'Sin tarifa') AS NombreTarifa,
                    COUNT(r.IdRecepcion) AS TotalRecepciones,
                    COALESCE(SUM(r.TotalPagado), 0) AS Total
                FROM recepcion r
                LEFT JOIN tarifa t ON r.IdTarifa = t.IdTarifa
                $where
                GROUP BY r.IdTarifa, t.Descripcion
                ORDER BY Total DESC";
        
        $stmt = $conectar->prepare($sql);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene clientes frecuentes
     */
    private function obtenerClientesFrecuentes($fecha_inicio, $fecha_fin) {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "SELECT 
                    CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente,
                    c.Documento,
                    COUNT(r.IdRecepcion) AS Visitas,
                    COALESCE(SUM(r.TotalPagado), 0) AS TotalGastado
                FROM recepcion r
                INNER JOIN cliente c ON r.IdCliente = c.IdCliente
                WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?
                GROUP BY c.IdCliente, c.Nombre, c.Apellido, c.Documento
                HAVING COUNT(r.IdRecepcion) >= 1
                ORDER BY Visitas DESC, TotalGastado DESC
                LIMIT 10";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene ocupación por piso
     */
    private function obtenerOcupacionPorPiso($fecha_inicio, $fecha_fin, $estado = '') {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $where = "WHERE DATE(r.FechaEntrada) BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($estado !== '') {
            $where .= " AND r.Estado = ?";
            $params[] = $estado;
        }
        
        $sql = "SELECT 
                    p.Descripcion AS NombrePiso,
                    (SELECT COUNT(*) FROM habitacion WHERE IdPiso = p.IdPiso AND Estado = 1) AS TotalHabitaciones,
                    COUNT(r.IdRecepcion) AS TotalRecepciones,
                    COALESCE(SUM(r.TotalPagado), 0) AS Ingresos
                FROM piso p
                LEFT JOIN habitacion h ON p.IdPiso = h.IdPiso AND h.Estado = 1
                LEFT JOIN recepcion r ON h.IdHabitacion = r.IdHabitacion 
                    AND DATE(r.FechaEntrada) BETWEEN ? AND ?
                " . ($estado !== '' ? " AND r.Estado = ?" : "") . "
                WHERE p.Estado = 1
                GROUP BY p.IdPiso, p.Descripcion
                ORDER BY TotalRecepciones DESC";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        if ($estado !== '') {
            $stmt->bindValue(3, $estado);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
