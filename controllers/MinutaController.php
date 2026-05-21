<?php

declare(strict_types=1);

class MinutaController
{
    private Minuta $minutaModel;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->minutaModel = new Minuta($db);
    }

    public function index(): void
    {
        $minutas = $this->minutaModel->todas();
        require __DIR__ . '/../views/minutas/index.php';
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $minuta = $this->minutaModel->porId($id);
        if (!$minuta) {
            $_SESSION['error'] = 'Minuta no encontrada.';
            header('Location: index.php?page=minutas');
            exit;
        }
        require __DIR__ . '/../views/minutas/edit.php';
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $precio_normal = (float) ($_POST['precio_normal'] ?? 0);
        $precio_urgente = (float) ($_POST['precio_urgente'] ?? 0);
        // campo "activo" opcional
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($id <= 0 || $nombre === '') {
            $_SESSION['error'] = 'Datos inválidos.';
            header('Location: index.php?page=minutas');
            exit;
        }

        $sql = "UPDATE minutas SET nombre=:n, precio_normal=:pn, precio_urgente=:pu, activo=:a WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':n'  => $nombre,
            ':pn' => $precio_normal,
            ':pu' => $precio_urgente,
            ':a'  => $activo,
            ':id' => $id
        ]);
        $_SESSION['exito'] = 'Minuta actualizada correctamente.';
        header('Location: index.php?page=minutas');
        exit;
    }
}
