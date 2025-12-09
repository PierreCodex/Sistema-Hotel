DELIMITER //
CREATE PROCEDURE sp_verificar_boleta_existente(IN p_rec_id INT)
BEGIN
    SELECT bol_id, bol_serie, bol_correlativo, bol_estado 
    FROM boleta 
    WHERE rec_id = p_rec_id AND bol_estado = 'ACEPTADA'
    ORDER BY bol_id DESC LIMIT 1;
END //
DELIMITER ;
