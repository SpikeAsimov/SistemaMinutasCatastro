<?php
declare(strict_types=1);

class Minuta
{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /** @return array Todas las minutas activas */
    public function todas(): array {
        return $this->db->query("SELECT id, nombre, numero, precio_normal, precio_urgente FROM minutas WHERE activo = 1 ORDER BY id")->fetchAll();
    }

    public function porId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM minutas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}