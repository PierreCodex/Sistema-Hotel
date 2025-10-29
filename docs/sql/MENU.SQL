-- Stored Procedures para MENU - Versión MySQL

DELIMITER $$

-- Procedimiento para listar menús por rol
DROP PROCEDURE IF EXISTS SP_L_MENU_01$$
CREATE PROCEDURE SP_L_MENU_01(
    IN IdRol INT
)
BEGIN
    SELECT        
        TD_MENU.MEND_ID, 
        TD_MENU.MEN_ID, 
        TD_MENU.IdRol, 
        TD_MENU.MEND_PERMI, 
        TD_MENU.FECH_CREA, 
        TD_MENU.EST, 
        TM_MENU.MEN_NOM, 
        TM_MENU.MEN_RUTA, 
        TM_MENU.MEN_IDENTI,
        TM_MENU.MEN_GRUPO,
        TM_MENU.MEN_ORDEN
    FROM            
        TD_MENU 
    INNER JOIN TM_MENU ON TD_MENU.MEN_ID = TM_MENU.MEN_ID
    WHERE 
        TD_MENU.IdRol = IdRol;
END$$

-- 3 Procedimiento para activar permiso de menú

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_MENU_01` (IN `MEND_ID` INT)   BEGIN
    UPDATE TD_MENU
    SET MEND_PERMI = 'Si'
    WHERE TD_MENU.MEND_ID = MEND_ID;
END$$

-- 4 Procedimiento para desactivar permiso de menú
DELIMITER $$

CREATE PROCEDURE SP_U_MENU_02(
    IN MEND_ID INT
)
BEGIN
    UPDATE TD_MENU
    SET MEND_PERMI = 'No'
    WHERE TD_MENU.MEND_ID = MEND_ID;
END$$

DELIMITER ;


-- Procedimiento para obtener menú específico por usuario y identificador
DROP PROCEDURE IF EXISTS SP_L_MENU_03$$
CREATE PROCEDURE SP_L_MENU_03(
    IN USU_ID INT,
    IN MEN_IDENTI VARCHAR(100)
)
BEGIN
    SELECT        
        TD_MENU.MEND_ID, 
        TD_MENU.MEN_ID, 
        TD_MENU.IdRol, 
        TD_MENU.MEND_PERMI, 
        TD_MENU.FECH_CREA, 
        TD_MENU.EST, 
        TM_MENU.MEN_NOM, 
        TM_MENU.MEN_RUTA, 
        TM_MENU.MEN_IDENTI, 
        TM_MENU.MEN_GRUPO,
        TM_MENU.MEN_ORDEN,
        TM_USUARIO.USU_ID, 
        TM_ROL.Descripcion AS ROL_NOM 
    FROM            
        TD_MENU 
    INNER JOIN TM_MENU ON TD_MENU.MEN_ID = TM_MENU.MEN_ID 
    INNER JOIN TM_ROL ON TD_MENU.IdRol = TM_ROL.IdRol 
    INNER JOIN TM_USUARIO ON TM_ROL.IdRol = TM_USUARIO.IdRol 
    WHERE   
        TM_USUARIO.USU_ID = USU_ID  
        AND TD_MENU.MEND_PERMI = 'Si' 
        AND TM_MENU.MEN_IDENTI = MEN_IDENTI;
END$$

DELIMITER ;

-- 2: registrar automáticamente los permisos de menú para un rol específico
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_MENU_02`(
    p_IdRol INT
)
BEGIN
    IF (SELECT COUNT(*) FROM TD_MENU WHERE IdRol = p_IdRol) = 0
    THEN
        -- Insertar todos los menús para el rol si no tiene ninguno
        INSERT INTO TD_MENU
        (MEN_ID, IdRol, MEND_PERMI, FECH_CREA, EST)
        (SELECT MEN_ID, p_IdRol, 'No', NOW(3), 1 FROM TM_MENU WHERE EST = 1);
    ELSE
        -- Insertar solo los menús que faltan para el rol
        INSERT INTO TD_MENU
        (MEN_ID, IdRol, MEND_PERMI, FECH_CREA, EST)
        (SELECT MEN_ID, p_IdRol, 'No', NOW(3), 1 
         FROM TM_MENU 
         WHERE EST = 1 
         AND MEN_ID NOT IN (SELECT MEN_ID FROM TD_MENU WHERE IdRol = p_IdRol));
    END IF;
END ;;
DELIMITER ;