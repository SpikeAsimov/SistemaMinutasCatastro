<?php

declare(strict_types=1);

class Venta
{
    public const FORMA_PAGO_EFECTIVO = 'efectivo';
    public const FORMA_PAGO_TRANSFERENCIA = 'transferencia';
    public const FILTRO_SIN_ESPECIFICAR = 'sin_especificar';

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public static function formasPago(): array
    {
        return [
            self::FORMA_PAGO_EFECTIVO => 'Ventas en efectivo',
            self::FORMA_PAGO_TRANSFERENCIA => 'Transferencias a cuenta',
        ];
    }

    public static function esFormaPagoValida(?string $formaPago): bool
    {
        return $formaPago !== null && array_key_exists($formaPago, self::formasPago());
    }

    public static function etiquetaFormaPago(?string $formaPago): string
    {
        return self::formasPago()[$formaPago] ?? 'Sin especificar';
    }

    /** Obtiene todas las ventas con su total y cantidad de ítems. */
    public function todas(): array
    {
        return $this->buscar();
    }

    /**
     * Busca ventas sin unir sus ítems, para que una venta siempre cuente una sola vez.
     */
    public function buscar(?string $inicio = null, ?string $fin = null, ?string $formaPago = null): array
    {
        [$where, $params] = $this->construirFiltros($inicio, $fin, $formaPago);
        $sql = "SELECT v.*, u.nombre AS vendedor,
                (SELECT COUNT(*) FROM venta_items vi WHERE vi.venta_id = v.id) AS num_items
                FROM ventas v
                JOIN usuarios u ON v.usuario_id = u.id
                $where
                ORDER BY v.fecha DESC, v.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Totales de todas las ventas que cumplen los filtros, no solo de una página. */
    public function resumen(?string $inicio = null, ?string $fin = null, ?string $formaPago = null): array
    {
        [$where, $params] = $this->construirFiltros($inicio, $fin, $formaPago);
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN v.forma_pago = 'efectivo' THEN 1 ELSE 0 END), 0) AS cantidad_efectivo,
                    COALESCE(SUM(CASE WHEN v.forma_pago = 'efectivo' THEN v.total ELSE 0 END), 0) AS total_efectivo,
                    COALESCE(SUM(CASE WHEN v.forma_pago = 'transferencia' THEN 1 ELSE 0 END), 0) AS cantidad_transferencia,
                    COALESCE(SUM(CASE WHEN v.forma_pago = 'transferencia' THEN v.total ELSE 0 END), 0) AS total_transferencia,
                    COALESCE(SUM(CASE WHEN v.forma_pago IS NULL OR v.forma_pago NOT IN ('efectivo', 'transferencia') THEN 1 ELSE 0 END), 0) AS cantidad_sin_especificar,
                    COALESCE(SUM(CASE WHEN v.forma_pago IS NULL OR v.forma_pago NOT IN ('efectivo', 'transferencia') THEN v.total ELSE 0 END), 0) AS total_sin_especificar,
                    COUNT(*) AS cantidad_general,
                    COALESCE(SUM(v.total), 0) AS total_general
                FROM ventas v
                $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resumen = $stmt->fetch() ?: [];

