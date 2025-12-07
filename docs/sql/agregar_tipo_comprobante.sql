-- Script para agregar el campo TipoComprobante a la tabla recepcion
-- Ejecutar en phpMyAdmin o consola MySQL

-- Agregar columna TipoComprobante a la tabla recepcion
-- '03' = Boleta (por defecto), '01' = Factura
ALTER TABLE `recepcion` 
ADD COLUMN `TipoComprobante` VARCHAR(2) NOT NULL DEFAULT '03' 
COMMENT '01=Factura, 03=Boleta' 
AFTER `Observacion`;

-- Crear nuevo stored procedure que incluye TipoComprobante
DELIMITER $$

DROP PROCEDURE IF EXISTS `SP_I_RECEPCION_03`$$

CREATE PROCEDURE `SP_I_RECEPCION_03` (
    IN `p_IdCliente` INT, 
    IN `p_IdHabitacion` INT, 
    IN `p_IdTarifa` INT,
    IN `p_PrecioInicial` DECIMAL(10,2), 
    IN `p_Adelanto` DECIMAL(10,2), 
    IN `p_Observacion` VARCHAR(500), 
    IN `p_FechaSalida` DATETIME, 
    IN `p_TipoComprobante` VARCHAR(2),
    OUT `p_IdRecepcion` INT
)
BEGIN
    DECLARE v_PrecioRestante DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_TotalPagado DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_CostoPenalidad DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_EstadoOcupado INT DEFAULT NULL;
    DECLARE v_TipoComp VARCHAR(2);

    SET v_PrecioRestante = GREATEST(p_PrecioInicial - IFNULL(p_Adelanto, 0.00), 0.00);
    SET v_TotalPagado   = IFNULL(p_Adelanto, 0.00);
    SET v_CostoPenalidad = 0.00;
    -- Si no viene tipo de comprobante, usar Boleta por defecto
    SET v_TipoComp = IFNULL(p_TipoComprobante, '03');

    INSERT INTO recepcion (
        IdCliente,
        IdHabitacion,
        IdTarifa,
        FechaEntrada,
        FechaSalida,
        FechaSalidaConfirmacion,
        PrecioInicial,
        Adelanto,
        PrecioRestante,
        TotalPagado,
        CostoPenalidad,
        Observacion,
        TipoComprobante,
        Estado
    ) VALUES (
        p_IdCliente,
        p_IdHabitacion,
        p_IdTarifa,
        NOW(),
        p_FechaSalida,
        NULL,
        p_PrecioInicial,
        IFNULL(p_Adelanto, 0.00),
        v_PrecioRestante,
        v_TotalPagado,
        v_CostoPenalidad,
        p_Observacion,
        v_TipoComp,
        1
    );

    SET p_IdRecepcion = LAST_INSERT_ID();

    -- Actualizar estado de habitación a OCUPADO (IdEstadoHabitacion = 11)
    SELECT IdEstadoHabitacion INTO v_EstadoOcupado 
    FROM estado_habitacion 
    WHERE UPPER(Descripcion) LIKE '%OCUPAD%' 
    LIMIT 1;

    IF v_EstadoOcupado IS NOT NULL THEN
        UPDATE habitacion 
        SET IdEstadoHabitacion = v_EstadoOcupado 
        WHERE IdHabitacion = p_IdHabitacion;
    END IF;
END$$

DELIMITER ;

-- Verificar la estructura
DESCRIBE `recepcion`;
