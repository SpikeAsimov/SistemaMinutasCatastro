-- Reversión de 20260916_add_forma_pago_ventas.sql.
-- ADVERTENCIA: eliminar la columna borra de forma irreversible la clasificación
-- de todas las ventas nuevas o editadas. Realice el respaldo indicado en README.md.

DROP PROCEDURE IF EXISTS rollback_20260916_forma_pago;

DELIMITER $$

CREATE PROCEDURE rollback_20260916_forma_pago()
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ventas'
          AND CONSTRAINT_NAME = 'chk_ventas_forma_pago'
    ) THEN
        ALTER TABLE ventas DROP CHECK chk_ventas_forma_pago;
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ventas'
          AND INDEX_NAME = 'idx_ventas_forma_pago_fecha'
    ) THEN
        ALTER TABLE ventas DROP INDEX idx_ventas_forma_pago_fecha;
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ventas'
          AND COLUMN_NAME = 'forma_pago'
    ) THEN
        ALTER TABLE ventas DROP COLUMN forma_pago;
    END IF;
END$$

DELIMITER ;

CALL rollback_20260916_forma_pago();
DROP PROCEDURE rollback_20260916_forma_pago;