        return array_merge([
            'cantidad_efectivo' => 0,
            'total_efectivo' => '0.00',
            'cantidad_transferencia' => 0,
            'total_transferencia' => '0.00',
            'cantidad_sin_especificar' => 0,
            'total_sin_especificar' => '0.00',
            'cantidad_general' => 0,
            'total_general' => '0.00',
        ], $resumen);
    }

    /** Obtiene los ítems de una venta específica */
    public function itemsDeVenta(int $venta_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT vi.*, m.nombre AS minuta_nombre, m.numero AS minuta_numero_intr
             FROM venta_items vi
             JOIN minutas m ON vi.minuta_id = m.id
             WHERE vi.venta_id = :vid
             ORDER BY vi.id"
        );
        $stmt->execute([':vid' => $venta_id]);
        return $stmt->fetchAll();
    }

    /** Crea una venta con múltiples ítems en una transacción */
    public function crear(array $cabecera, array $items): int
    {
        if (!self::esFormaPagoValida($cabecera['forma_pago'] ?? null)) {
            throw new InvalidArgumentException('Forma de pago inválida para una venta nueva.');
        }

        $this->db->beginTransaction();
        try {
            // Insertar cabecera
            $stmt = $this->db->prepare(
                "INSERT INTO ventas (comprador_nombre, observaciones, forma_pago, usuario_id, total)
                 VALUES (:comp, :obs, :forma_pago, :uid, 0)"
            );
            $stmt->execute([
                ':comp' => $cabecera['comprador_nombre'] ?: 'Consumidor Final',
                ':obs'  => $cabecera['observaciones'] ?? null,
                ':forma_pago' => $cabecera['forma_pago'],
                ':uid'  => $_SESSION['usuario_id'] ?? 0,
            ]);
            $ventaId = (int) $this->db->lastInsertId();

            $totalVenta = 0;
            $stmtItem = $this->db->prepare(
                "INSERT INTO venta_items (venta_id, minuta_id, numero_minuta, tipo_precio, precio_unitario)
                 VALUES (:vid, :mid, :num, :tipo, :precio)"
            );
            foreach ($items as $item) {
                $precio = (float) $item['precio'];
                $stmtItem->execute([
                    ':vid'   => $ventaId,
                    ':mid'   => $item['minuta_id'],
                    ':num'   => $item['numero_minuta'],
                    ':tipo'  => $item['tipo_precio'],
                    ':precio' => $precio,
                ]);
                $totalVenta += $precio;
            }

            // Actualizar el total de la venta
            $this->db->prepare("UPDATE ventas SET total = :total WHERE id = :id")
                ->execute([':total' => $totalVenta, ':id' => $ventaId]);

            $this->db->commit();
            return $ventaId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            // Relanzar para capturar en el controlador (clave duplicada, etc.)
            throw $e;
        }
    }

    /** Ventas con ítems dentro de un rango de fechas (inicio y fin inclusive). */
    public function todasPorRango(string $inicio, string $fin, ?string $formaPago = null): array
    {
        return $this->buscar($inicio, $fin, $formaPago);
    }


        /** Obtiene una venta por su ID */
    public function porId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM ventas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Actualiza la cabecera de una venta */
    public function actualizarCabecera(int $id, array $datos): void {
        $stmt = $this->db->prepare(
            "UPDATE ventas
             SET comprador_nombre = :comp, observaciones = :obs, forma_pago = :forma_pago, total = :total
             WHERE id = :id"
        );
        $stmt->execute([
            ':comp' => $datos['comprador_nombre'],
            ':obs'  => $datos['observaciones'] ?? null,
            ':forma_pago' => $datos['forma_pago'],
            ':total'=> $datos['total'],
            ':id'   => $id
        ]);
    }

    /** Elimina todos los ítems de una venta (se usa dentro de una transacción) */
    public function eliminarItems(int $venta_id): void {
        $this->db->prepare("DELETE FROM venta_items WHERE venta_id = :vid")->execute([':vid' => $venta_id]);
    }

    /** Inserta un nuevo ítem para una venta (dentro de transacción) */
    public function insertarItem(int $venta_id, array $item): void {
        $stmt = $this->db->prepare(
            "INSERT INTO venta_items (venta_id, minuta_id, numero_minuta, tipo_precio, precio_unitario)
             VALUES (:vid, :mid, :num, :tipo, :precio)"
        );
        $stmt->execute([
            ':vid'   => $venta_id,
            ':mid'   => $item['minuta_id'],
            ':num'   => $item['numero_minuta'],
            ':tipo'  => $item['tipo_precio'],
            ':precio'=> $item['precio'],
        ]);
    }

    /** Actualiza una venta completa: cabecera + items (borra todos los items y los reinserta) */
    public function actualizarCompleta(int $id, array $cabecera, array $items): void {
        $formaPago = $cabecera['forma_pago'] ?? null;
        if ($formaPago !== null && !self::esFormaPagoValida($formaPago)) {
            throw new InvalidArgumentException('Forma de pago inválida para la venta.');
        }

        $this->db->beginTransaction();
        try {
            $totalVenta = 0;
            foreach ($items as $item) {
                $totalVenta += (float)$item['precio'];
            }
            $this->actualizarCabecera($id, [
                'comprador_nombre' => $cabecera['comprador_nombre'],
                'observaciones'   => $cabecera['observaciones'] ?? null,
                'forma_pago'      => $cabecera['forma_pago'],
                'total'           => $totalVenta,
            ]);
            $this->eliminarItems($id);
            foreach ($items as $item) {
                $this->insertarItem($id, $item);
            }
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function construirFiltros(?string $inicio, ?string $fin, ?string $formaPago): array
    {
        $condiciones = [];
        $params = [];

        if ($inicio !== null) {
            $condiciones[] = 'v.fecha >= :inicio';
            $params[':inicio'] = $inicio;
        }
        if ($fin !== null) {
            $condiciones[] = 'v.fecha <= :fin';
            $params[':fin'] = $fin;
        }

        if ($formaPago === self::FILTRO_SIN_ESPECIFICAR) {
            $condiciones[] = "(v.forma_pago IS NULL OR v.forma_pago NOT IN ('efectivo', 'transferencia'))";
        } elseif ($formaPago !== null) {
            if (!self::esFormaPagoValida($formaPago)) {
                throw new InvalidArgumentException('Forma de pago de filtro inválida.');
            }
            $condiciones[] = 'v.forma_pago = :forma_pago';
            $params[':forma_pago'] = $formaPago;
        }

        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
        return [$where, $params];
    }

}
