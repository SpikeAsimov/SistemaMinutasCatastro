<?php
declare(strict_types=1);

class VentaController
{
    private Venta $ventaModel;
    private Minuta $minutaModel;
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->ventaModel = new Venta($db);
        $this->minutaModel = new Minuta($db);
    }

    public function index(): void {
        $ventas = $this->ventaModel->todas();
        $minutas = $this->minutaModel->todas();
        require __DIR__ . '/../views/ventas/index.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=ventas');
            exit;
        }
        // Sanitización básica
        $minuta_id   = (int) ($_POST['minuta_id'] ?? 0);
        $numero      = (int) ($_POST['numero_minuta'] ?? 0);
        $tipo_precio = $_POST['tipo_precio'] ?? 'normal';
        $comprador   = trim($_POST['comprador_nombre'] ?? 'Consumidor Final');
        $obs         = trim($_POST['observaciones'] ?? '');

        if ($minuta_id <= 0 || $numero <= 0) {
            $_SESSION['error'] = 'Debe seleccionar una minuta y un número válido.';
            header('Location: index.php?page=ventas');
            exit;
        }

        // Calcular total según tipo
        $minuta = $this->minutaModel->porId($minuta_id);
        if (!$minuta) {
            $_SESSION['error'] = 'Minuta no encontrada.';
            header('Location: index.php?page=ventas');
            exit;
        }
        $total = ($tipo_precio === 'urgente') ? $minuta['precio_urgente'] : $minuta['precio_normal'];

        try {
            $this->ventaModel->crear([
                'minuta_id'       => $minuta_id,
                'numero_minuta'   => $numero,
                'tipo_precio'     => $tipo_precio,
                'comprador_nombre'=> $comprador,
                'observaciones'   => $obs,
                'total'           => $total,
            ]);
            $_SESSION['exito'] = 'Venta registrada correctamente.';
        } catch (PDOException $e) {
            // Captura violación de llave única compuesta
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                $_SESSION['error'] = "El número de minuta $numero ya fue registrado para el tipo seleccionado.";
            } else {
                $_SESSION['error'] = 'Error al registrar la venta.';
            }
        }
        header('Location: index.php?page=ventas');
        exit;
    }

    public function reporte(): void {
        $ventas = $this->ventaModel->todas();
        require __DIR__ . '/../views/ventas/reporte.php';
    }
}