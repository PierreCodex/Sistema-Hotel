-- =============================================
-- STORED PROCEDURES CORREGIDOS PARA TABLA CATEGORIA
-- Estructura: IdCategoria, Descripcion, Estado, FechaCreacion
-- =============================================

-- Eliminar procedimientos existentes si existen


-- 1. Listar todas las categorías activas
DELIMITER $$
CREATE PROCEDURE SP_L_CATEGORIA_01()
BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM categoria 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;

-- 2. Obtener categoría por ID específico
DELIMITER $$
CREATE PROCEDURE SP_L_CATEGORIA_02(IN CAT_ID INT)
BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM categoria 
    WHERE IdCategoria = CAT_ID;
END$$
DELIMITER ;

-- 3. Eliminar categoría (cambio de estado)
DELIMITER $$
CREATE PROCEDURE SP_D_CATEGORIA_01(IN CAT_ID INT)
BEGIN
    UPDATE categoria 
    SET Estado = 0 
    WHERE IdCategoria = CAT_ID;
END$$
DELIMITER ;

-- 4. Insertar nueva categoría (versión mejorada)
DELIMITER $$
CREATE PROCEDURE SP_I_CATEGORIA_01(IN CAT_NOM VARCHAR(150))
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdCategoria INTO existing_id 
    FROM categoria 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(CAT_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE categoria 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdCategoria = existing_id;
        
        SELECT existing_id as IdCategoria, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO categoria (Descripcion, Estado, FechaCreacion) 
        VALUES (CAT_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdCategoria, 'Registro creado' as Mensaje;
    END IF;
END$$
DELIMITER ;

-- 5. Actualizar categoría existente
DELIMITER $$
CREATE PROCEDURE SP_U_CATEGORIA_01(IN CAT_ID INT, IN CAT_NOM VARCHAR(150))
BEGIN
    UPDATE categoria 
    SET Descripcion = CAT_NOM 
    WHERE IdCategoria = CAT_ID AND Estado = 1;
END$$
DELIMITER ;

-- Verificar que los procedimientos se crearon correctamente
SHOW PROCEDURE STATUS WHERE Name LIKE 'SP_%CATEGORIA%';