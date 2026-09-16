<?php

declare(strict_types=1);

class Minuta
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** @return array Todos los tipos de minuta, incluidos los inactivos. */
    public function todas(): array
    {
        return $this->db->query(
            "SELECT id, nombre, numero, precio_normal, precio_urgente, activo
            FROM minutas
            ORDER BY id"
        )->fetchAll();
    }

    /** @return array Tipos de minuta disponibles para vender. */
    public function activas(): array
    {
        return $this->db->query(
            "SELECT id, nombre, numero, precio_normal, precio_urgente, activo
             FROM minutas
             WHERE activo = 1
             ORDER BY id"
        )->fetchAll();
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM minutas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function numeroExiste(int $numero, bool $bloquear = false): bool
    {
        $sql = "SELECT id FROM minutas WHERE numero = :numero LIMIT 1";
        if ($bloquear) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':numero' => $numero]);
        return (bool) $stmt->fetchColumn();
    }

    /** Crea un tipo de minuta sin asociarlo a una venta. */
    public function crear(array $datos): int
    {
        $this->db->beginTransaction();

        try {
            if ($this->numeroExiste((int) $datos['numero'], true)) {
                throw new DomainException('Ya existe una minuta con ese numero interno.');
            }

            $stmt = $this->db->prepare(
                "INSERT INTO minutas (nombre, numero, precio_normal, precio_urgente, activo)
                 VALUES (:nombre, :numero, :precio_normal, :precio_urgente, :activo)"
            );
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':numero' => $datos['numero'],
                ':precio_normal' => $datos['precio_normal'],
                ':precio_urgente' => $datos['precio_urgente'],
                ':activo' => $datos['activo'],
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
