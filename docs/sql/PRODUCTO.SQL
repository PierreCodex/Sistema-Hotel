-- 1. Listar todas las productos activas e inactivas:
DELIMITER $$
CREATE PROCEDURE SP_L_PRODUCTO_01()
BEGIN
    SELECT 
        IdProducto AS PRO_ID,
        Nombre AS PRO_NOM,
        Detalle AS PRO_DET,
        Precio AS PRO_PRE,
        Cantidad AS PRO_CANT,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM producto 
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;


-- 1. Listar todas las productos activas
DELIMITER $$
CREATE PROCEDURE SP_L_PRODUCTO_03()
BEGIN
    SELECT 
        IdProducto AS PRO_ID,
        Nombre AS PRO_NOM,
        Detalle AS PRO_DET,
        Precio AS PRO_PRE,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM producto 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;


-- 2. Obtener producto por ID específico
DELIMITER $$
CREATE PROCEDURE SP_L_PRODUCTO_02(IN PRO_ID INT)
BEGIN
    SELECT 
        IdProducto AS PRO_ID,
        Nombre AS PRO_NOM,
        Detalle AS PRO_DET,
        Precio AS PRO_PRE,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM producto 
    WHERE IdProducto = PRO_ID;
END$$
DELIMITER ;

-- 3. Eliminar producto
DELIMITER $$
CREATE PROCEDURE SP_D_PRODUCTO_01(IN PRO_ID INT)
BEGIN
    DELETE FROM producto 
    WHERE IdProducto = PRO_ID;
END$$
DELIMITER ;

-- 4. Insertar nueva producto
DELIMITER $$
CREATE PROCEDURE SP_I_PRODUCTO_01(IN PRO_NOM VARCHAR(50), IN PRO_DET VARCHAR(100), IN PRO_PRE DECIMAL(10,2), IN PRO_CANT INT)
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdProducto INTO existing_id 
    FROM producto 
    WHERE UPPER(TRIM(Nombre)) = UPPER(TRIM(PRO_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE producto 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdProducto = existing_id;
        
        SELECT existing_id as IdProducto, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO producto (Nombre, Detalle, Precio, Cantidad, Estado, FechaCreacion) 
        VALUES (PRO_NOM, PRO_DET, PRO_PRE, PRO_CANT, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdProducto, 'Registro creado' as Mensaje;
    END IF;
END$$
DELIMITER ;

-- 5. Actualizar producto existente (incluye Precio y Cantidad)
DELIMITER $$
CREATE PROCEDURE SP_U_PRODUCTO_01(IN PRO_ID INT, IN PRO_NOM VARCHAR(50), IN PRO_DET VARCHAR(100), IN PRO_PRE DECIMAL(10,2), IN PRO_CANT INT)
BEGIN
    UPDATE producto 
    SET Nombre = PRO_NOM, 
        Detalle = PRO_DET, 
        Precio = PRO_PRE, 
        Cantidad = PRO_CANT
    WHERE IdProducto = PRO_ID AND Estado = 1;
END$$
DELIMITER ;

--Cambiar estado del Producto (activar/desactivar)
DELIMITER $$
CREATE PROCEDURE SP_CAMBIAR_ESTADO_PRODUCTO_01(IN PRO_ID INT, IN NUEVO_ESTADO INT)
BEGIN
    UPDATE producto 
    SET Estado = NUEVO_ESTADO 
    WHERE IdProducto = PRO_ID;
END$$
DELIMITER ;
-- Listar todos (SP_L_PRODUCTO_01)
DROP PROCEDURE IF EXISTS SP_L_PRODUCTO_01 $$
CREATE PROCEDURE SP_L_PRODUCTO_01 ()
BEGIN
  SELECT 
    IdProducto AS PRO_ID,
    Nombre AS PRO_NOM,
    Detalle AS PRO_DET,
    Precio AS PRO_PRE,
    Cantidad AS PRO_CANT,
    Estado AS EST,
    FechaCreacion AS FEC_CREA
  FROM producto
  ORDER BY Nombre ASC;
END $$

-- Obtener por ID (SP_L_PRODUCTO_02)
DROP PROCEDURE IF EXISTS SP_L_PRODUCTO_02 $$
CREATE PROCEDURE SP_L_PRODUCTO_02 (IN p_IdProducto INT)
BEGIN
  SELECT 
    IdProducto AS PRO_ID,
    Nombre AS PRO_NOM,
    Detalle AS PRO_DET,
    Precio AS PRO_PRE,
    Cantidad AS PRO_CANT,
    Estado AS EST,
    FechaCreacion AS FEC_CREA
  FROM producto
  WHERE IdProducto = p_IdProducto;
END $$

-- Listar activos (SP_L_PRODUCTO_03)
DROP PROCEDURE IF EXISTS SP_L_PRODUCTO_03 $$
CREATE PROCEDURE SP_L_PRODUCTO_03 ()
BEGIN
  SELECT 
    IdProducto AS PRO_ID,
    Nombre AS PRO_NOM,
    Detalle AS PRO_DET,
    Precio AS PRO_PRE,
    Cantidad AS PRO_CANT,
    Estado AS EST,
    FechaCreacion AS FEC_CREA
  FROM producto
  WHERE Estado = 1
  ORDER BY Nombre ASC;
END $$

-- Insertar (SP_I_PRODUCTO_01)
DROP PROCEDURE IF EXISTS SP_I_PRODUCTO_01 $$
CREATE PROCEDURE SP_I_PRODUCTO_01 (
  IN p_Nombre VARCHAR(50),
  IN p_Detalle VARCHAR(100),
  IN p_Precio DECIMAL(10,2),
  IN p_Cantidad INT
)
BEGIN
  INSERT INTO producto (Nombre, Detalle, Precio, Cantidad, Estado)
  VALUES (p_Nombre, p_Detalle, p_Precio, p_Cantidad, 1);
  SELECT LAST_INSERT_ID() AS IdProducto;
END $$

-- Actualizar (SP_U_PRODUCTO_01)
DROP PROCEDURE IF EXISTS SP_U_PRODUCTO_01 $$
CREATE PROCEDURE SP_U_PRODUCTO_01 (
  IN p_IdProducto INT,
  IN p_Nombre VARCHAR(50),
  IN p_Detalle VARCHAR(100),
  IN p_Precio DECIMAL(10,2),
  IN p_Cantidad INT
)
BEGIN
  UPDATE producto
  SET Nombre = p_Nombre,
      Detalle = p_Detalle,
      Precio = p_Precio,
      Cantidad = p_Cantidad
  WHERE IdProducto = p_IdProducto;
  SELECT ROW_COUNT() AS affected;
END $$

-- Eliminar (SP_D_PRODUCTO_01)
DELIMITER $$
CREATE PROCEDURE SP_D_PRODUCTO_01(IN PRO_ID INT)
BEGIN
    DELETE FROM producto 
    WHERE IdProducto = PRO_ID;
END$$
DELIMITER ;

-- Cambiar estado (SP_CAMBIAR_ESTADO_PRODUCTO_01)
DROP PROCEDURE IF EXISTS SP_CAMBIAR_ESTADO_PRODUCTO_01 $$
CREATE PROCEDURE SP_CAMBIAR_ESTADO_PRODUCTO_01 (
  IN p_IdProducto INT,
  IN p_NuevoEstado TINYINT
)
BEGIN
  UPDATE producto
  SET Estado = p_NuevoEstado
  WHERE IdProducto = p_IdProducto;
  SELECT ROW_COUNT() AS affected;
END $$
