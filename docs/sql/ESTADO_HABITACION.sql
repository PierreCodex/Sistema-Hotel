-- =============================================
-- Stored Procedures para Estado Habitación
-- =============================================

-- Procedimiento para listar todos los estados de habitación
DELIMITER $$
CREATE PROCEDURE SP_L_ESTADO_HABITACION_01()
BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    ORDER BY IdEstadoHabitacion DESC;
END$$
DELIMITER ;

-- Procedimiento para listar estados de habitación activos
DELIMITER $$
CREATE PROCEDURE SP_L_ESTADO_HABITACION_03()
BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    WHERE Estado = 1
    ORDER BY Descripcion ASC;
END$$
DELIMITER ;

-- Procedimiento para obtener estado de habitación por ID
DELIMITER $$
CREATE PROCEDURE SP_L_ESTADO_HABITACION_02(IN p_est_hab_id INT)
BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    WHERE IdEstadoHabitacion = p_est_hab_id;
END$$
DELIMITER ;

-- Procedimiento para eliminar estado de habitación
DELIMITER $$
CREATE PROCEDURE SP_D_ESTADO_HABITACION_01(IN p_est_hab_id INT)
BEGIN
    DELETE FROM estado_habitacion 
    WHERE IdEstadoHabitacion = p_est_hab_id;
END$$
DELIMITER ;

-- Procedimiento para insertar nuevo estado de habitación
DELIMITER $$
CREATE PROCEDURE SP_I_ESTADO_HABITACION_01(IN EST_HAB_NOM VARCHAR(50))
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdEstadoHabitacion INTO existing_id 
    FROM estado_habitacion 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(EST_HAB_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE estado_habitacion 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdEstadoHabitacion = existing_id;
        
        SELECT existing_id as IdEstadoHabitacion, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO estado_habitacion (Descripcion, Estado, FechaCreacion) 
        VALUES (EST_HAB_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdEstadoHabitacion, 'Registro creado' as Mensaje;
    END IF;
END$$
DELIMITER ;

-- Procedimiento para actualizar estado de habitación
DELIMITER $$
CREATE PROCEDURE SP_U_ESTADO_HABITACION_01(IN EST_HAB_ID INT, IN EST_HAB_NOM VARCHAR(50))
BEGIN
    UPDATE estado_habitacion 
    SET Descripcion = EST_HAB_NOM 
    WHERE IdEstadoHabitacion = EST_HAB_ID AND Estado = 1;
END$$
DELIMITER ;

-- Procedimiento para cambiar estado del estado de habitación
DELIMITER $$
CREATE PROCEDURE SP_CAMBIAR_ESTADO_ESTADO_HABITACION_01(IN EST_HAB_ID INT, IN NUEVO_ESTADO INT)
BEGIN
    UPDATE estado_habitacion 
    SET Estado = NUEVO_ESTADO 
    WHERE IdEstadoHabitacion = EST_HAB_ID;
END$$
DELIMITER ;