-- Garantiza que el numero interno identifique un unico tipo de minuta.
-- Antes de aplicar, la consulta siguiente no debe devolver filas:
-- SELECT numero, COUNT(*) FROM minutas GROUP BY numero HAVING COUNT(*) > 1;

ALTER TABLE minutas
    ADD CONSTRAINT uq_minutas_numero UNIQUE (numero);
