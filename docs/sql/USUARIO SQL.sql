-- =============================================
-- STORED PROCEDURES PARA TABLA USUARIO
-- Estructura: IdUsuario, Nombre, Apellido, DNI, Correo, Pass, Estado, FechaCreacion, IdRol
-- =============================================
-- 1. Listar todos los usuarios (activos e inactivos) excluyendo al usuario logueado
DELIMITER $$
CREATE PROCEDURE SP_L_USUARIO_03(IN CURRENT_USER_ID INT)
BEGIN
    SELECT 
        u.IdUsuario AS USU_ID,
        u.Nombre AS USU_NOM,
        u.Apellido AS USU_APE,
        u.DNI AS USU_DNI,
        u.Correo AS USU_CORREO,
        u.Estado AS EST,
        DATE_FORMAT(u.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        u.IdRol AS ROL_ID,
        COALESCE(r.Descripcion, 'Sin Rol') AS ROL_NOM,
        CASE 
            WHEN u.Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_TEXTO
    FROM usuario u
    LEFT JOIN rol r ON u.IdRol = r.IdRol
    WHERE u.IdUsuario != CURRENT_USER_ID
    ORDER BY u.FechaCreacion DESC;
END$$
DELIMITER ;


-- 2. Obtener usuario por ID específico
DELIMITER $$
CREATE PROCEDURE SP_L_USUARIO_02(IN USU_ID INT)
BEGIN
    SELECT 
        u.IdUsuario AS USU_ID,
        u.Nombre AS USU_NOM,
        u.Apellido AS USU_APE,
        u.DNI AS USU_DNI,
        u.Correo AS USU_CORREO,
        u.Pass AS USU_PASS,
        u.Estado AS EST,
        u.FechaCreacion AS FECH_CREA,
        u.IdRol AS ROL_ID,
        COALESCE(r.Descripcion, 'Sin Rol') AS ROL_NOM
    FROM usuario u
    LEFT JOIN rol r ON u.IdRol = r.IdRol
    WHERE u.IdUsuario = USU_ID;
END$$
DELIMITER ;

-- 3. Eliminar usuario definitivamente
DELIMITER $$
CREATE PROCEDURE SP_D_USUARIO_01(IN USU_ID INT)
BEGIN
    DELETE FROM usuario 
    WHERE IdUsuario = USU_ID;
END$$
DELIMITER ;

-- 4. Insertar nuevo usuario
DELIMITER $$
CREATE PROCEDURE SP_I_USUARIO_01(
    IN USU_NOM VARCHAR(50),
    IN USU_APE VARCHAR(50),
    IN USU_DNI VARCHAR(8),
    IN USU_CORREO VARCHAR(100),
    IN USU_PASS VARCHAR(255),
    IN ROL_ID INT
)
BEGIN
    INSERT INTO usuario (Nombre, Apellido, DNI, Correo, Pass, Estado, FechaCreacion, IdRol) 
    VALUES (USU_NOM, USU_APE, USU_DNI, USU_CORREO, USU_PASS, 1, NOW(), ROL_ID);
END$$
DELIMITER ;

-- 5.  Actualizar usuario existente
DELIMITER $$
CREATE PROCEDURE SP_U_USUARIO_01(
    IN USU_ID INT,
    IN USU_NOM VARCHAR(50),
    IN USU_APE VARCHAR(50),
    IN USU_DNI VARCHAR(15),
    IN USU_CORREO VARCHAR(100),
    IN USU_PASS VARCHAR(255),
    IN ROL_ID INT
)
BEGIN
    UPDATE usuario 
    SET Nombre = USU_NOM,
        Apellido = USU_APE,
        DNI = USU_DNI,
        Correo = USU_CORREO,
        Pass = CASE 
            WHEN USU_PASS IS NOT NULL AND USU_PASS != '' THEN USU_PASS 
            ELSE Pass 
        END,
        IdRol = ROL_ID
    WHERE IdUsuario = USU_ID;
END$$
DELIMITER ;


-- 6. Actualizar contraseña de usuario
DELIMITER $$
CREATE PROCEDURE SP_U_USUARIO_PASS_01(
    IN USU_ID INT,
    IN USU_PASS VARCHAR(255)
)
BEGIN
    UPDATE usuario 
    SET Pass = USU_PASS
    WHERE IdUsuario = USU_ID;
END$$
DELIMITER ;
-- 7. Cambiar estado del usuario (activar/desactivar)
DELIMITER $$
CREATE PROCEDURE SP_CAMBIAR_ESTADO_USUARIO_01(IN USU_ID INT, IN NUEVO_ESTADO INT)
BEGIN
    UPDATE usuario 
    SET Estado = NUEVO_ESTADO 
    WHERE IdUsuario = USU_ID;
END$$
DELIMITER ;
-- SIN USAR. Obtener usuario por correo (para login)
DELIMITER $$
CREATE PROCEDURE SP_L_USUARIO_LOGIN_01(IN USU_CORREO VARCHAR(100))
BEGIN
    SELECT 
        u.IdUsuario AS USU_ID,
        u.Nombre AS USU_NOM,
        u.Apellido AS USU_APE,
        u.DNI AS USU_DNI,
        u.Correo AS USU_CORREO,
        u.Pass AS USU_PASS,
        u.Estado AS EST,
        u.IdRol AS ROL_ID,
        COALESCE(r.Descripcion, 'Sin Rol') AS ROL_NOM
    FROM usuario u
    LEFT JOIN rol r ON u.IdRol = r.IdRol
    WHERE u.Correo = USU_CORREO AND u.Estado = 1;
END$$
DELIMITER ;

-- 8. Obtener IdUsuario por DNI (solo usuarios activos)
DELIMITER $$
CREATE PROCEDURE SP_L_USUARIO_BY_DNI_01(IN USU_DNI VARCHAR(15))
BEGIN
    SELECT IdUsuario 
    FROM usuario 
    WHERE DNI = USU_DNI AND Estado = 1;
END$$
DELIMITER ;


