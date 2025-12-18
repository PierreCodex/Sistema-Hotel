<?php
/**
 * Modelo Dashboard
 * Proporciona estadísticas para el panel principal
 */
class Dashboard extends Conectar {
    
    /**
     * Obtiene estadísticas generales para el Administrador
     */
    public function obtenerEstadisticasAdmin() {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $stats = [];
        
        // Total de habitaciones activas
        $sql = "SELECT COUNT(*) as total FROM habitacion WHERE Estado = 1";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['total_habitaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Habitaciones disponibles
        $sql = "SELECT COUNT(*) as total FROM habitacion h 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.Estado = 1 AND UPPER(eh.Descripcion) LIKE '%DISPONIBLE%'";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['habitaciones_disponibles'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Habitaciones ocupadas (recepciones activas)
        $sql = "SELECT COUNT(*) as total FROM recepcion WHERE Estado = 1";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['habitaciones_ocupadas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Habitaciones en limpieza
        $sql = "SELECT COUNT(*) as total FROM habitacion h 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.Estado = 1 AND UPPER(eh.Descripcion) LIKE '%LIMPIEZA%'";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['habitaciones_limpieza'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ingresos del día (recepciones)
        $sql = "SELECT COALESCE(SUM(TotalPagado), 0) as total FROM recepcion 
                WHERE DATE(FechaEntrada) = CURDATE()";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['ingresos_hospedaje_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ventas del día
        $sql = "SELECT COALESCE(SUM(Total), 0) as total FROM venta 
                WHERE DATE(FechaCreacion) = CURDATE() AND Estado NOT IN ('ANULADO', 'BORRADOR')";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['ventas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ingresos del mes (recepciones)
        $sql = "SELECT COALESCE(SUM(TotalPagado), 0) as total FROM recepcion 
                WHERE MONTH(FechaEntrada) = MONTH(CURDATE()) AND YEAR(FechaEntrada) = YEAR(CURDATE())";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['ingresos_hospedaje_mes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ventas del mes
        $sql = "SELECT COALESCE(SUM(Total), 0) as total FROM venta 
                WHERE MONTH(FechaCreacion) = MONTH(CURDATE()) AND YEAR(FechaCreacion) = YEAR(CURDATE())
                AND Estado NOT IN ('ANULADO', 'BORRADOR')";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['ventas_mes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total clientes registrados
        $sql = "SELECT COUNT(*) as total FROM cliente WHERE Estado = 1";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['total_clientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total usuarios activos
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE Estado = 1";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Recepciones del mes
        $sql = "SELECT COUNT(*) as total FROM recepcion 
                WHERE MONTH(FechaEntrada) = MONTH(CURDATE()) AND YEAR(FechaEntrada) = YEAR(CURDATE())";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['recepciones_mes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Check-outs pendientes hoy
        $sql = "SELECT COUNT(*) as total FROM recepcion 
                WHERE Estado = 1 AND DATE(FechaSalida) <= CURDATE()";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['checkouts_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $stats;
    }
    
    /**
     * Obtiene estadísticas para el Empleado
     */
    public function obtenerEstadisticasEmpleado() {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $stats = [];
        
        // Habitaciones disponibles
        $sql = "SELECT COUNT(*) as total FROM habitacion h 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.Estado = 1 AND UPPER(eh.Descripcion) LIKE '%DISPONIBLE%'";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['habitaciones_disponibles'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Habitaciones ocupadas
        $sql = "SELECT COUNT(*) as total FROM recepcion WHERE Estado = 1";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['habitaciones_ocupadas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Check-ins de hoy
        $sql = "SELECT COUNT(*) as total FROM recepcion 
                WHERE DATE(FechaEntrada) = CURDATE()";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['checkins_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Check-outs pendientes hoy
        $sql = "SELECT COUNT(*) as total FROM recepcion 
                WHERE Estado = 1 AND DATE(FechaSalida) <= CURDATE()";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['checkouts_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Habitaciones en limpieza
        $sql = "SELECT COUNT(*) as total FROM habitacion h 
                INNER JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion 
                WHERE h.Estado = 1 AND UPPER(eh.Descripcion) LIKE '%LIMPIEZA%'";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['habitaciones_limpieza'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ventas pendientes (del empleado hoy)
        $sql = "SELECT COUNT(*) as total FROM venta 
                WHERE DATE(FechaCreacion) = CURDATE() AND Estado = 'PENDIENTE'";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $stats['ventas_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $stats;
    }
    
    /**
     * Obtiene las últimas recepciones activas
     * @param int $limit Número de registros a retornar
     * @param int|null $usuarioId ID del usuario para filtrar (null = todas)
     */
    public function obtenerRecepcionesActivas($limit = 5, $usuarioId = null) {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "SELECT 
                    r.IdRecepcion,
                    CONCAT(c.Nombre, ' ', c.Apellido) AS Cliente,
                    h.Numero AS Habitacion,
                    r.FechaEntrada,
                    r.FechaSalida,
                    r.TotalPagado
                FROM recepcion r
                INNER JOIN cliente c ON r.IdCliente = c.IdCliente
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                WHERE r.Estado = 1";
        
        // Si se proporciona usuarioId, filtrar por usuario
        if ($usuarioId !== null) {
            $sql .= " AND r.IdUsuario = :usuarioId";
        }
        
        $sql .= " ORDER BY r.FechaEntrada DESC LIMIT :limit";
        
        $stmt = $conectar->prepare($sql);
        
        if ($usuarioId !== null) {
            $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene las últimas ventas
     */
    public function obtenerUltimasVentas($limit = 5) {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "SELECT 
                    v.IdVenta,
                    h.Numero AS Habitacion,
                    v.Total,
                    v.Estado,
                    v.FechaCreacion
                FROM venta v
                INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
                INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
                WHERE v.Estado != 'BORRADOR'
                ORDER BY v.FechaCreacion DESC
                LIMIT ?";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene datos para el gráfico de ingresos de los últimos 7 días
     */
    public function obtenerIngresosUltimos7Dias() {
        $conectar = parent::Conexion();
        parent::set_names();
        
        $sql = "SELECT 
                    DATE_FORMAT(fecha, '%d/%m') AS dia,
                    COALESCE(hospedaje, 0) AS hospedaje,
                    COALESCE(ventas, 0) AS ventas
                FROM (
                    SELECT DATE(DATE_SUB(CURDATE(), INTERVAL n DAY)) AS fecha
                    FROM (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
                          UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) nums
                ) fechas
                LEFT JOIN (
                    SELECT DATE(FechaEntrada) AS fecha_r, SUM(TotalPagado) AS hospedaje
                    FROM recepcion
                    WHERE FechaEntrada >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    GROUP BY DATE(FechaEntrada)
                ) rec ON fechas.fecha = rec.fecha_r
                LEFT JOIN (
                    SELECT DATE(FechaCreacion) AS fecha_v, SUM(Total) AS ventas
                    FROM venta
                    WHERE FechaCreacion >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    AND Estado NOT IN ('ANULADO', 'BORRADOR')
                    GROUP BY DATE(FechaCreacion)
                ) ven ON fechas.fecha = ven.fecha_v
                ORDER BY fechas.fecha ASC";
        
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
