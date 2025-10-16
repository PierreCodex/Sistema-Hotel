-- =============================================
-- STORED PROCEDURES PARA TABLA ROL
-- Estructura: IdRol, Descripcion, Estado, FechaCreacion
--  - Solo maneja IdRol
-- =============================================

-- 1. Listar todos los roles activos
DELIMITER $$
CREATE PROCEDURE SP_L_ROL_01()
BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM rol 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;

-- 2. Obtener rol por ID específico
DELIMITER $$
CREATE PROCEDURE SP_L_ROL_02(IN ROL_ID INT)
BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM rol 
    WHERE IdRol = ROL_ID;
END$$
DELIMITER ;

-- 3. Eliminar rol (cambio de estado)
DELIMITER $$
CREATE PROCEDURE SP_D_ROL_01(IN ROL_ID INT)
BEGIN
    UPDATE rol 
    SET Estado = 0 
    WHERE IdRol = ROL_ID;
END$$
DELIMITER ;

-- 4. Insertar nuevo rol
DELIMITER $$
CREATE PROCEDURE SP_I_ROL_01(
    IN ROL_NOM VARCHAR(50)
)
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdRol INTO existing_id 
    FROM rol 
    WHERE Descripcion = ROL_NOM 
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE rol 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdRol = existing_id;
        
        SELECT existing_id as IdRol, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO rol (Descripcion, Estado, FechaCreacion) 
        VALUES (ROL_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdRol, 'Registro creado' as Mensaje;
    END IF;
END$$
DELIMITER ;


-- 5. Actualizar rol existente
DELIMITER $$
CREATE PROCEDURE SP_U_ROL_01(IN ROL_ID INT, IN ROL_NOM VARCHAR(50))
BEGIN
    UPDATE rol 
    SET Descripcion = ROL_NOM 
    WHERE IdRol = ROL_ID;
END$$
DELIMITER ;

-- 6. Listar todos los roles (activos e inactivos)
DELIMITER $$
CREATE PROCEDURE SP_L_ROL_03()
BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        CASE 
            WHEN Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_DESC,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM rol 
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;

-- 7. Reactivar rol
DELIMITER $$
CREATE PROCEDURE SP_A_ROL_01(IN ROL_ID INT)
BEGIN
    UPDATE rol 
    SET Estado = 1 
    WHERE IdRol = ROL_ID;
END$$
DELIMITER ;

-- 8. Buscar roles por descripción
DELIMITER $$
CREATE PROCEDURE SP_S_ROL_01(IN BUSCAR VARCHAR(50))
BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM rol 
    WHERE Descripcion LIKE CONCAT('%', BUSCAR, '%')
    AND Estado = 1
    ORDER BY Descripcion ASC;
END$$
DELIMITER ;