-- =====================================================
-- Script para agregar IdTarifa a la tabla recepcion
-- y crear el nuevo stored procedure SP_I_RECEPCION_02
-- =====================================================

-- 1. Agregar campo IdTarifa a la tabla recepcion
ALTER TABLE `recepcion` 
ADD COLUMN `IdTarifa` INT(11) NULL DEFAULT NULL AFTER `IdHabitacion`;

-- 2. Agregar foreign key (opcional pero recomendado)
ALTER TABLE `recepcion` 
ADD CONSTRAINT `fk_recepcion_tarifa` 
FOREIGN KEY (`IdTarifa`) REFERENCES `tarifa`(`IdTarifa`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- 3. Crear nuevo stored procedure que incluye IdTarifa
DELIMITER $$

DROP PROCEDURE IF EXISTS `SP_I_RECEPCION_02`$$

CREATE PROCEDURE `SP_I_RECEPCION_02` (
    IN `p_IdCliente` INT, 
    IN `p_IdHabitacion` INT, 
    IN `p_IdTarifa` INT,
    IN `p_PrecioInicial` DECIMAL(10,2), 
    IN `p_Adelanto` DECIMAL(10,2), 
    IN `p_Observacion` VARCHAR(500), 
    IN `p_FechaSalida` DATETIME, 
    OUT `p_IdRecepcion` INT
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
