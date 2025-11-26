-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-11-2025 a las 03:33:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db-hotel`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_CATEGORIA_01` (IN `CAT_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE categoria 
    SET Estado = NUEVO_ESTADO 
    WHERE IdCategoria = CAT_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_ESTADO_HABITACION_01` (IN `EST_HAB_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE estado_habitacion 
    SET Estado = NUEVO_ESTADO 
    WHERE IdEstadoHabitacion = EST_HAB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_HABITACION_01` (IN `HAB_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE habitacion 
    SET Estado = NUEVO_ESTADO 
    WHERE IdHabitacion = HAB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_PISO_01` (IN `PISO_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE piso 
    SET Estado = NUEVO_ESTADO 
    WHERE IdPiso = PISO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_ROL_01` (IN `ROL_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE rol 
    SET Estado = NUEVO_ESTADO 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_USUARIO_01` (IN `USU_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE usuario 
    SET Estado = NUEVO_ESTADO 
    WHERE IdUsuario = USU_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_CATEGORIA_01` (IN `CAT_ID` INT)   BEGIN
    DELETE FROM categoria 
    WHERE IdCategoria = CAT_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_ESTADO_HABITACION_01` (IN `p_est_hab_id` INT)   BEGIN
    DELETE FROM estado_habitacion 
    WHERE IdEstadoHabitacion = p_est_hab_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_HABITACION_01` (IN `HAB_ID` INT)   BEGIN
    DELETE FROM habitacion 
    WHERE IdHabitacion = HAB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_PISO_01` (IN `PISO_ID` INT)   BEGIN
    DELETE FROM piso 
    WHERE IdPiso = PISO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_ROL_01` (IN `ROL_ID` INT)   BEGIN
    DELETE FROM rol 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_USUARIO_01` (IN `USU_ID` INT)   BEGIN
    DELETE FROM usuario 
    WHERE IdUsuario = USU_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_CATEGORIA_01` (IN `CAT_NOM` VARCHAR(150))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    SELECT IdCategoria INTO existing_id 
    FROM categoria 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(CAT_NOM))
    LIMIT 1;
    IF existing_id > 0 THEN
        UPDATE categoria 
        SET Estado = 1,
            FechaCreacion = NOW() 
        WHERE IdCategoria = existing_id;
        SELECT existing_id as IdCategoria, 'Registro reactivado' as Mensaje;
    ELSE
        INSERT INTO categoria (Descripcion, Estado, FechaCreacion) 
        VALUES (CAT_NOM, 1, NOW());
        SELECT LAST_INSERT_ID() as IdCategoria, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_CLIENTE_01` (IN `p_tipo_documento` VARCHAR(15), IN `p_documento` VARCHAR(15), IN `p_nombre` VARCHAR(50), IN `p_apellido` VARCHAR(50), IN `p_direccion` VARCHAR(250), OUT `p_id_cliente` INT)   BEGIN
    INSERT INTO cliente (TipoDocumento, Documento, Nombre, Apellido, Direccion, Estado, FechaCreacion)
    VALUES (p_tipo_documento, p_documento, p_nombre, p_apellido, p_direccion, 1, NOW());
    
    SET p_id_cliente = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_ESTADO_HABITACION_01` (IN `EST_HAB_NOM` VARCHAR(50))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdEstadoHabitacion INTO existing_id 
    FROM estado_habitacion 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(EST_HAB_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE estado_habitacion 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdEstadoHabitacion = existing_id;
        
        SELECT existing_id as IdEstadoHabitacion, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO estado_habitacion (Descripcion, Estado, FechaCreacion) 
        VALUES (EST_HAB_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdEstadoHabitacion, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_HABITACION_01` (IN `HAB_NUM` VARCHAR(50), IN `HAB_DET` VARCHAR(100), IN `HAB_PRE` DECIMAL(10,2), IN `HAB_EST_ID` INT, IN `HAB_PISO_ID` INT, IN `HAB_CAT_ID` INT)   BEGIN
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
        INSERT INTO habitacion (Numero, Detalle, IdEstadoHabitacion, IdPiso, IdCategoria, Estado, FechaCreacion) 
        VALUES (HAB_NUM, HAB_DET, HAB_EST_ID, HAB_PISO_ID, HAB_CAT_ID, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdHabitacion, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_MENU_02` (`p_IdRol` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_PISO_01` (IN `PISO_NOM` VARCHAR(50))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdPiso INTO existing_id 
    FROM piso 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(PISO_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE piso 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdPiso = existing_id;
        
        SELECT existing_id as IdPiso, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO piso (Descripcion, Estado, FechaCreacion) 
        VALUES (PISO_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdPiso, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_RECEPCION_01` (IN `p_IdCliente` INT, IN `p_IdHabitacion` INT, IN `p_PrecioInicial` DECIMAL(10,2), IN `p_Adelanto` DECIMAL(10,2), IN `p_Observacion` VARCHAR(500), IN `p_FechaSalida` DATETIME, OUT `p_IdRecepcion` INT)   BEGIN
    DECLARE v_PrecioRestante DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_TotalPagado DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_CostoPenalidad DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_EstadoOcupado INT DEFAULT NULL;

    SET v_PrecioRestante = GREATEST(p_PrecioInicial - IFNULL(p_Adelanto, 0.00), 0.00);
    SET v_TotalPagado   = IFNULL(p_Adelanto, 0.00);
    SET v_CostoPenalidad = 0.00;

    INSERT INTO recepcion (
        IdCliente,
        IdHabitacion,
        FechaEntrada,
        FechaSalida,
        FechaSalidaConfirmacion,
        PrecioInicial,
        Adelanto,
        PrecioRestante,
        TotalPagado,
        CostoPenalidad,
        Observacion,
        Estado
    ) VALUES (
        p_IdCliente,
        p_IdHabitacion,
        NOW(),
        p_FechaSalida,
        NULL,
        p_PrecioInicial,
        IFNULL(p_Adelanto, 0.00),
        v_PrecioRestante,
        v_TotalPagado,
        v_CostoPenalidad,
        p_Observacion,
        1
    );

    -- Marcar habitación como "Ocupado" de forma dinámica
    SELECT IdEstadoHabitacion INTO v_EstadoOcupado
    FROM estado_habitacion 
    WHERE Estado = 1 
      AND UPPER(Descripcion) LIKE '%OCUPADO%'
    ORDER BY IdEstadoHabitacion ASC
    LIMIT 1;

    IF v_EstadoOcupado IS NOT NULL THEN
        UPDATE habitacion 
        SET IdEstadoHabitacion = v_EstadoOcupado
        WHERE IdHabitacion = p_IdHabitacion;
    END IF;

    SET p_IdRecepcion = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_ROL_01` (IN `ROL_NOM` VARCHAR(50))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdRol INTO existing_id 
    FROM rol 
    WHERE Descripcion = ROL_NOM 
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE rol 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdRol = existing_id;
        
        SELECT existing_id as IdRol, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO rol (Descripcion, Estado, FechaCreacion) 
        VALUES (ROL_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdRol, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_USUARIO_01` (IN `USU_NOM` VARCHAR(50), IN `USU_APE` VARCHAR(50), IN `USU_DNI` VARCHAR(8), IN `USU_CORREO` VARCHAR(100), IN `USU_PASS` VARCHAR(255), IN `ROL_ID` INT)   BEGIN
    INSERT INTO usuario (Nombre, Apellido, DNI, Correo, Pass, Estado, FechaCreacion, IdRol) 
    VALUES (USU_NOM, USU_APE, USU_DNI, USU_CORREO, USU_PASS, 1, NOW(), ROL_ID);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CATEGORIA_01` ()   BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM categoria 
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CATEGORIA_02` (IN `CAT_ID` INT)   BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM categoria 
    WHERE IdCategoria = CAT_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CATEGORIA_03` ()   BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM categoria 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CLIENTE_01` ()   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CLIENTE_02` (IN `p_id` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ESTADO_HABITACION_01` ()   BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    ORDER BY IdEstadoHabitacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ESTADO_HABITACION_02` (IN `p_est_hab_id` INT)   BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    WHERE IdEstadoHabitacion = p_est_hab_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ESTADO_HABITACION_03` ()   BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    WHERE Estado = 1
    ORDER BY Descripcion ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_01` ()   BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        (
            SELECT t.Monto
            FROM habitacion_tarifa ht
            JOIN tarifa t ON t.IdTarifa = ht.id_tarifa
            WHERE ht.id_habitacion = h.IdHabitacion
              AND t.Tipo = 'NOCHE'
              AND ht.fecha_inicio <= NOW()
              AND (ht.fecha_fin IS NULL OR ht.fecha_fin >= NOW())
            ORDER BY ht.fecha_inicio DESC
            LIMIT 1
        ) AS HAB_PRE,
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_02` (IN `HAB_ID` INT)   BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        (
            SELECT t.Monto
            FROM habitacion_tarifa ht
            JOIN tarifa t ON t.IdTarifa = ht.id_tarifa
            WHERE ht.id_habitacion = h.IdHabitacion
              AND t.Tipo = 'NOCHE'
              AND ht.fecha_inicio <= NOW()
              AND (ht.fecha_fin IS NULL OR ht.fecha_fin >= NOW())
            ORDER BY ht.fecha_inicio DESC
            LIMIT 1
        ) AS HAB_PRE,
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_03` ()   BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        (
            SELECT t.Monto
            FROM habitacion_tarifa ht
            JOIN tarifa t ON t.IdTarifa = ht.id_tarifa
            WHERE ht.id_habitacion = h.IdHabitacion
              AND t.Tipo = 'NOCHE'
              AND ht.fecha_inicio <= NOW()
              AND (ht.fecha_fin IS NULL OR ht.fecha_fin >= NOW())
            ORDER BY ht.fecha_inicio DESC
            LIMIT 1
        ) AS HAB_PRE,
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_OCUPADO` ()   BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        (
            SELECT t.Monto
            FROM habitacion_tarifa ht
            JOIN tarifa t ON t.IdTarifa = ht.id_tarifa
            WHERE ht.id_habitacion = h.IdHabitacion
              AND t.Tipo = 'NOCHE'
              AND ht.fecha_inicio <= NOW()
              AND (ht.fecha_fin IS NULL OR ht.fecha_fin >= NOW())
            ORDER BY ht.fecha_inicio DESC
            LIMIT 1
        ) AS HAB_PRE,
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_MENU_01` (IN `IdRol` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PISO_01` ()   BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM piso 
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PISO_02` (IN `PISO_ID` INT)   BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM piso 
    WHERE IdPiso = PISO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PISO_03` ()   BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM piso 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ROL_01` ()   BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM rol 
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ROL_02` (IN `ROL_ID` INT)   BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM rol 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ROL_03` ()   BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM rol 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_USUARIO_02` (IN `USU_ID` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_USUARIO_03` (IN `CURRENT_USER_ID` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_USUARIO_BY_DNI_01` (IN `USU_DNI` VARCHAR(15))   BEGIN
    SELECT IdUsuario 
    FROM usuario 
    WHERE DNI = USU_DNI AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_CATEGORIA_01` (IN `CAT_ID` INT, IN `CAT_NOM` VARCHAR(150))   BEGIN
    UPDATE categoria 
    SET Descripcion = CAT_NOM
    WHERE IdCategoria = CAT_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_ESTADO_HABITACION_01` (IN `EST_HAB_ID` INT, IN `EST_HAB_NOM` VARCHAR(50))   BEGIN
    UPDATE estado_habitacion 
    SET Descripcion = EST_HAB_NOM 
    WHERE IdEstadoHabitacion = EST_HAB_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_HABITACION_01` (IN `HAB_ID` INT, IN `HAB_NUM` VARCHAR(50), IN `HAB_DET` VARCHAR(100), IN `HAB_PRE` DECIMAL(10,2), IN `HAB_EST_ID` INT, IN `HAB_PISO_ID` INT, IN `HAB_CAT_ID` INT)   BEGIN
    UPDATE habitacion 
    SET Numero = HAB_NUM,
        Detalle = HAB_DET,
        IdEstadoHabitacion = HAB_EST_ID,
        IdPiso = HAB_PISO_ID,
        IdCategoria = HAB_CAT_ID
    WHERE IdHabitacion = HAB_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_MENU_01` (IN `MEND_ID` INT)   BEGIN
    UPDATE TD_MENU
    SET MEND_PERMI = 'Si'
    WHERE TD_MENU.MEND_ID = MEND_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_MENU_02` (IN `MEND_ID` INT)   BEGIN
    UPDATE TD_MENU
    SET MEND_PERMI = 'No'
    WHERE TD_MENU.MEND_ID = MEND_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_PISO_01` (IN `PISO_ID` INT, IN `PISO_NOM` VARCHAR(50))   BEGIN
    UPDATE piso 
    SET Descripcion = PISO_NOM 
    WHERE IdPiso = PISO_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_ROL_01` (IN `ROL_ID` INT, IN `ROL_NOM` VARCHAR(50))   BEGIN
    UPDATE rol 
    SET Descripcion = ROL_NOM 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_USUARIO_01` (IN `USU_ID` INT, IN `USU_NOM` VARCHAR(50), IN `USU_APE` VARCHAR(50), IN `USU_DNI` VARCHAR(15), IN `USU_CORREO` VARCHAR(100), IN `USU_PASS` VARCHAR(255), IN `ROL_ID` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_USUARIO_PASS_01` (IN `USU_ID` INT, IN `USU_PASS` VARCHAR(255))   BEGIN
    UPDATE usuario 
    SET Pass = USU_PASS
    WHERE IdUsuario = USU_ID;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `IdCategoria` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`IdCategoria`, `Descripcion`, `Estado`, `FechaCreacion`) VALUES
(36, 'Tematica', 1, '2025-10-29 15:35:19'),
(37, 'Matrimonial', 1, '2025-10-29 16:30:23'),
(38, 'Doble', 1, '2025-11-13 21:44:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `IdCliente` int(11) NOT NULL,
  `TipoDocumento` varchar(15) NOT NULL,
  `Documento` varchar(15) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Direccion` varchar(250) DEFAULT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`IdCliente`, `TipoDocumento`, `Documento`, `Nombre`, `Apellido`, `Direccion`, `Estado`, `FechaCreacion`) VALUES
(2, 'DNI', '73668217', 'Jean', 'Cordova', 'gfdsgfdgfd', 1, '2025-11-05 22:23:15'),
(3, 'RUC', '12345678987', 'Juan', 'Perez ', 'ghfhgf', 1, '2025-11-05 23:20:34'),
(4, 'DNI', '77777777', 'Maryuri', 'Cordova', 'Sullana', 1, '2025-11-05 23:58:56'),
(5, 'RUC', '11111111', 'Juan', 'Prez', 'Mallaritos', 1, '2025-11-06 00:01:40'),
(6, 'DNI', '65465465', 'MariaJose', 'Amaya Romero', 'Calle Santa Isabel', 1, '2025-11-06 00:06:28'),
(7, 'DNI', '77766556', 'AAnyelo', 'gfdg', 'fgfd', 1, '2025-11-06 00:16:11'),
(12, 'DNI', '22222222', 'sddsd', 'sds', 'sds', 1, '2025-11-06 00:32:49'),
(15, 'DNI', '73456787', 'LIZBETH MERY', 'HUAMANI CAQUI', 'jjjjhjhg', 1, '2025-11-06 00:38:35'),
(23, 'DNI', '76567897', 'BRUNO ALEXANDER', 'DOMINGUEZ BARRETO', 'jjjj', 1, '2025-11-06 00:48:24'),
(24, 'DNI', '78675678', 'SNAYDER DOMINIC', 'SALAS SAYA', 'Su casa', 1, '2025-11-06 00:53:15'),
(25, 'DNI', '76545678', 'YESICA', 'CASTRO CASAFRANCA', 'S casa', 1, '2025-11-06 01:23:27'),
(29, 'DNI', '45654567', 'ELIZET MARILYN', 'VERDE JULIAN', 'Mallaritors', 1, '2025-11-06 23:16:26'),
(30, '', '', '', '', '', 1, '2025-11-09 22:34:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `IdDetalleVenta` int(11) NOT NULL,
  `IdVenta` int(11) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `SubTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_habitacion`
--

CREATE TABLE `estado_habitacion` (
  `IdEstadoHabitacion` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_habitacion`
--

INSERT INTO `estado_habitacion` (`IdEstadoHabitacion`, `Descripcion`, `Estado`, `FechaCreacion`) VALUES
(11, 'DISPONIBLE', 1, '2025-10-29 22:31:13'),
(12, 'OCUPADO', 1, '2025-10-29 22:31:25'),
(13, 'LIMPIEZA', 1, '2025-10-29 22:31:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitacion`
--

CREATE TABLE `habitacion` (
  `IdHabitacion` int(11) NOT NULL,
  `Numero` varchar(50) NOT NULL,
  `Detalle` varchar(100) DEFAULT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `IdEstadoHabitacion` int(11) NOT NULL,
  `IdPiso` int(11) NOT NULL,
  `IdCategoria` int(11) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitacion`
--

INSERT INTO `habitacion` (`IdHabitacion`, `Numero`, `Detalle`, `Precio`, `IdEstadoHabitacion`, `IdPiso`, `IdCategoria`, `Estado`, `FechaCreacion`) VALUES
(6, '102', 'fsfs', 20.00, 12, 3, 38, 1, '2025-11-13 22:11:10'),
(7, '103', 'fdhfghgf', 70.00, 12, 3, 36, 1, '2025-11-13 23:37:32'),
(8, '12', 'fsddfsd', 22.00, 11, 3, 37, 1, '2025-11-14 21:31:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitacion_tarifa`
--

CREATE TABLE `habitacion_tarifa` (
  `id_habitacion_tarifa` int(11) NOT NULL,
  `id_habitacion` int(11) NOT NULL,
  `id_tarifa` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitacion_tarifa`
--

INSERT INTO `habitacion_tarifa` (`id_habitacion_tarifa`, `id_habitacion`, `id_tarifa`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 8, 1, '2025-11-15 21:32:24', '2025-11-29 21:32:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `piso`
--

CREATE TABLE `piso` (
  `IdPiso` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `piso`
--

INSERT INTO `piso` (`IdPiso`, `Descripcion`, `Estado`, `FechaCreacion`) VALUES
(1, 'PRIMER PISO', 1, '2025-10-29 16:57:32'),
(3, 'SEGUNDO PISO', 1, '2025-11-03 21:21:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `IdProducto` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Detalle` varchar(100) DEFAULT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recepcion`
--

CREATE TABLE `recepcion` (
  `IdRecepcion` int(11) NOT NULL,
  `IdCliente` int(11) NOT NULL,
  `IdHabitacion` int(11) NOT NULL,
  `FechaEntrada` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaSalida` datetime DEFAULT NULL,
  `FechaSalidaConfirmacion` datetime DEFAULT NULL,
  `PrecioInicial` decimal(10,2) NOT NULL,
  `Adelanto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `PrecioRestante` decimal(10,2) NOT NULL DEFAULT 0.00,
  `TotalPagado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `CostoPenalidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Observacion` varchar(500) DEFAULT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recepcion`
--

INSERT INTO `recepcion` (`IdRecepcion`, `IdCliente`, `IdHabitacion`, `FechaEntrada`, `FechaSalida`, `FechaSalidaConfirmacion`, `PrecioInicial`, `Adelanto`, `PrecioRestante`, `TotalPagado`, `CostoPenalidad`, `Observacion`, `Estado`) VALUES
(5, 25, 6, '2025-11-13 23:14:41', '2025-11-14 02:14:00', NULL, 20.00, 10.00, 10.00, 10.00, 0.00, 'dfgdf', 1),
(6, 24, 7, '2025-11-13 23:38:43', '2025-11-14 02:38:00', NULL, 70.00, 80.00, 0.00, 80.00, 0.00, 'kkk', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `IdRol` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`IdRol`, `Descripcion`, `Estado`, `FechaCreacion`) VALUES
(1, 'Administrador', 1, '2025-10-14 10:08:05'),
(2, 'Empleado', 1, '2025-10-14 10:08:05'),
(12, 'tECNICO', 0, '2025-10-29 14:20:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifa`
--

CREATE TABLE `tarifa` (
  `IdTarifa` int(11) NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarifa`
--

INSERT INTO `tarifa` (`IdTarifa`, `Descripcion`, `Precio`, `Estado`, `FechaCreacion`) VALUES
(1, 'Tarifa Doble', 30.00, 1, '2025-11-15 21:31:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `td_menu`
--

CREATE TABLE `td_menu` (
  `MEND_ID` int(11) NOT NULL,
  `MEN_ID` int(11) DEFAULT NULL,
  `IdRol` int(11) DEFAULT NULL,
  `MEND_PERMI` varchar(2) DEFAULT NULL,
  `FECH_CREA` datetime(3) DEFAULT NULL,
  `EST` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `td_menu`
--

INSERT INTO `td_menu` (`MEND_ID`, `MEN_ID`, `IdRol`, `MEND_PERMI`, `FECH_CREA`, `EST`) VALUES
(1, 2, 1, 'Si', NULL, 1),
(32, 1, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(33, 3, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(34, 4, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(35, 5, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(40, 10, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(41, 11, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(42, 12, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(47, 1, 2, 'No', '2025-10-24 22:48:40.359', 1),
(48, 2, 2, 'No', '2025-10-24 22:48:40.359', 1),
(49, 3, 2, 'Si', '2025-10-24 22:48:40.359', 1),
(50, 4, 2, 'No', '2025-10-24 22:48:40.359', 1),
(51, 5, 2, 'No', '2025-10-24 22:48:40.359', 1),
(56, 10, 2, 'No', '2025-10-24 22:48:40.359', 1),
(57, 11, 2, 'No', '2025-10-24 22:48:40.359', 1),
(58, 12, 2, 'No', '2025-10-24 22:48:40.359', 1),
(75, 13, 1, 'Si', '2025-11-05 20:26:00.320', 1),
(76, 14, 1, 'Si', '2025-11-12 19:09:38.017', 1),
(77, 15, 1, 'Si', '2025-11-12 20:27:58.993', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_menu`
--

CREATE TABLE `tm_menu` (
  `MEN_ID` int(11) NOT NULL,
  `MEN_NOM` varchar(150) DEFAULT NULL,
  `MEN_RUTA` varchar(250) DEFAULT NULL,
  `MEN_IDENTI` varchar(150) DEFAULT NULL,
  `MEN_GRUPO` varchar(150) DEFAULT NULL,
  `MEN_ORDEN` int(11) DEFAULT NULL,
  `FECH_CREA` datetime(3) DEFAULT NULL,
  `EST` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_menu`
--

INSERT INTO `tm_menu` (`MEN_ID`, `MEN_NOM`, `MEN_RUTA`, `MEN_IDENTI`, `MEN_GRUPO`, `MEN_ORDEN`, `FECH_CREA`, `EST`) VALUES
(1, 'Dashboard', '../home', 'dashboard', 'Principal', 1, '2025-10-24 15:40:00.000', 1),
(2, 'Rol', '../MntRol', 'mnt-rol', 'Usuarios', 2, '2025-10-24 15:40:00.000', 1),
(3, 'Mantenimiento Categoria', '../MntCategoria', 'mnt-categoria', 'Mantenimiento', 3, '2025-10-24 15:40:00.000', 1),
(4, 'Mantenimiento Piso', '../MntPiso', 'mnt-piso', 'Mantenimiento', 4, '2025-10-24 15:40:00.000', 1),
(5, 'Mantenimiento Habitacion', '../MntHabitacion', 'mnt-habitacion', 'Mantenimiento', 5, '2025-10-24 15:40:00.000', 1),
(10, 'Usuarios', '../MntUsuario', 'mnt-usuario', 'Usuarios', 10, '2025-10-24 15:40:00.000', 1),
(11, 'Clientes', '	\n../MntCliente', 'mnt-cliente', 'Clientes', 11, '2025-10-24 15:40:00.000', 1),
(12, 'Estado Habitacion', '	\n../MntEstadoHabitacion', 'mtn-estadohabitacion', 'Mantenimiento', 12, '2025-10-24 15:40:00.000', 1),
(13, 'Recepcion', '../ListRecepcion', 'gst-recepcion', 'Gestion', 3, '2025-11-05 20:25:45.000', 1),
(14, 'Productos', '../MntProducto', 'mtn-producto', 'Tienda', 1, '2025-11-12 19:09:24.000', 1),
(15, 'Vender', '../ListVender', 'gst-venta', 'Tienda', 1, '2025-11-12 20:26:53.000', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IdUsuario` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `DNI` varchar(8) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `Pass` varchar(255) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  `IdRol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IdUsuario`, `Nombre`, `Apellido`, `DNI`, `Correo`, `Pass`, `Estado`, `FechaCreacion`, `IdRol`) VALUES
(1, 'n', 'a', 'd', 'pierrecodex18@gmail.com', '$2y$10$0bPMytnjSTOwvZI3jrG/.Oggo23UKQPp5nsDGaiusYf5vnbczPrCO', 1, '2025-10-14 10:08:33', 1),
(21, 'Jose Mario', 'Perez', '87764534', 'jose@gmail.com', '$2y$10$dslmGcl8TJ02Fl63Fbsb3eTewBB2v5DuA6BC6DBqcQJr4t0OwnRJK', 1, '2025-10-15 16:22:49', 2),
(53, 'Marta Prez 3', 'Cordova', '76567897', 'polo2@gmail.com', '$2y$10$uJYSX8ldeG0l7Z6rlxMK8uywNm5fudURjhtUXkbwQ0d2qeo0gGycK', 1, '2025-10-29 13:32:35', 2),
(54, 'FSDF', 'cordova', '87764532', 'joshhhe@gmail.com', '$2y$10$4LILqWdX5EWNzI8OJ/0ZmuDpMnPh2cZdr.f4Fatc42R/qx0WxTFmW', 1, '2025-10-29 14:06:33', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `IdVenta` int(11) NOT NULL,
  `IdRecepcion` int(11) NOT NULL,
  `Total` decimal(10,2) NOT NULL,
  `Estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`IdCategoria`),
  ADD UNIQUE KEY `uq_categoria_descripcion` (`Descripcion`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`IdCliente`),
  ADD UNIQUE KEY `uq_cliente_documento` (`Documento`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`IdDetalleVenta`),
  ADD KEY `fk_detalle_venta_venta` (`IdVenta`),
  ADD KEY `fk_detalle_venta_producto` (`IdProducto`);

--
-- Indices de la tabla `estado_habitacion`
--
ALTER TABLE `estado_habitacion`
  ADD PRIMARY KEY (`IdEstadoHabitacion`);

--
-- Indices de la tabla `habitacion`
--
ALTER TABLE `habitacion`
  ADD PRIMARY KEY (`IdHabitacion`),
  ADD UNIQUE KEY `uq_habitacion_numero` (`Numero`),
  ADD KEY `fk_habitacion_estado` (`IdEstadoHabitacion`),
  ADD KEY `fk_habitacion_piso` (`IdPiso`),
  ADD KEY `fk_habitacion_categoria` (`IdCategoria`);

--
-- Indices de la tabla `habitacion_tarifa`
--
ALTER TABLE `habitacion_tarifa`
  ADD PRIMARY KEY (`id_habitacion_tarifa`),
  ADD KEY `ix_habitacion_tarifa_habitacion` (`id_habitacion`),
  ADD KEY `ix_habitacion_tarifa_tarifa` (`id_tarifa`),
  ADD KEY `ix_habitacion_tarifa_periodo` (`id_habitacion`,`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `piso`
--
ALTER TABLE `piso`
  ADD PRIMARY KEY (`IdPiso`),
  ADD UNIQUE KEY `uq_piso_descripcion` (`Descripcion`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`IdProducto`),
  ADD UNIQUE KEY `uq_producto_nombre` (`Nombre`);

--
-- Indices de la tabla `recepcion`
--
ALTER TABLE `recepcion`
  ADD PRIMARY KEY (`IdRecepcion`),
  ADD KEY `fk_recepcion_cliente` (`IdCliente`),
  ADD KEY `fk_recepcion_habitacion` (`IdHabitacion`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`IdRol`),
  ADD UNIQUE KEY `uq_rol_descripcion` (`Descripcion`);

--
-- Indices de la tabla `tarifa`
--
ALTER TABLE `tarifa`
  ADD PRIMARY KEY (`IdTarifa`);

--
-- Indices de la tabla `td_menu`
--
ALTER TABLE `td_menu`
  ADD PRIMARY KEY (`MEND_ID`),
  ADD KEY `FK_td_menu_tm_menu` (`MEN_ID`),
  ADD KEY `FK_td_menu_rol` (`IdRol`);

--
-- Indices de la tabla `tm_menu`
--
ALTER TABLE `tm_menu`
  ADD PRIMARY KEY (`MEN_ID`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IdUsuario`),
  ADD KEY `ix_usuario_dni` (`DNI`),
  ADD KEY `ix_usuario_correo` (`Correo`),
  ADD KEY `ix_usuario_rol` (`IdRol`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`IdVenta`),
  ADD KEY `fk_venta_recepcion` (`IdRecepcion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `IdCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `IdCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `IdDetalleVenta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_habitacion`
--
ALTER TABLE `estado_habitacion`
  MODIFY `IdEstadoHabitacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `habitacion`
--
ALTER TABLE `habitacion`
  MODIFY `IdHabitacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `habitacion_tarifa`
--
ALTER TABLE `habitacion_tarifa`
  MODIFY `id_habitacion_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `piso`
--
ALTER TABLE `piso`
  MODIFY `IdPiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `IdProducto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recepcion`
--
ALTER TABLE `recepcion`
  MODIFY `IdRecepcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `IdRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tarifa`
--
ALTER TABLE `tarifa`
  MODIFY `IdTarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `td_menu`
--
ALTER TABLE `td_menu`
  MODIFY `MEND_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `tm_menu`
--
ALTER TABLE `tm_menu`
  MODIFY `MEN_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `IdVenta` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_detalle_venta_producto` FOREIGN KEY (`IdProducto`) REFERENCES `producto` (`IdProducto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_venta_venta` FOREIGN KEY (`IdVenta`) REFERENCES `venta` (`IdVenta`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `habitacion`
--
ALTER TABLE `habitacion`
  ADD CONSTRAINT `fk_habitacion_categoria` FOREIGN KEY (`IdCategoria`) REFERENCES `categoria` (`IdCategoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_habitacion_estado` FOREIGN KEY (`IdEstadoHabitacion`) REFERENCES `estado_habitacion` (`IdEstadoHabitacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_habitacion_piso` FOREIGN KEY (`IdPiso`) REFERENCES `piso` (`IdPiso`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `habitacion_tarifa`
--
ALTER TABLE `habitacion_tarifa`
  ADD CONSTRAINT `fk_habitacion_tarifa_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `habitacion` (`IdHabitacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_habitacion_tarifa_tarifa` FOREIGN KEY (`id_tarifa`) REFERENCES `tarifa` (`IdTarifa`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `recepcion`
--
ALTER TABLE `recepcion`
  ADD CONSTRAINT `fk_recepcion_cliente` FOREIGN KEY (`IdCliente`) REFERENCES `cliente` (`IdCliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recepcion_habitacion` FOREIGN KEY (`IdHabitacion`) REFERENCES `habitacion` (`IdHabitacion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `td_menu`
--
ALTER TABLE `td_menu`
  ADD CONSTRAINT `FK_td_menu_rol` FOREIGN KEY (`IdRol`) REFERENCES `rol` (`IdRol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_td_menu_tm_menu` FOREIGN KEY (`MEN_ID`) REFERENCES `tm_menu` (`MEN_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`IdRol`) REFERENCES `rol` (`IdRol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `fk_venta_recepcion` FOREIGN KEY (`IdRecepcion`) REFERENCES `recepcion` (`IdRecepcion`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
