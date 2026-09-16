<?php

declare(strict_types=1);

class GastoController
{
    private Gasto $gastoModel;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->gastoModel = new Gasto($db);
    }

    /** Listado de gastos */
    public function index(): void
    {
        $gastos = $this->gastoModel->todos();
        require __DIR__ . '/../views/gastos/index.php';
    }

    /** Muestra formulario de creación */
    public function create(): void
    {
        require __DIR__ . '/../views/gastos/create.php';
    }

    /** Procesa el guardado del nuevo gasto */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=gastos');
            exit;
        }

        $fecha    = $_POST['fecha'] ?? '';
        $desc     = trim($_POST['descripcion'] ?? '');
        $num_fact = trim($_POST['numero_factura'] ?? '');
        $importe  = (float) ($_POST['importe'] ?? 0);

        if ($fecha === '' || $desc === '' || $num_fact === '' || $importe <= 0) {
            $_SESSION['error'] = 'Todos los campos son obligatorios y el importe debe ser mayor a 0.';
            header('Location: index.php?page=gastos&action=create');
            exit;
        }

        try {
            $this->gastoModel->crear([
                'fecha'          => $fecha,
                'descripcion'    => $desc,
                'numero_factura' => $num_fact,
                'importe'        => $importe,
            ]);
            $_SESSION['exito'] = 'Gasto registrado correctamente.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error al guardar el gasto.';
        }

        header('Location: index.php?page=gastos');
        exit;
    }

        /** Muestra el formulario de edición */
    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $gasto = $this->gastoModel->porId($id);
        if (!$gasto) {
            $_SESSION['error'] = 'Gasto no encontrado.';
            header('Location: index.php?page=gastos');
            exit;
        }
        require __DIR__ . '/../views/gastos/edit.php';
    }

    /** Procesa la actualización del gasto */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=gastos');
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $fecha    = $_POST['fecha'] ?? '';
        $desc     = trim($_POST['descripcion'] ?? '');
        $num_fact = trim($_POST['numero_factura'] ?? '');
        $importe  = (float)($_POST['importe'] ?? 0);

        if ($id <= 0 || $fecha === '' || $desc === '' || $num_fact === '' || $importe <= 0) {
            $_SESSION['error'] = 'Todos los campos son obligatorios y el importe debe ser mayor a 0.';
            header("Location: index.php?page=gastos&action=edit&id=$id");
            exit;
        }

        try {
            $this->gastoModel->actualizar($id, [
                'fecha'          => $fecha,
                'descripcion'    => $desc,
                'numero_factura' => $num_fact,
                'importe'        => $importe,
            ]);
            $_SESSION['exito'] = 'Gasto actualizado correctamente.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error al actualizar el gasto.';
        }

        header('Location: index.php?page=gastos');
        exit;
    }

        /** Genera el reporte PDF de gastos con filtro de meses */
    public function reporte(): void {
        $start = $_GET['start'] ?? null;
        $end   = $_GET['end']   ?? null;

        if ($start && $end) {
            $inicio = date('Y-m-d 00:00:00', strtotime($start . '-01'));
            $fin    = date('Y-m-t 23:59:59', strtotime($start . '-01'));
            if ($start !== $end) {
                $fin = date('Y-m-t 23:59:59', strtotime($end . '-01'));
            }
            $gastos = $this->gastoModel->todasPorRango($inicio, $fin);
        } else {
            $gastos = $this->gastoModel->todos();
        }
        require __DIR__ . '/../views/gastos/reporte.php';
    }
}
