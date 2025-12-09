DELIMITER //

DROP PROCEDURE IF EXISTS SP_REC_OBTENER_DETALLES_FACTURA //

CREATE PROCEDURE SP_REC_OBTENER_DETALLES_FACTURA(
    IN p_IdRecepcion INT
)
BEGIN
    SELECT 
        r.*, 
        h.Numero as NumeroHabitacion, 
        t.Descripcion as TarifaDescripcion
    FROM recepcion r
    LEFT JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
    LEFT JOIN tarifa t ON r.IdTarifa = t.IdTarifa
    WHERE r.IdRecepcion = p_IdRecepcion;
END //

DELIMITER ;
