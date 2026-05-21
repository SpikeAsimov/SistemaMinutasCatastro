<?php
declare(strict_types=1);

class Usuario
{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function autenticar(string $usuario, string $password): ?array {
        $stmt = $this->db->prepare("SELECT id, nombre, usuario, password FROM usuarios WHERE usuario = :u AND activo = 1");
        $stmt->execute([':u' => $usuario]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password'])) {
            return $row; // sin el hash
        }
        return null;
    }
}