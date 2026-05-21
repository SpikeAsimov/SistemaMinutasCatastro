<?php
declare(strict_types=1);

class Venta
{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /** @return array Listado de ventas con nombre de minuta */
    public function todas(): array {
        $sql = "SELECT v.*, m.nombre AS minuta_nombre, m.numero AS minuta_numero_intr, u.nombre AS vendedor
                FROM ventas v
                JOIN minutas m ON v.minuta_id = m.id
                JOIN usuarios u ON v.usuario_id = u.id
                ORDER BY v.fecha DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function crear(array $datos): int {
        $sql = "INSERT INTO ventas (minuta_id, numero_minuta, tipo_precio, comprador_nombre, observaciones, total, usuario_id)
                VALUES (:minuta_id, :numero, :tipo, :comprador, :obs, :total, :usuario)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':minuta_id' => $datos['minuta_id'],
            ':numero'    => $datos['numero_minuta'],
            ':tipo'      => $datos['tipo_precio'],
            ':comprador' => $datos['comprador_nombre'] ?: 'Consumidor Final',
            ':obs'       => $datos['observaciones'] ?? null,
            ':total'     => $datos['total'],
            ':usuario'   => $_SESSION['usuario_id'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }
}