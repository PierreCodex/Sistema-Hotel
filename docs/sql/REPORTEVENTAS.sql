DROP PROCEDURE IF EXISTS SP_R_VENTAS_RESUMEN//
CREATE PROCEDURE SP_R_VENTAS_RESUMEN(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT 
        COALESCE(SUM(v.Total), 0) AS total_ventas,
        COUNT(v.IdVenta) AS cantidad_ventas,
        COALESCE(AVG(v.Total), 0) AS ticket_promedio,
        (
            SELECT COALESCE(SUM(dv.Cantidad), 0) 
            FROM detalle_venta dv
            INNER JOIN venta v2 ON dv.IdVenta = v2.IdVenta
            WHERE DATE(v2.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
            AND v2.Estado != 'BORRADOR' AND v2.Estado != 'ANULADO'
            AND (p_estado = '' OR v2.Estado = p_estado)
        ) AS productos_vendidos
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado);
END//



DELIMITER $$
-- SP_R_VENTAS_VARIACION: Obtiene el total del período anterior para calcular variación
DROP PROCEDURE IF EXISTS SP_R_VENTAS_VARIACION//
CREATE PROCEDURE SP_R_VENTAS_VARIACION(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT COALESCE(SUM(v.Total), 0) AS total_anterior
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO';
END$$
DELIMITER ;



-- SP_R_VENTAS_LISTA: Obtiene la lista detallada de ventas
DROP PROCEDURE IF EXISTS SP_R_VENTAS_LISTA//
CREATE PROCEDURE SP_R_VENTAS_LISTA(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT 
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
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR'
    AND (p_estado = '' OR v.Estado = p_estado)
    ORDER BY v.FechaCreacion DESC;
END//


-- SP_R_VENTAS_GRAFICO_DIARIO: Datos para gráfico vista diaria
DROP PROCEDURE IF EXISTS SP_R_VENTAS_GRAFICO_DIARIO//
CREATE PROCEDURE SP_R_VENTAS_GRAFICO_DIARIO(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT 
        DATE_FORMAT(v.FechaCreacion, '%d/%m') AS periodo,
        COALESCE(SUM(v.Total), 0) AS total,
        COUNT(v.IdVenta) AS cantidad
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY DATE(v.FechaCreacion)
    ORDER BY MIN(v.FechaCreacion) ASC;
END//

-- SP_R_VENTAS_GRAFICO_SEMANAL: Datos para gráfico vista semanal
DROP PROCEDURE IF EXISTS SP_R_VENTAS_GRAFICO_SEMANAL//
SP_R_VENTAS_TOP_PRODUCTOS//

-- SP_R_VENTAS_GRAFICO_MENSUAL: Datos para gráfico vista mensual
DROP PROCEDURE IF EXISTS SP_R_VENTAS_GRAFICO_MENSUAL//
CREATE PROCEDURE SP_R_VENTAS_GRAFICO_MENSUAL(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT 
        DATE_FORMAT(v.FechaCreacion, '%b %Y') AS periodo,
        COALESCE(SUM(v.Total), 0) AS total,
        COUNT(v.IdVenta) AS cantidad
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY DATE_FORMAT(v.FechaCreacion, '%Y-%m')
    ORDER BY MIN(v.FechaCreacion) ASC;
END//

-- SP_R_VENTAS_TOP_PRODUCTOS: Productos más vendidos
DROP PROCEDURE IF EXISTS SP_R_VENTAS_TOP_PRODUCTOS//
CREATE PROCEDURE SP_R_VENTAS_TOP_PRODUCTOS(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT 
        p.Nombre AS NombreProducto,
        SUM(dv.Cantidad) AS CantidadTotal,
        SUM(dv.SubTotal) AS TotalVendido
    FROM detalle_venta dv
    INNER JOIN producto p ON dv.IdProducto = p.IdProducto
    INNER JOIN venta v ON dv.IdVenta = v.IdVenta
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY p.IdProducto, p.Nombre
    ORDER BY CantidadTotal DESC
    LIMIT 10;
END//

-- SP_R_VENTAS_POR_EMPLEADO: Ventas agrupadas por empleado
DROP PROCEDURE IF EXISTS SP_R_VENTAS_POR_EMPLEADO//
CREATE PROCEDURE SP_R_VENTAS_POR_EMPLEADO(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT 
        COALESCE(CONCAT(u.Nombre, ' ', u.Apellido), 'No registrado') AS NombreEmpleado,
        COUNT(v.IdVenta) AS CantidadVentas,
        COALESCE(SUM(v.Total), 0) AS TotalVentas
    FROM venta v
    INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
    LEFT JOIN boleta b ON r.IdRecepcion = b.rec_id
    LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY u.IdUsuario
    ORDER BY TotalVentas DESC;
END//