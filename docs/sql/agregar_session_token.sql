-- =============================================
-- AGREGAR COLUMNA session_token A TABLA USUARIO
-- Para implementar sesión única por usuario
-- =============================================

-- Verificar si la columna ya existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = 'usuario';
SET @columnname = 'session_token';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(64) NULL DEFAULT NULL COMMENT ''Token de sesión única para prevenir logins múltiples'';')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Crear índice para mejorar el rendimiento de las consultas
SET @preparedStatement2 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = 'idx_session_token')
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_session_token ON ', @tablename, ' (session_token);')
));

PREPARE createIndexIfNotExists FROM @preparedStatement2;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

SELECT 'Columna session_token agregada exitosamente a la tabla usuario' AS Resultado;
