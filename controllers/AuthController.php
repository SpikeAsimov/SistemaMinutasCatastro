<?php
declare(strict_types=1);

class AuthController
{
    private Usuario $usuarioModel;
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->usuarioModel = new Usuario($db);
    }

    public function loginForm(): void {
        require __DIR__ . '/../views/login.php';
    }

    public function loginPost(): void {
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = $this->usuarioModel->autenticar($usuario, $password);
        if ($user) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            header('Location: index.php?page=ventas');
            exit;
        }
        $_SESSION['error'] = 'Credenciales inválidas';
        header('Location: index.php?page=login');
        exit;
    }

    public function logout(): void {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}