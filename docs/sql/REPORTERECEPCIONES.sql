-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS PARA REPORTES
-- Sistema Hotel Las Palmeras
-- Fecha: 29/11/2025
-- =====================================================

DELIMITER //

-- =====================================================
-- REPORTES DE VENTAS
-- =====================================================




-- =====================================================
-- REPORTES DE RECEPCIONES
-- =====================================================

-- SP_R_RECEPCIONES_RESUMEN: Resumen estadístico de recepciones
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_RESUMEN//
CREATE PROCEDURE SP_R_RECEPCIONES_RESUMEN(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        COUNT(r.IdRecepcion) AS total_recepciones,
        SUM(CASE WHEN r.Estado = 1 THEN 1 ELSE 0 END) AS recepciones_activas,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos_hospedaje,
        ROUND(AVG(TIMESTAMPDIFF(HOUR, r.FechaEntrada, 
            COALESCE(r.FechaSalidaConfirmacion, r.FechaSalida, NOW()))), 1) AS estancia_promedio
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado);
END//

-- SP_R_RECEPCIONES_VARIACION: Total del período anterior para variación
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_VARIACION//
CREATE PROCEDURE SP_R_RECEPCIONES_VARIACION(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT COUNT(*) AS total_anterior 
    FROM recepcion 
    WHERE DATE(FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin;
END//

-- SP_R_RECEPCIONES_LISTA: Lista detallada de recepciones
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_LISTA//
CREATE PROCEDURE SP_R_RECEPCIONES_LISTA(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
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
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    ORDER BY r.FechaEntrada DESC;
END//

-- SP_R_RECEPCIONES_GRAFICO_DIARIO: Gráfico ocupación diario
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_GRAFICO_DIARIO//
CREATE PROCEDURE SP_R_RECEPCIONES_GRAFICO_DIARIO(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        DATE_FORMAT(r.FechaEntrada, '%d/%m') AS periodo,
        COUNT(r.IdRecepcion) AS recepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY DATE(r.FechaEntrada)
    ORDER BY MIN(r.FechaEntrada) ASC;
END//

-- SP_R_RECEPCIONES_GRAFICO_SEMANAL: Gráfico ocupación semanal
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_GRAFICO_SEMANAL//
CREATE PROCEDURE SP_R_RECEPCIONES_GRAFICO_SEMANAL(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        CONCAT('Sem ', WEEK(r.FechaEntrada, 1)) AS periodo,
        COUNT(r.IdRecepcion) AS recepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY YEARWEEK(r.FechaEntrada, 1)
    ORDER BY MIN(r.FechaEntrada) ASC;
END//

-- SP_R_RECEPCIONES_GRAFICO_MENSUAL: Gráfico ocupación mensual
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_GRAFICO_MENSUAL//
CREATE PROCEDURE SP_R_RECEPCIONES_GRAFICO_MENSUAL(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        DATE_FORMAT(r.FechaEntrada, '%b %Y') AS periodo,
        COUNT(r.IdRecepcion) AS recepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY DATE_FORMAT(r.FechaEntrada, '%Y-%m')
    ORDER BY MIN(r.FechaEntrada) ASC;
END//

-- SP_R_RECEPCIONES_GRAFICO_PISOS: Distribución por pisos
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_GRAFICO_PISOS//
CREATE PROCEDURE SP_R_RECEPCIONES_GRAFICO_PISOS(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        p.Descripcion AS piso,
        COUNT(r.IdRecepcion) AS cantidad
    FROM recepcion r
    INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
    INNER JOIN piso p ON h.IdPiso = p.IdPiso
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY p.IdPiso, p.Descripcion
    ORDER BY cantidad DESC;
END//

-- SP_R_RECEPCIONES_HABITACIONES_TOP: Habitaciones más solicitadas
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_HABITACIONES_TOP//
CREATE PROCEDURE SP_R_RECEPCIONES_HABITACIONES_TOP(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        h.Numero AS NumeroHabitacion,
        cat.Descripcion AS Categoria,
        COUNT(r.IdRecepcion) AS TotalRecepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS Ingresos
    FROM recepcion r
    INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
    LEFT JOIN categoria cat ON h.IdCategoria = cat.IdCategoria
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY h.IdHabitacion, h.Numero, cat.Descripcion
    ORDER BY TotalRecepciones DESC
    LIMIT 10;
END//

-- SP_R_RECEPCIONES_POR_TARIFA: Ingresos por tarifa
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_POR_TARIFA//
CREATE PROCEDURE SP_R_RECEPCIONES_POR_TARIFA(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        COALESCE(t.Descripcion, 'Sin tarifa') AS NombreTarifa,
        COUNT(r.IdRecepcion) AS TotalRecepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS Total
    FROM recepcion r
    LEFT JOIN tarifa t ON r.IdTarifa = t.IdTarifa
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY r.IdTarifa, t.Descripcion
    ORDER BY Total DESC;
END//

-- SP_R_RECEPCIONES_CLIENTES_FRECUENTES: Clientes frecuentes
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_CLIENTES_FRECUENTES//
CREATE PROCEDURE SP_R_RECEPCIONES_CLIENTES_FRECUENTES(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT 
        CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente,
        c.Documento,
        COUNT(r.IdRecepcion) AS Visitas,
        COALESCE(SUM(r.TotalPagado), 0) AS TotalGastado
    FROM recepcion r
    INNER JOIN cliente c ON r.IdCliente = c.IdCliente
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    GROUP BY c.IdCliente, c.Nombre, c.Apellido, c.Documento
    HAVING COUNT(r.IdRecepcion) >= 1
    ORDER BY Visitas DESC, TotalGastado DESC
    LIMIT 10;
END//

-- SP_R_RECEPCIONES_POR_PISO: Ocupación por piso
DROP PROCEDURE IF EXISTS SP_R_RECEPCIONES_POR_PISO//
CREATE PROCEDURE SP_R_RECEPCIONES_POR_PISO(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT 
        p.Descripcion AS NombrePiso,
        (SELECT COUNT(*) FROM habitacion WHERE IdPiso = p.IdPiso AND Estado = 1) AS TotalHabitaciones,
        COUNT(r.IdRecepcion) AS TotalRecepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS Ingresos
    FROM piso p
    LEFT JOIN habitacion h ON p.IdPiso = h.IdPiso AND h.Estado = 1
    LEFT JOIN recepcion r ON h.IdHabitacion = r.IdHabitacion 
        AND DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
        AND (p_estado = '' OR r.Estado = p_estado)
    WHERE p.Estado = 1
    GROUP BY p.IdPiso, p.Descripcion
    ORDER BY TotalRecepciones DESC;
END//

DELIMITER ;

-- Mensaje de confirmación
SELECT 'Procedimientos almacenados de Reportes creados exitosamente' AS Mensaje;
