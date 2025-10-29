-- =============================================
-- STORED PROCEDURES CORREGIDOS PARA TABLA PISO
-- Estructura: IdPiso, Descripcion, Estado, FechaCreacion
-- =============================================

-- Eliminar procedimientos existentes si existen


-- 1. Listar todos los pisos activos e inactivos:
DELIMITER $$
CREATE PROCEDURE SP_L_PISO_01()
BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM piso 
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;


-- 1. Listar todos los pisos activos
DELIMITER $$
CREATE PROCEDURE SP_L_PISO_03()
BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM piso 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;



-- 2. Obtener piso por ID específico
DELIMITER $$
CREATE PROCEDURE SP_L_PISO_02(IN PISO_ID INT)
BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM piso 
    WHERE IdPiso = PISO_ID;
END$$
DELIMITER ;

-- 3. Eliminar piso ()
DELIMITER $$
CREATE PROCEDURE SP_D_PISO_01(IN PISO_ID INT)
BEGIN
    DELETE FROM piso 
    WHERE IdPiso = PISO_ID;
END$$
DELIMITER ;

-- 4. Insertar nuevo piso
DELIMITER $$
CREATE PROCEDURE SP_I_PISO_01(IN PISO_NOM VARCHAR(50))
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdPiso INTO existing_id 
    FROM piso 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(PISO_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE piso 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdPiso = existing_id;
        
        SELECT existing_id as IdPiso, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO piso (Descripcion, Estado, FechaCreacion) 
        VALUES (PISO_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdPiso, 'Registro creado' as Mensaje;
    END IF;
END$$
DELIMITER ;

-- 5. Actualizar piso existente
DELIMITER $$
CREATE PROCEDURE SP_U_PISO_01(IN PISO_ID INT, IN PISO_NOM VARCHAR(50))
BEGIN
    UPDATE piso 
    SET Descripcion = PISO_NOM 
    WHERE IdPiso = PISO_ID AND Estado = 1;
END$$
DELIMITER ;

-- Verificar que los procedimientos se crearon correctamente
SHOW PROCEDURE STATUS WHERE Name LIKE 'SP_%PISO%';

--Cambiar estado del Piso (activar/desactivar)
DELIMITER $$
CREATE PROCEDURE SP_CAMBIAR_ESTADO_PISO_01(IN PISO_ID INT, IN NUEVO_ESTADO INT)
BEGIN
    UPDATE piso 
    SET Estado = NUEVO_ESTADO 
    WHERE IdPiso = PISO_ID;
END$$
DELIMITER ;