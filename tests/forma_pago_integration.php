<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Venta.php';

const TEST_DATABASE = 'catastro_minutas_test_forma_pago';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' (esperado: ' . var_export($expected, true) . ', obtenido: ' . var_export($actual, true) . ')');
    }
}

function assertMoney(float $expected, mixed $actual, string $message): void
{
    if (abs($expected - (float)$actual) > 0.001) {
        throw new RuntimeException($message . ' (esperado: ' . $expected . ', obtenido: ' . (float)$actual . ')');
    }
}

$server = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

if (TEST_DATABASE !== 'catastro_minutas_test_forma_pago') {
    throw new RuntimeException('Nombre de base temporal inesperado.');
}

$server->exec('DROP DATABASE IF EXISTS `' . TEST_DATABASE . '`');
$server->exec('CREATE DATABASE `' . TEST_DATABASE . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

try {
    $db = new PDO('mysql:host=localhost;dbname=' . TEST_DATABASE . ';charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $db->exec("CREATE TABLE usuarios (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB");
    $db->exec("CREATE TABLE minutas (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        numero INT NOT NULL,
        nombre VARCHAR(150) NOT NULL,
        precio_normal DECIMAL(10,2) NOT NULL,
        precio_urgente DECIMAL(10,2) NOT NULL
    ) ENGINE=InnoDB");
    $db->exec("CREATE TABLE ventas (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        comprador_nombre VARCHAR(150) NOT NULL DEFAULT 'Consumidor Final',
        observaciones TEXT NULL,
        forma_pago VARCHAR(20) NULL,
        usuario_id INT NOT NULL,
        fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        CONSTRAINT fk_test_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
        CONSTRAINT chk_test_forma_pago CHECK (forma_pago IS NULL OR forma_pago IN ('efectivo', 'transferencia'))
    ) ENGINE=InnoDB");
    $db->exec("CREATE TABLE venta_items (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        venta_id INT NOT NULL,
        minuta_id INT NOT NULL,
        numero_minuta INT NOT NULL,
        tipo_precio ENUM('normal', 'urgente') NOT NULL DEFAULT 'normal',
        precio_unitario DECIMAL(10,2) NOT NULL,
        UNIQUE KEY uq_test_item (minuta_id, numero_minuta),
        CONSTRAINT fk_test_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
        CONSTRAINT fk_test_minuta FOREIGN KEY (minuta_id) REFERENCES minutas(id)
    ) ENGINE=InnoDB");

    $db->exec("INSERT INTO usuarios (nombre) VALUES ('Pruebas')");
    $db->exec("INSERT INTO minutas (numero, nombre, precio_normal, precio_urgente) VALUES
        (1, 'Minuta 1000', 1000, 1000),
        (2, 'Minuta 2000', 2000, 2000),
        (3, 'Minuta 1500', 1500, 1500),
        (4, 'Minuta 500', 500, 500)");

    $_SESSION['usuario_id'] = 1;
    $model = new Venta($db);
    $crear = static function (Venta $model, int $minutaId, int $numero, float $precio, string $formaPago): int {
        return $model->crear([
            'comprador_nombre' => 'Venta de prueba',
            'observaciones' => null,
            'forma_pago' => $formaPago,
        ], [[
            'minuta_id' => $minutaId,
            'numero_minuta' => $numero,
            'tipo_precio' => 'normal',
            'precio' => $precio,
        ]]);
    };

    $efectivo1 = $crear($model, 1, 101, 1000, Venta::FORMA_PAGO_EFECTIVO);
    assertSameValue(Venta::FORMA_PAGO_EFECTIVO, $model->porId($efectivo1)['forma_pago'], 'Persistencia después de recargar');
    $crear($model, 2, 102, 2000, Venta::FORMA_PAGO_EFECTIVO);
    $crear($model, 3, 103, 1500, Venta::FORMA_PAGO_TRANSFERENCIA);
    $crear($model, 4, 104, 500, Venta::FORMA_PAGO_TRANSFERENCIA);
    $db->exec("INSERT INTO ventas (comprador_nombre, observaciones, forma_pago, usuario_id, fecha, total)
               VALUES ('Histórica', NULL, NULL, 1, '2025-01-15 12:00:00', 700)");

    $resumen = $model->resumen();
    assertSameValue(2, (int)$resumen['cantidad_efectivo'], 'Cantidad en efectivo');
    assertMoney(3000, $resumen['total_efectivo'], 'Importe en efectivo');
    assertSameValue(2, (int)$resumen['cantidad_transferencia'], 'Cantidad digital');
    assertMoney(2000, $resumen['total_transferencia'], 'Importe digital');
    assertSameValue(1, (int)$resumen['cantidad_sin_especificar'], 'Cantidad histórica');
    assertMoney(700, $resumen['total_sin_especificar'], 'Importe histórico');
    assertSameValue(5, (int)$resumen['cantidad_general'], 'Cantidad general');
    assertMoney(5700, $resumen['total_general'], 'Importe general');

    $soloTransferencias = $model->resumen(null, null, Venta::FORMA_PAGO_TRANSFERENCIA);
    assertSameValue(2, (int)$soloTransferencias['cantidad_general'], 'Filtro de transferencias');
    assertMoney(2000, $soloTransferencias['total_general'], 'Total filtrado de transferencias');

    $historicas = $model->buscar(null, null, Venta::FILTRO_SIN_ESPECIFICAR);
    assertSameValue(1, count($historicas), 'Filtro sin especificar');

    $rangoSinHistorico = $model->resumen('2026-01-01 00:00:00', '2026-12-31 23:59:59');
    assertSameValue(4, (int)$rangoSinHistorico['cantidad_general'], 'Filtro de fecha');
    assertMoney(5000, $rangoSinHistorico['total_general'], 'Total del filtro de fecha');

    $sinResultados = $model->resumen('2035-01-01 00:00:00', '2035-01-31 23:59:59');
    assertSameValue(0, (int)$sinResultados['cantidad_general'], 'Cantidad sin resultados');
    assertMoney(0, $sinResultados['total_general'], 'Importe sin resultados');

    $items = $model->itemsDeVenta($efectivo1);
    $model->actualizarCompleta($efectivo1, [
        'comprador_nombre' => 'Venta editada',
        'observaciones' => 'Cambio de forma de pago',
        'forma_pago' => Venta::FORMA_PAGO_TRANSFERENCIA,
    ], [[
        'minuta_id' => (int)$items[0]['minuta_id'],
        'numero_minuta' => (int)$items[0]['numero_minuta'],
        'tipo_precio' => $items[0]['tipo_precio'],
        'precio' => (float)$items[0]['precio_unitario'],
    ]]);
    assertSameValue(Venta::FORMA_PAGO_TRANSFERENCIA, $model->porId($efectivo1)['forma_pago'], 'Edición de forma de pago');

    assertSameValue(false, Venta::esFormaPagoValida(null), 'Rechazo de pago ausente');
    assertSameValue(false, Venta::esFormaPagoValida('tarjeta'), 'Rechazo de pago inválido');
    try {
        $model->crear([
            'comprador_nombre' => 'Inválida',
            'forma_pago' => 'tarjeta',
        ], [[
            'minuta_id' => 1,
            'numero_minuta' => 999,
            'tipo_precio' => 'normal',
            'precio' => 1000,
        ]]);
        throw new RuntimeException('La venta con pago inválido no fue rechazada.');
    } catch (InvalidArgumentException) {
        // Resultado esperado.
    }

    $inicio = null;
    $fin = null;
    $filtros = ['forma_pago' => null];
    $resumen = $model->resumen();
    $ventasConItems = [];
    foreach ($model->buscar() as $venta) {
        $venta['items'] = $model->itemsDeVenta((int)$venta['id']);
        $ventasConItems[] = $venta;
    }
    ob_start();
    require __DIR__ . '/../views/ventas/reporte.php';
    $pdf = ob_get_clean();
    if (!is_string($pdf) || !str_starts_with($pdf, '%PDF-')) {
        throw new RuntimeException('El reporte PDF no pudo generarse.');
    }

    echo "OK: alta, validación, persistencia, edición, filtros, histórico, conciliación, conjunto vacío y PDF.\n";
} finally {
    $server->exec('DROP DATABASE IF EXISTS `' . TEST_DATABASE . '`');
}
