-- =============================================
-- STORED PROCEDURES PARA TARIFAS Y ASIGNACIONES A HABITACIONES
-- Tablas: tarifa(IdTarifa, Descripcion, Precio, Estado, FechaCreacion)
--         habitacion_tarifa(id_habitacion_tarifa, id_habitacion, id_tarifa, fecha_inicio, fecha_fin)
-- =============================================

-- 1) Listar tarifas activas
DELIMITER $$
CREATE PROCEDURE SP_L_TARIFA_01()
BEGIN
    SELECT IdTarifa, Descripcion, Precio
    FROM tarifa
    WHERE Estado = 1
    ORDER BY Descripcion;
END$$
DELIMITER ;

-- 2) Listar tarifas asignadas a una habitación
DELIMITER $$
CREATE PROCEDURE SP_L_TARIFA_X_HABITACION_01(IN HAB_ID INT)
BEGIN
    SELECT 
        ht.id_habitacion_tarifa,
        ht.id_habitacion,
        ht.id_tarifa,
        ht.fecha_inicio,
        ht.fecha_fin,
        t.Descripcion,
        t.Precio
    FROM habitacion_tarifa ht
    INNER JOIN tarifa t ON t.IdTarifa = ht.id_tarifa
    WHERE ht.id_habitacion = HAB_ID
    ORDER BY ht.fecha_inicio DESC;
END$$
DELIMITER ;

-- 3) Asignar tarifa a habitación (con validación básica de solapamiento)
DELIMITER $$
CREATE PROCEDURE SP_I_HABITACION_TARIFA_01(
    IN HAB_ID INT,
    IN TARIFA_ID INT,
    IN FECHA_INICIO DATETIME,
    IN FECHA_FIN DATETIME
)
BEGIN
    DECLARE cnt INT DEFAULT 0;

    -- Validación sencilla: comprobar si existe asignación activa de la misma tarifa
    SELECT COUNT(1) INTO cnt
    FROM habitacion_tarifa
    WHERE id_habitacion = HAB_ID
      AND id_tarifa = TARIFA_ID
      AND (
           (fecha_fin IS NULL AND FECHA_INICIO >= fecha_inicio)
           OR
           (fecha_fin IS NOT NULL AND FECHA_INICIO BETWEEN fecha_inicio AND fecha_fin)
      );

    IF cnt > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya existe una asignación activa de esta tarifa en el periodo indicado';
    ELSE
        INSERT INTO habitacion_tarifa (id_habitacion, id_tarifa, fecha_inicio, fecha_fin)
        VALUES (HAB_ID, TARIFA_ID, FECHA_INICIO, FECHA_FIN);
        SELECT LAST_INSERT_ID() AS id_insertado;
    END IF;
END$$
DELIMITER ;

-- 4) Actualizar vigencia de asignación
DELIMITER $$
CREATE PROCEDURE SP_U_HABITACION_TARIFA_01(
    IN HAB_TAR_ID INT,
    IN FECHA_INICIO DATETIME,
    IN FECHA_FIN DATETIME
)
BEGIN
    UPDATE habitacion_tarifa
    SET fecha_inicio = FECHA_INICIO,
        fecha_fin = FECHA_FIN
    WHERE id_habitacion_tarifa = HAB_TAR_ID;
END$$
DELIMITER ;

-- 5) Eliminar asignación de tarifa
DELIMITER $$
CREATE PROCEDURE SP_D_HABITACION_TARIFA_01(IN HAB_TAR_ID INT)
BEGIN
    DELETE FROM habitacion_tarifa
    WHERE id_habitacion_tarifa = HAB_TAR_ID;
END$$
DELIMITER ;

-- Comprobar creación
SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name LIKE 'SP\_%TARIFA%';

-- =============================================
-- CRUD de catálogo de tarifas
-- =============================================

-- Listar catálogo completo de tarifas
DELIMITER $$
CREATE PROCEDURE SP_L_TARIFA_CATALOGO_01()
BEGIN
    SELECT IdTarifa, Descripcion, Precio, Estado
    FROM tarifa
    ORDER BY Descripcion;
END$$
DELIMITER ;

-- Insertar tarifa
DELIMITER $$
CREATE PROCEDURE SP_I_TARIFA_01(
    IN TAR_DESC VARCHAR(100),
    IN TAR_PRECIO DECIMAL(10,2)
)
BEGIN
    INSERT INTO tarifa (Descripcion, Precio, Estado)
    VALUES (TAR_DESC, TAR_PRECIO, 1);
    SELECT LAST_INSERT_ID() AS id_insertado;
END$$
DELIMITER ;

-- Actualizar tarifa
DELIMITER $$
CREATE PROCEDURE SP_U_TARIFA_01(
    IN TAR_ID INT,
    IN DESCR VARCHAR(100),
    IN PREC DECIMAL(10,2)
)
BEGIN
    UPDATE tarifa
    SET Descripcion = DESCR,
        Precio = PREC
    WHERE IdTarifa = TAR_ID;
END$$
DELIMITER ;

-- Cambiar estado de tarifa
DELIMITER $$
CREATE PROCEDURE SP_U_TARIFA_CAMBIAR_ESTADO_01(
    IN TAR_ID INT,
    IN NUEVO_ESTADO TINYINT
)
BEGIN
    UPDATE tarifa
    SET Estado = NUEVO_ESTADO
    WHERE IdTarifa = TAR_ID;
END$$
DELIMITER ;

-- Eliminar (baja lógica) tarifa
DELIMITER $$
CREATE PROCEDURE SP_D_TARIFA_01(IN TAR_ID INT)
BEGIN
    DELETE FROM tarifa 
    WHERE IdTarifa = TAR_ID;
END$$
DELIMITER ;

-- Obtener tarifa por ID
DELIMITER $$
CREATE PROCEDURE SP_L_TARIFA_X_ID_01(
    IN TAR_ID INT
)
BEGIN
    SELECT IdTarifa AS TAR_ID,
        Descripcion AS TAR_DESC,
        Precio AS TAR_PRECIO,
        Estado AS EST   
    FROM tarifa
    WHERE IdTarifa = TAR_ID
    LIMIT 1;
END$$
DELIMITER ;



-- Verificar existencia (descripcion única)
DELIMITER $$
CREATE PROCEDURE SP_S_TARIFA_VERIFICAR_EXISTENTE_01(
    IN TAR_DESC VARCHAR(100),
    IN EXCLUDE_ID INT
)
BEGIN
    SELECT COUNT(1) AS cnt
    FROM tarifa
    WHERE TRIM(UPPER(Descripcion)) = TRIM(UPPER(TAR_DESC))
      AND (EXCLUDE_ID IS NULL OR IdTarifa <> EXCLUDE_ID);
END$$
DELIMITER ;