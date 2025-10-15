-- =============================================
-- STORED PROCEDURES PARA TABLA USUARIO
-- Estructura: IdUsuario, Nombre, Apellido, DNI, Correo, Pass, Estado, FechaCreacion, IdRol
-- =============================================

-- 1. Listar todos los usuarios activos excluyendo al usuario logueado
DELIMITER $$
CREATE PROCEDURE SP_L_USUARIO_01(IN CURRENT_USER_ID INT)
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
        COALESCE(r.Descripcion, 'Sin Rol') AS ROL_NOM
    FROM usuario u
    LEFT JOIN rol r ON u.IdRol = r.IdRol
    WHERE u.Estado = 1 AND u.IdUsuario != CURRENT_USER_ID
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

-- 3. Eliminar usuario (cambio de estado)
DELIMITER $$
CREATE PROCEDURE SP_D_USUARIO_01(IN USU_ID INT)
BEGIN
    UPDATE usuario 
    SET Estado = 0 
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

-- 5. Actualizar usuario existente
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

-- 6. Listar todos los usuarios (activos e inactivos) excluyendo al usuario logueado
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

-- 7. Reactivar usuario
DELIMITER $$
CREATE PROCEDURE SP_A_USUARIO_01(IN USU_ID INT)
BEGIN
    UPDATE usuario 
    SET Estado = 1 
    WHERE IdUsuario = USU_ID;
END$$
DELIMITER ;

-- 8. Buscar usuarios por nombre, apellido o correo
DELIMITER $$
CREATE PROCEDURE SP_S_USUARIO_01(IN BUSCAR VARCHAR(100))
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
        COALESCE(r.Descripcion, 'Sin Rol') AS ROL_NOM
    FROM usuario u
    LEFT JOIN rol r ON u.IdRol = r.IdRol
    WHERE (u.Nombre LIKE CONCAT('%', BUSCAR, '%') 
           OR u.Apellido LIKE CONCAT('%', BUSCAR, '%')
           OR u.Correo LIKE CONCAT('%', BUSCAR, '%'))
    AND u.Estado = 1
    ORDER BY u.FechaCreacion DESC;
END$$
DELIMITER ;

-- 9. Procedimiento de inserción modificado con reactivación
DELIMITER $$
CREATE PROCEDURE SP_I_USUARIO_02(
    IN USU_NOM VARCHAR(50),
    IN USU_APE VARCHAR(50),
    IN USU_DNI VARCHAR(15),
    IN USU_CORREO VARCHAR(100),
    IN USU_PASS VARCHAR(255),
    IN ROL_ID INT
)
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un usuario con el mismo DNI o correo (activo o inactivo)
    SELECT IdUsuario INTO existing_id 
    FROM usuario 
    WHERE DNI = USU_DNI OR Correo = USU_CORREO
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar datos
        UPDATE usuario 
        SET Nombre = USU_NOM,
            Apellido = USU_APE,
            DNI = USU_DNI,
            Correo = USU_CORREO,
            Pass = USU_PASS,
            IdRol = ROL_ID,
            Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdUsuario = existing_id;
        
        SELECT existing_id as IdUsuario, 'Usuario reactivado y actualizado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo usuario
        INSERT INTO usuario (Nombre, Apellido, DNI, Correo, Pass, Estado, FechaCreacion, IdRol) 
        VALUES (USU_NOM, USU_APE, USU_DNI, USU_CORREO, USU_PASS, 1, NOW(), ROL_ID);
        
        SELECT LAST_INSERT_ID() as IdUsuario, 'Usuario creado exitosamente' as Mensaje;
    END IF;
END$$
DELIMITER ;

-- 10. Actualizar contraseña de usuario
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

-- 11. Obtener usuario por correo (para login)
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

-- 12. Combo box de usuarios activos
DELIMITER $$
CREATE PROCEDURE SP_L_USUARIO_COMBO_01()
BEGIN
    SELECT 
        IdUsuario AS USU_ID,
        CONCAT(Nombre, ' ', Apellido) AS USU_NOMBRE_COMPLETO
    FROM usuario 
    WHERE Estado = 1
    ORDER BY Nombre, Apellido;
END$$
DELIMITER ;

-- 13. Actualizar usuario sin modificar contraseña (para evitar inyección SQL)
DELIMITER $$
CREATE PROCEDURE SP_U_USUARIO_SIN_PASS_01(
    IN USU_ID INT,
    IN USU_NOM VARCHAR(50),
    IN USU_APE VARCHAR(50),
    IN USU_DNI VARCHAR(8),
    IN USU_CORREO VARCHAR(100),
    IN ROL_ID INT
)
BEGIN
    UPDATE usuario 
    SET Nombre = USU_NOM,
        Apellido = USU_APE,
        DNI = USU_DNI,
        Correo = USU_CORREO,
        IdRol = ROL_ID
    WHERE IdUsuario = USU_ID;
END$$
DELIMITER ;