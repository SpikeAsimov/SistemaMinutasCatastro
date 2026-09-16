-- Sistema Minutas Chilecito
-- Agrega la forma de pago a la cabecera de la venta.
-- Los registros existentes quedan en NULL porque no existe evidencia para clasificarlos.
-- Compatible con MySQL 8.x e idempotente ante reejecuciones.

DROP PROCEDURE IF EXISTS migrate_20260916_forma_pago;

DELIMITER $$

CREATE PROCEDURE migrate_20260916_forma_pago()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ventas'
          AND COLUMN_NAME = 'forma_pago'
    ) THEN
        ALTER TABLE ventas
            ADD COLUMN forma_pago VARCHAR(20) NULL
            COMMENT 'efectivo, transferencia; NULL = histórico sin especificar'
            AFTER observaciones;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ventas'
          AND CONSTRAINT_NAME = 'chk_ventas_forma_pago'
    ) THEN
        ALTER TABLE ventas
            ADD CONSTRAINT chk_ventas_forma_pago
            CHECK (forma_pago IS NULL OR forma_pago IN ('efectivo', 'transferencia'));
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ventas'
          AND INDEX_NAME = 'idx_ventas_forma_pago_fecha'
    ) THEN
        ALTER TABLE ventas
            ADD INDEX idx_ventas_forma_pago_fecha (forma_pago, fecha);
    END IF;
END$$

DELIMITER ;

CALL migrate_20260916_forma_pago();
DROP PROCEDURE migrate_20260916_forma_pago;

-- Verificación esperada:
-- 1) La columna es nullable para preservar las ventas históricas.
-- 2) Todas las filas existentes deben figurar como "sin especificar" (NULL).
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'ventas'
  AND COLUMN_NAME = 'forma_pago';

SELECT
    COUNT(*) AS ventas_totales,
    COALESCE(SUM(forma_pago = 'efectivo'), 0) AS ventas_efectivo,
    COALESCE(SUM(forma_pago = 'transferencia'), 0) AS ventas_transferencia,
    COALESCE(SUM(forma_pago IS NULL), 0) AS ventas_sin_especificar
FROM ventas;
