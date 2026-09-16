<?php

declare(strict_types=1);

class VentaController
{
    private Venta $ventaModel;
    private Minuta $minutaModel;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ventaModel = new Venta($db);
        $this->minutaModel = new Minuta($db);
    }

    public function index(): void
    {
        $filtros = $this->obtenerFiltrosReporte();
        $ventas = $this->ventaModel->buscar($filtros['inicio'], $filtros['fin'], $filtros['forma_pago']);
        $resumen = $this->ventaModel->resumen($filtros['inicio'], $filtros['fin'], $filtros['forma_pago']);
        $minutas = $this->minutaModel->activas();
        $ventaFormData = $_SESSION['venta_form_data'] ?? [];
        unset($_SESSION['venta_form_data']);
        require __DIR__ . '/../views/ventas/index.php';
    }

    public function detalle(): void
    {
        $venta_id = (int)($_GET['id'] ?? 0);
        $items = $this->ventaModel->itemsDeVenta($venta_id);
        $venta = $this->ventaModel->porId($venta_id);
        if (!$venta) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: index.php?page=ventas');
            exit;
        }
        require __DIR__ . '/../views/ventas/detalle.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=ventas');
            exit;
        }

        // Leer cabecera
        $comprador = trim($_POST['comprador_nombre'] ?? 'Consumidor Final');
        $obs = trim($_POST['observaciones'] ?? '');
        $formaPago = trim((string)($_POST['forma_pago'] ?? ''));

        // Leer items (arrays enviados desde el formulario)
        $itemsMinutaId = $_POST['items_minuta_id'] ?? [];
        $itemsNumero   = $_POST['items_numero'] ?? [];
        $itemsTipo     = $_POST['items_tipo'] ?? [];
        $itemsPrecio   = $_POST['items_precio'] ?? [];

        $formData = [
            'comprador_nombre' => $comprador,
            'observaciones' => $obs,
            'forma_pago' => $formaPago,
            'items_minuta_id' => is_array($itemsMinutaId) ? $itemsMinutaId : [],
            'items_numero' => is_array($itemsNumero) ? $itemsNumero : [],
            'items_tipo' => is_array($itemsTipo) ? $itemsTipo : [],
            'items_precio' => is_array($itemsPrecio) ? $itemsPrecio : [],
        ];

        if (!Venta::esFormaPagoValida($formaPago)) {
            $this->redirigirErrorAlta('Seleccione una forma de pago válida.', $formData);
        }

        // Validar que al menos un ítem
        if (!is_array($itemsMinutaId) || count($itemsMinutaId) === 0) {
            $this->redirigirErrorAlta('Debe agregar al menos una minuta al carrito.', $formData);
        }

        // Construir array de items
        $items = [];
        $minutasCache = []; // para buscar precios si es necesario
        foreach ($itemsMinutaId as $i => $mid) {
            $mid = (int) $mid;
            $num = (int)($itemsNumero[$i] ?? 0);
            $tipo = $itemsTipo[$i] ?? 'normal';
            $precio = (float)($itemsPrecio[$i] ?? 0);

            if ($mid <= 0 || $num <= 0 || $precio <= 0) {
                $this->redirigirErrorAlta('Ítem inválido: complete todos los datos.', $formData);
            }

            // Obtener precio desde la base si el cliente no lo envió correctamente (por seguridad)
            if (!isset($minutasCache[$mid])) {
                $minutasCache[$mid] = $this->minutaModel->porId($mid);
            }
            $minuta = $minutasCache[$mid];
            if (!$minuta) {
                $this->redirigirErrorAlta('Minuta no encontrada.', $formData);
            }
            $precioCalculado = ($tipo === 'urgente') ? $minuta['precio_urgente'] : $minuta['precio_normal'];
            // Usamos el precio calculado del servidor para evitar manipulaciones
            $items[] = [
                'minuta_id' => $mid,
                'numero_minuta' => $num,
                'tipo_precio' => $tipo,
                'precio' => $precioCalculado,
            ];
        }

        try {
            $ventaId = $this->ventaModel->crear([
                'comprador_nombre' => $comprador,
                'observaciones' => $obs,
                'forma_pago' => $formaPago,
            ], $items);
            unset($_SESSION['venta_form_data']);
            $_SESSION['imprimir_venta_id'] = $ventaId;
            $_SESSION['exito'] = 'Venta registrada correctamente.';
        } catch (PDOException $e) {
            // Captura violación de llave única en ítems
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                $_SESSION['error'] = 'Al menos un número de minuta ya fue registrado previamente. Verifique el carrito.';
            } else {
                $_SESSION['error'] = 'Error al registrar la venta: ' . $e->getMessage();
            }
            $_SESSION['venta_form_data'] = $formData;
        }
        header('Location: index.php?page=ventas');
        exit;
    }

        /** Muestra el formulario de edición de una venta */
    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $venta = $this->ventaModel->porId($id);
        if (!$venta) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: index.php?page=ventas');
            exit;
        }
        $items = $this->ventaModel->itemsDeVenta($id);
        $minutas = $this->minutaModel->activas();
        $formData = $_SESSION['venta_edit_form_data'][$id] ?? [];
        unset($_SESSION['venta_edit_form_data'][$id]);
        require __DIR__ . '/../views/ventas/edit.php';
    }

    /** Procesa la actualización de la venta */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=ventas');
            exit;
        }

        $venta_id = (int)($_POST['venta_id'] ?? 0);
        $comprador = trim($_POST['comprador_nombre'] ?? 'Consumidor Final');
        $obs = trim($_POST['observaciones'] ?? '');
        $formaPagoRaw = trim((string)($_POST['forma_pago'] ?? ''));

        $ventaExistente = $this->ventaModel->porId($venta_id);
        if (!$ventaExistente) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: index.php?page=ventas');
            exit;
        }

        $formaPago = null;
        if (Venta::esFormaPagoValida($formaPagoRaw)) {
            $formaPago = $formaPagoRaw;
        } elseif ($formaPagoRaw !== '' || $ventaExistente['forma_pago'] !== null) {
            $this->redirigirErrorEdicion($venta_id, 'Seleccione una forma de pago válida.', [
                'comprador_nombre' => $comprador,
                'observaciones' => $obs,
                'forma_pago' => $formaPagoRaw,
            ]);
        }

        $formData = [
            'comprador_nombre' => $comprador,
            'observaciones' => $obs,
            'forma_pago' => $formaPago,
        ];

        // Leer arrays de ítems enviados
        $itemsMinutaId = $_POST['items_minuta_id'] ?? [];
        $itemsNumero   = $_POST['items_numero'] ?? [];
        $itemsTipo     = $_POST['items_tipo'] ?? [];
        $itemsPrecio   = $_POST['items_precio'] ?? [];

        if (empty($itemsMinutaId)) {
            $this->redirigirErrorEdicion($venta_id, 'Debe haber al menos un ítem.', $formData);
        }

        // Construir array de items (validación similar a store)
        $items = [];
        $minutasCache = [];
        foreach ($itemsMinutaId as $i => $mid) {
            $mid = (int) $mid;
            $num = (int)($itemsNumero[$i] ?? 0);
            $tipo = $itemsTipo[$i] ?? 'normal';
            $precio = (float)($itemsPrecio[$i] ?? 0);

            if ($mid <= 0 || $num <= 0 || $precio <= 0) {
                $this->redirigirErrorEdicion($venta_id, 'Ítem inválido.', $formData);
            }

            if (!isset($minutasCache[$mid])) {
                $minutasCache[$mid] = $this->minutaModel->porId($mid);
            }
            $minuta = $minutasCache[$mid];
            if (!$minuta) {
                $this->redirigirErrorEdicion($venta_id, 'Minuta no encontrada.', $formData);
            }
            $precioCalculado = ($tipo === 'urgente') ? $minuta['precio_urgente'] : $minuta['precio_normal'];
            $items[] = [
                'minuta_id'      => $mid,
                'numero_minuta'  => $num,
                'tipo_precio'    => $tipo,
                'precio'         => $precioCalculado,
            ];
        }

        try {
            $this->ventaModel->actualizarCompleta($venta_id, [
                'comprador_nombre' => $comprador,
                'observaciones'    => $obs,
                'forma_pago'       => $formaPago,
            ], $items);
            unset($_SESSION['venta_edit_form_data'][$venta_id]);
            $_SESSION['exito'] = 'Venta actualizada correctamente.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                $_SESSION['error'] = 'Al menos un número de minuta ya está registrado.';
            } else {
                $_SESSION['error'] = 'Error al actualizar la venta.';
            }
            $_SESSION['venta_edit_form_data'][$venta_id] = $formData;
        }
        header('Location: index.php?page=ventas');
        exit;
    }

    public function reporte(): void
    {
        $filtros = $this->obtenerFiltrosReporte();
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];
        $ventas = $this->ventaModel->buscar($inicio, $fin, $filtros['forma_pago']);
        $resumen = $this->ventaModel->resumen($inicio, $fin, $filtros['forma_pago']);

        $ventasConItems = [];
        foreach ($ventas as $venta) {
            $venta['items'] = $this->ventaModel->itemsDeVenta($venta['id']);
            $ventasConItems[] = $venta;
        }
        require __DIR__ . '/../views/ventas/reporte.php';
    }

    private function redirigirErrorAlta(string $mensaje, array $formData): void
    {
        $_SESSION['error'] = $mensaje;
        $_SESSION['venta_form_data'] = $formData;
        header('Location: index.php?page=ventas');
        exit;
    }

    private function redirigirErrorEdicion(int $ventaId, string $mensaje, array $formData): void
    {
        $_SESSION['error'] = $mensaje;
        $_SESSION['venta_edit_form_data'][$ventaId] = $formData;
        header("Location: index.php?page=ventas&action=edit&id=$ventaId");
        exit;
    }

    private function obtenerFiltrosReporte(): array
    {
        $start = $this->normalizarMes($_GET['start'] ?? null);
        $end = $this->normalizarMes($_GET['end'] ?? null);

        if ($start !== null && $end !== null && $start > $end) {
            [$start, $end] = [$end, $start];
        }

        $formaPago = trim((string)($_GET['forma_pago'] ?? ''));
        $formasFiltroValidas = array_merge(
            array_keys(Venta::formasPago()),
            [Venta::FILTRO_SIN_ESPECIFICAR]
        );
        if (!in_array($formaPago, $formasFiltroValidas, true)) {
            $formaPago = null;
        }

        return [
            'start' => $start,
            'end' => $end,
            'inicio' => $start === null ? null : $start . '-01 00:00:00',
            'fin' => $end === null ? null : date('Y-m-t 23:59:59', strtotime($end . '-01')),
            'forma_pago' => $formaPago,
        ];
    }

    private function normalizarMes(mixed $mes): ?string
    {
        if (!is_string($mes) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            return null;
        }
        return $mes;
    }
}
