-- Stored Procedures para la tabla CLIENTE

-- Listar todos los clientes activos
DELIMITER //
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
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        CASE 
            WHEN Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_TEXTO
    FROM cliente 
    WHERE Estado = 1
    ORDER BY IdCliente DESC;
END //
DELIMITER ;

-- Listar todos los clientes (incluyendo inactivos)
DELIMITER //
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
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        CASE 
            WHEN Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_TEXTO
    FROM cliente 
    ORDER BY IdCliente DESC;
END //
DELIMITER ;

-- Listar cliente por ID
DELIMITER //
CREATE PROCEDURE SP_L_CLIENTE_02(IN p_id INT)
BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM cliente 
    WHERE IdCliente = p_id;
END //
DELIMITER ;

-- Eliminar cliente (cambiar estado a 0)
DELIMITER //
CREATE PROCEDURE SP_D_CLIENTE_01(IN p_id INT)
BEGIN
    UPDATE cliente 
    SET Estado = 0 
    WHERE IdCliente = p_id;
END //
DELIMITER ;

-- Insertar nuevo cliente
DELIMITER //
CREATE PROCEDURE SP_I_CLIENTE_01(
    IN p_tipo_documento VARCHAR(15),
    IN p_documento VARCHAR(15),
    IN p_nombre VARCHAR(50),
    IN p_apellido VARCHAR(50),
    IN p_direccion VARCHAR(250),
    OUT p_id_cliente INT
)
BEGIN
    INSERT INTO cliente (TipoDocumento, Documento, Nombre, Apellido, Direccion, Estado, FechaCreacion)
    VALUES (p_tipo_documento, p_documento, p_nombre, p_apellido, p_direccion, 1, NOW());
    
    SET p_id_cliente = LAST_INSERT_ID();
END //
DELIMITER ;

-- Actualizar cliente
DELIMITER //
CREATE PROCEDURE SP_U_CLIENTE_01(
    IN p_id INT,
    IN p_tipo_documento VARCHAR(15),
    IN p_documento VARCHAR(15),
    IN p_nombre VARCHAR(50),
    IN p_apellido VARCHAR(50),
    IN p_direccion VARCHAR(250)
)
BEGIN
    UPDATE cliente 
    SET 
        TipoDocumento = p_tipo_documento,
        Documento = p_documento,
        Nombre = p_nombre,
        Apellido = p_apellido,
        Direccion = p_direccion
    WHERE IdCliente = p_id;
END //
DELIMITER ;

-- Cambiar estado del cliente
DELIMITER //
CREATE PROCEDURE SP_CAMBIAR_ESTADO_CLIENTE_01(IN p_id INT, IN p_nuevo_estado INT)
BEGIN
    UPDATE cliente 
    SET Estado = p_nuevo_estado 
    WHERE IdCliente = p_id;
END //
DELIMITER ;

-- Buscar cliente por documento
DELIMITER //
CREATE PROCEDURE SP_L_CLIENTE_BY_DOCUMENTO_01(IN p_documento VARCHAR(15))
BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        CASE 
            WHEN Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_TEXTO
    FROM cliente 
    WHERE Documento = p_documento AND Estado = 1;
END //
DELIMITER ;

-- Buscar clientes por nombre o apellido
DELIMITER //
CREATE PROCEDURE SP_L_CLIENTE_BY_NOMBRE_01(IN p_nombre VARCHAR(100))
BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        CASE 
            WHEN Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_TEXTO
    FROM cliente 
    WHERE (Nombre LIKE CONCAT('%', p_nombre, '%') OR Apellido LIKE CONCAT('%', p_nombre, '%')) 
    AND Estado = 1
    ORDER BY Nombre, Apellido;
END //
DELIMITER ;