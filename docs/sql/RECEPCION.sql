-- Procedimiento de inserción para la tabla recepcion
-- Usa los campos principales y calcula importes derivados
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_RECEPCION_01` (
    IN p_IdCliente INT,
    IN p_IdHabitacion INT,
    IN p_PrecioInicial DECIMAL(10,2),
    IN p_Adelanto DECIMAL(10,2),
    IN p_Observacion VARCHAR(500),
    IN p_FechaSalida DATETIME,
    OUT p_IdRecepcion INT
)  
BEGIN
    DECLARE v_PrecioRestante DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_TotalPagado DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_CostoPenalidad DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_EstadoOcupado INT DEFAULT NULL;

    SET v_PrecioRestante = GREATEST(p_PrecioInicial - IFNULL(p_Adelanto, 0.00), 0.00);
    SET v_TotalPagado   = IFNULL(p_Adelanto, 0.00);
    SET v_CostoPenalidad = 0.00;

    INSERT INTO recepcion (
        IdCliente,
        IdHabitacion,
        FechaEntrada,
        FechaSalida,
        FechaSalidaConfirmacion,
        PrecioInicial,
        Adelanto,
        PrecioRestante,
        TotalPagado,
        CostoPenalidad,
        Observacion,
        Estado
    ) VALUES (
        p_IdCliente,
        p_IdHabitacion,
        NOW(),
        p_FechaSalida,
        NULL,
        p_PrecioInicial,
        IFNULL(p_Adelanto, 0.00),
        v_PrecioRestante,
        v_TotalPagado,
        v_CostoPenalidad,
        p_Observacion,
        1
    );

    -- Marcar habitación como "Ocupado" de forma dinámica
    SELECT IdEstadoHabitacion INTO v_EstadoOcupado
    FROM estado_habitacion 
    WHERE Estado = 1 
      AND UPPER(Descripcion) LIKE '%OCUPADO%'
    ORDER BY IdEstadoHabitacion ASC
    LIMIT 1;

    IF v_EstadoOcupado IS NOT NULL THEN
        UPDATE habitacion 
        SET IdEstadoHabitacion = v_EstadoOcupado
        WHERE IdHabitacion = p_IdHabitacion;
    END IF;

    SET p_IdRecepcion = LAST_INSERT_ID();
END$$
DELIMITER ;