-- Script para agregar columna FechaCreacion a la tabla venta
-- Esto permite limpiar ventas borrador antiguas y restaurar su stock automáticamente

-- Verificar si la columna ya existe antes de agregarla
SET @exist := (SELECT COUNT(*) 
               FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'venta' 
               AND COLUMN_NAME = 'FechaCreacion');

SET @query := IF(@exist = 0, 
    'ALTER TABLE venta ADD COLUMN FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP',
    'SELECT "La columna FechaCreacion ya existe" AS mensaje');

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Alternativa simple (ejecutar directamente):
-- ALTER TABLE venta ADD COLUMN FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP;
