-- =============================================
-- STORED PROCEDURES PARA TABLA HABITACION
-- Estructura: IdHabitacion, Numero, Detalle, Precio, IdEstadoHabitacion, IdPiso, IdCategoria, Estado, FechaCreacion
-- =============================================

-- Eliminar procedimientos existentes si existen

-- 1. Listar todas las habitaciones con información completa (MEJORADO)
DELIMITER $$
CREATE PROCEDURE SP_L_HABITACION_01()
BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        DATE_FORMAT(h.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        -- Información adicional con JOINs
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    ORDER BY h.FechaCreacion DESC;
END$$
DELIMITER ;

-- 2. Listar todas las habitaciones activas con información completa (MEJORADO)
DELIMITER $$
CREATE PROCEDURE SP_L_HABITACION_03()
BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        DATE_FORMAT(h.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        -- Información adicional con JOINs
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    WHERE h.Estado = 1
    ORDER BY h.FechaCreacion DESC;
END$$
DELIMITER ;

-- Listar solo las habitaciones con estado OCUPADO
DELIMITER $$
CREATE PROCEDURE SP_L_HABITACION_OCUPADO()
BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        DATE_FORMAT(h.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    WHERE h.Estado = 1
      AND UPPER(eh.Descripcion) LIKE '%OCUPADO%'
    ORDER BY h.FechaCreacion DESC;
END$$
DELIMITER ;

-- 3. Obtener habitación por ID con información completa (MEJORADO)
DELIMITER $$
CREATE PROCEDURE SP_L_HABITACION_02(IN HAB_ID INT)
BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        h.FechaCreacion AS FECH_CREA,
        -- Información adicional con JOINs
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    WHERE h.IdHabitacion = HAB_ID;
END$$
DELIMITER ;

-- Stored Procedure para insertar habitación (con estado automático disponible)
DELIMITER $$
CREATE PROCEDURE SP_I_HABITACION_01(
    IN HAB_NUM VARCHAR(50),
    IN HAB_DET VARCHAR(100),
    IN HAB_EST_ID INT,
    IN HAB_PISO_ID INT,
    IN HAB_CAT_ID INT
)
BEGIN
    DECLARE existing_id INT DEFAULT 0;
    DECLARE estado_disponible INT DEFAULT 1;
    
    -- Si no se proporciona estado o es 0, usar el estado "Disponible" dinámicamente
    IF HAB_EST_ID IS NULL OR HAB_EST_ID = 0 THEN
        -- Obtener dinámicamente el ID del estado "Disponible"
        SELECT IdEstadoHabitacion INTO estado_disponible
        FROM estado_habitacion 
        WHERE Estado = 1 
        AND UPPER(Descripcion) LIKE '%DISPONIBLE%'
        ORDER BY IdEstadoHabitacion ASC
        LIMIT 1;
        
        SET HAB_EST_ID = IFNULL(estado_disponible, 1);
    END IF;
    
    -- Verificar si existe un registro con el mismo número (activo o inactivo)
    SELECT IdHabitacion INTO existing_id 
    FROM habitacion 
    WHERE UPPER(TRIM(Numero)) = UPPER(TRIM(HAB_NUM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar datos
        UPDATE habitacion 
        SET Estado = 1, 
            Detalle = HAB_DET,
            IdEstadoHabitacion = HAB_EST_ID,
            IdPiso = HAB_PISO_ID,
            IdCategoria = HAB_CAT_ID,
            FechaCreacion = NOW() 
        WHERE IdHabitacion = existing_id;
        
        SELECT existing_id as IdHabitacion, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO habitacion (Numero, Detalle,IdEstadoHabitacion, IdPiso, IdCategoria, Estado, FechaCreacion) 
        VALUES (HAB_NUM, HAB_DET, HAB_EST_ID, HAB_PISO_ID, HAB_CAT_ID, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdHabitacion, 'Registro creado' as Mensaje;
    END IF;
END$$
DELIMITER ;
-- 3. Eliminar habitación (cambio de estado)
DELIMITER $$
CREATE PROCEDURE SP_D_HABITACION_01(IN HAB_ID INT)
BEGIN
    DELETE FROM habitacion 
    WHERE IdHabitacion = HAB_ID;
END$$
DELIMITER ;


-- 5. Actualizar habitación existente
DELIMITER $$
CREATE PROCEDURE SP_U_HABITACION_01(
    IN HAB_ID INT,
    IN HAB_NUM VARCHAR(50),
    IN HAB_DET VARCHAR(100),
    IN HAB_EST_ID INT,
    IN HAB_PISO_ID INT,
    IN HAB_CAT_ID INT
)
BEGIN
    UPDATE habitacion 
    SET Numero = HAB_NUM,
        Detalle = HAB_DET,
        IdEstadoHabitacion = HAB_EST_ID,
        IdPiso = HAB_PISO_ID,
        IdCategoria = HAB_CAT_ID
    WHERE IdHabitacion = HAB_ID AND Estado = 1;
END$$
DELIMITER ;

-- Verificar que los procedimientos se crearon correctamente
SHOW PROCEDURE STATUS WHERE Name LIKE 'SP_%HABITACION%';

--Cambiar estado del Habitación (activar/desactivar)
DELIMITER $$
CREATE PROCEDURE SP_CAMBIAR_ESTADO_HABITACION_01(IN HAB_ID INT, IN NUEVO_ESTADO INT)
BEGIN
    UPDATE habitacion 
    SET Estado = NUEVO_ESTADO 
    WHERE IdHabitacion = HAB_ID;
END$$
DELIMITER ;

-- =============================================
-- PROCEDIMIENTOS ADICIONALES PARA INTEGRIDAD DE ESTADOS
-- =============================================

-- Procedimiento para verificar que existe al menos un estado "Disponible"
DELIMITER $$
CREATE PROCEDURE VerificarEstadoDisponibleHabitacion()
BEGIN
    DECLARE estado_disponible INT DEFAULT 0;
    
    -- Buscar estado "Disponible" activo
    SELECT IdEstadoHabitacion INTO estado_disponible
    FROM estado_habitacion 
    WHERE Estado = 1 
    AND UPPER(Descripcion) LIKE '%DISPONIBLE%'
    LIMIT 1;
    
    SELECT 
        IFNULL(estado_disponible, 0) as IdEstadoDisponible,
        CASE 
            WHEN estado_disponible > 0 THEN 'OK - Estado Disponible encontrado'
            ELSE 'ADVERTENCIA - No se encontró estado Disponible activo'
        END as Mensaje;
END$$
DELIMITER ;


-- Cambiar tipo de estado de la habitación (OCUPADO, LIMPIEZA, DISPONIBLE)
DELIMITER $$
CREATE PROCEDURE SP_CAMBIAR_TIPO_ESTADO_HABITACION_01(
    IN HAB_ID INT,
    IN ID_ESTADO_HABITACION INT
)
BEGIN
    UPDATE habitacion 
    SET IdEstadoHabitacion = ID_ESTADO_HABITACION
    WHERE IdHabitacion = HAB_ID AND Estado = 1;
    
    -- Retornar el resultado de la actualización
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        eh.Descripcion AS ESTADO_NOM,
        'Tipo de estado actualizado' AS Mensaje
    FROM habitacion h
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion
    WHERE h.IdHabitacion = HAB_ID;
END$$
DELIMITER ;

