-- =============================================
-- STORED PROCEDURES PARA TABLA CLIENTE
-- Estructura: IdCliente, TipoDocumento, Documento, Nombre, Apellido, Direccion, Estado, 
--             IdUsuarioCreacion, FechaCreacion, IdUsuarioModificacion, FechaModificacion
-- =============================================

USE `db-hotel`;

-- Eliminar procedimientos existentes si existen
DROP PROCEDURE IF EXISTS SP_L_CLIENTE_01;
DROP PROCEDURE IF EXISTS SP_L_CLIENTE_02;
DROP PROCEDURE IF EXISTS SP_L_CLIENTE_03;
DROP PROCEDURE IF EXISTS SP_I_CLIENTE_01;
DROP PROCEDURE IF EXISTS SP_U_CLIENTE_01;
DROP PROCEDURE IF EXISTS SP_D_CLIENTE_01;
DROP PROCEDURE IF EXISTS SP_CAMBIAR_ESTADO_CLIENTE_01;

-- =============================================
-- 1. Listar todos los clientes (activos e inactivos)
-- =============================================
DELIMITER $$
CREATE PROCEDURE SP_L_CLIENTE_01()
BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        IdUsuarioCreacion,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        IdUsuarioModificacion,
        DATE_FORMAT(FechaModificacion, '%d/%m/%Y %H:%i:%s') AS FECH_MOD
    FROM cliente 
    ORDER BY FechaCreacion DESC;
END$$
DELIMITER ;

-- =============================================
-- 2. Obtener cliente por ID específico
-- =============================================
DELIMITER $$
CREATE PROCEDURE SP_L_CLIENTE_02(IN CLI_ID INT)
BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        IdUsuarioCreacion,
        FechaCreacion AS FECH_CREA,
        IdUsuarioModificacion,
        FechaModificacion AS FECH_MOD
    FROM cliente 
    WHERE IdCliente = CLI_ID;
END$$
DELIMITER ;

-- =============================================
-- 3. Listar solo clientes activos (para combos)
-- =============================================
DELIMITER $$
CREATE PROCEDURE SP_L_CLIENTE_03()
BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM cliente 
    WHERE Estado = 1
    ORDER BY Nombre ASC, Apellido ASC;
END$$
DELIMITER ;

-- =============================================
-- 4. Insertar nuevo cliente (con auditoría)
-- =============================================
DELIMITER $$
CREATE PROCEDURE SP_I_CLIENTE_01(
    IN CLI_TIPO_DOC VARCHAR(10),
    IN CLI_DOC VARCHAR(20),
    IN CLI_NOM VARCHAR(100),
    IN CLI_APE VARCHAR(100),
    IN CLI_DIR VARCHAR(255),
    IN USU_ID INT
)
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un cliente con el mismo documento
    SELECT IdCliente INTO existing_id 
    FROM cliente 
    WHERE Documento = CLI_DOC
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar datos
        UPDATE cliente 
        SET TipoDocumento = CLI_TIPO_DOC,
            Nombre = CLI_NOM,
            Apellido = CLI_APE,
            Direccion = CLI_DIR,
            Estado = 1,
            IdUsuarioModificacion = USU_ID,
            FechaModificacion = NOW()
        WHERE IdCliente = existing_id;
        
        SELECT existing_id as IdCliente, 'Cliente reactivado y actualizado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO cliente (
            TipoDocumento, 
            Documento, 
            Nombre, 
            Apellido, 
            Direccion, 
            Estado, 
            IdUsuarioCreacion, 
            FechaCreacion
        ) 
        VALUES (
            CLI_TIPO_DOC, 
            CLI_DOC, 
            CLI_NOM, 
            CLI_APE, 
            CLI_DIR, 
            1, 
            USU_ID, 
            NOW()
        );
        
        SELECT LAST_INSERT_ID() as IdCliente, 'Cliente creado exitosamente' as Mensaje;
    END IF;
END$$
DELIMITER ;

-- =============================================
-- 5. Actualizar cliente existente (con auditoría)
-- =============================================
DELIMITER $$
CREATE PROCEDURE SP_U_CLIENTE_01(
    IN CLI_ID INT,
    IN CLI_TIPO_DOC VARCHAR(10),
    IN CLI_DOC VARCHAR(20),
    IN CLI_NOM VARCHAR(100),
    IN CLI_APE VARCHAR(100),
    IN CLI_DIR VARCHAR(255),
    IN USU_ID INT
)
BEGIN
    UPDATE cliente 
    SET TipoDocumento = CLI_TIPO_DOC,
        Documento = CLI_DOC,
        Nombre = CLI_NOM,
        Apellido = CLI_APE,
        Direccion = CLI_DIR,
        IdUsuarioModificacion = USU_ID,
        FechaModificacion = NOW()
    WHERE IdCliente = CLI_ID;
    
    SELECT ROW_COUNT() as FilasAfectadas, 'Cliente actualizado exitosamente' as Mensaje;
END$$
DELIMITER ;

-- =============================================
-- 6. Eliminar cliente (físicamente)
-- =============================================
DELIMITER $$
CREATE PROCEDURE SP_D_CLIENTE_01(IN CLI_ID INT)
BEGIN
    DELETE FROM cliente 
    WHERE IdCliente = CLI_ID;
    
    SELECT ROW_COUNT() as FilasAfectadas, 'Cliente eliminado' as Mensaje;
END$$
DELIMITER ;

-- =============================================
-- 7. Cambiar estado del cliente (activar/desactivar) con auditoría
-- =============================================
DELIMITER $$
CREATE PROCEDURE SP_CAMBIAR_ESTADO_CLIENTE_01(
    IN CLI_ID INT, 
    IN NUEVO_ESTADO INT,
    IN USU_ID INT
)
BEGIN
    UPDATE cliente 
    SET Estado = NUEVO_ESTADO,
        IdUsuarioModificacion = USU_ID,
        FechaModificacion = NOW()
    WHERE IdCliente = CLI_ID;
    
    SELECT ROW_COUNT() as FilasAfectadas, 
           CASE WHEN NUEVO_ESTADO = 1 THEN 'Cliente activado' ELSE 'Cliente desactivado' END as Mensaje;
END$$
DELIMITER ;

-- =============================================
-- Verificar que los procedimientos se crearon correctamente
-- =============================================
SHOW PROCEDURE STATUS WHERE Db = 'db-hotel' AND Name LIKE 'SP_%CLIENTE%';

SELECT 'Stored Procedures de Cliente creados exitosamente' AS Resultado;