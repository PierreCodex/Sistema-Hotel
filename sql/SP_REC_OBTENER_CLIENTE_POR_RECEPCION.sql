DELIMITER //

DROP PROCEDURE IF EXISTS SP_REC_OBTENER_CLIENTE_POR_RECEPCION //

CREATE PROCEDURE SP_REC_OBTENER_CLIENTE_POR_RECEPCION(
    IN p_IdRecepcion INT
)
BEGIN
    SELECT 
        c.TipoDocumento, 
        c.Documento, 
        c.Nombre, 
        c.Apellido, 
        c.Direccion
    FROM recepcion r
    INNER JOIN cliente c ON r.IdCliente = c.IdCliente
    WHERE r.IdRecepcion = p_IdRecepcion;
END //

DELIMITER ;
