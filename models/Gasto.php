<?php

declare(strict_types=1);

class Gasto
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** Obtiene todos los gastos ordenados por fecha descendente */
    public function todos(): array
    {
        $sql = "SELECT g.*, u.nombre AS usuario_nombre
                FROM gastos g
                JOIN usuarios u ON g.usuario_id = u.id
                ORDER BY g.fecha DESC, g.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Crea un nuevo gasto */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO gastos (fecha, descripcion, numero_factura, importe, usuario_id)
             VALUES (:fecha, :desc, :num_fact, :importe, :uid)"
        );
        $stmt->execute([
            ':fecha'    => $datos['fecha'],
            ':desc'     => $datos['descripcion'],
            ':num_fact' => $datos['numero_factura'],
            ':importe'  => $datos['importe'],
            ':uid'      => $_SESSION['usuario_id'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

        /** Obtiene un gasto por su ID */
    public function porId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM gastos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Actualiza un gasto existente */
    public function actualizar(int $id, array $datos): void {
        $stmt = $this->db->prepare(
            "UPDATE gastos SET fecha = :fecha, descripcion = :desc, numero_factura = :num_fact, importe = :importe
             WHERE id = :id"
        );
        $stmt->execute([
            ':fecha'    => $datos['fecha'],
            ':desc'     => $datos['descripcion'],
            ':num_fact' => $datos['numero_factura'],
            ':importe'  => $datos['importe'],
            ':id'       => $id,
        ]);
    }

        /** Obtiene gastos entre fechas (inicio y fin inclusive) */
    public function todasPorRango(string $inicio, string $fin): array {
        $sql = "SELECT g.*, u.nombre AS usuario_nombre
                FROM gastos g
                JOIN usuarios u ON g.usuario_id = u.id
                WHERE g.fecha BETWEEN :inicio AND :fin
                ORDER BY g.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':inicio' => $inicio, ':fin' => $fin]);
        return $stmt->fetchAll();
    }
}
