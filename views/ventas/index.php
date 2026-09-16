<?php
ob_start();
$formComprador = (string)($ventaFormData['comprador_nombre'] ?? '');
$formObservaciones = (string)($ventaFormData['observaciones'] ?? '');
$formFormaPago = (string)($ventaFormData['forma_pago'] ?? '');
$minutasPorId = [];
foreach ($minutas as $minutaDisponible) {
    $minutasPorId[(int)$minutaDisponible['id']] = $minutaDisponible;
}
$carritoInicial = [];
foreach (($ventaFormData['items_minuta_id'] ?? []) as $i => $minutaIdAnterior) {
    $minutaIdAnterior = (int)$minutaIdAnterior;
    $numeroAnterior = (int)($ventaFormData['items_numero'][$i] ?? 0);
    $tipoAnterior = (string)($ventaFormData['items_tipo'][$i] ?? 'normal');
    if (!isset($minutasPorId[$minutaIdAnterior]) || $numeroAnterior <= 0 || !in_array($tipoAnterior, ['normal', 'urgente'], true)) {
        continue;
    }
    $minutaAnterior = $minutasPorId[$minutaIdAnterior];
    $carritoInicial[] = [
        'minutaId' => (string)$minutaIdAnterior,
        'numero' => $numeroAnterior,
        'tipo' => $tipoAnterior,
        'precio' => (float)($tipoAnterior === 'urgente' ? $minutaAnterior['precio_urgente'] : $minutaAnterior['precio_normal']),
        'nombreMinuta' => $minutaAnterior['nombre'],
    ];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Ventas de Minutas</h1>
        <p class="page-subtitle">Registro de ventas, reportes mensuales y carga rapida de nuevas operaciones.</p>
    </div>
    <div class="page-actions d-flex flex-wrap gap-2">
        <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalVenta">
            <i class="fa-solid fa-plus me-1"></i> Nueva venta
        </button>
        <a href="index.php?page=minutas" class="btn btn-outline-brand">
            <i class="fa-solid fa-file-lines me-1"></i> Minutas
        </a>
    </div>
</div>

<form class="toolbar-card mb-3" id="filtrosVentas" method="get" action="index.php">
    <input type="hidden" name="page" value="ventas">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3 col-lg-2">
            <label class="form-label fw-semibold" for="reporteStart">Desde</label>
            <input type="month" name="start" id="reporteStart" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['start'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-3 col-lg-2">
            <label class="form-label fw-semibold" for="reporteEnd">Hasta</label>
            <input type="month" name="end" id="reporteEnd" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['end'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label fw-semibold" for="filtroFormaPago">Forma de pago</label>
            <select name="forma_pago" id="filtroFormaPago" class="form-select form-select-sm">
                <option value="">Todas</option>
                <option value="efectivo" <?= ($filtros['forma_pago'] ?? null) === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                <option value="transferencia" <?= ($filtros['forma_pago'] ?? null) === 'transferencia' ? 'selected' : '' ?>>Transferencia / depósito bancario</option>
                <option value="sin_especificar" <?= ($filtros['forma_pago'] ?? null) === 'sin_especificar' ? 'selected' : '' ?>>Sin especificar</option>
            </select>
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-brand-dark btn-sm">
                <i class="fa-solid fa-filter me-1"></i> Aplicar filtros
            </button>
        </div>
        <div class="col-12 col-md-auto">
            <button type="button" id="btnGenerarReporte" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-file-pdf me-1"></i> Abrir PDF
            </button>
        </div>
        <div class="col-12 col-md-auto">
            <a href="index.php?page=ventas" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="app-card h-100"><div class="app-card-body">
            <div class="text-muted small">Ventas en efectivo</div>
            <div class="h5 mb-0 fw-bold"><?= (int)$resumen['cantidad_efectivo'] ?> · $<?= number_format((float)$resumen['total_efectivo'], 2) ?></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="app-card h-100"><div class="app-card-body">
            <div class="text-muted small">Transferencias a cuenta</div>
            <div class="h5 mb-0 fw-bold"><?= (int)$resumen['cantidad_transferencia'] ?> · $<?= number_format((float)$resumen['total_transferencia'], 2) ?></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="app-card h-100"><div class="app-card-body">
            <div class="text-muted small">Sin especificar</div>
            <div class="h5 mb-0 fw-bold"><?= (int)$resumen['cantidad_sin_especificar'] ?> · $<?= number_format((float)$resumen['total_sin_especificar'], 2) ?></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="app-card h-100"><div class="app-card-body">
            <div class="text-muted small">Total general</div>
            <div class="h5 mb-0 fw-bold"><?= (int)$resumen['cantidad_general'] ?> · $<?= number_format((float)$resumen['total_general'], 2) ?></div>
        </div></div>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header d-flex align-items-center justify-content-between gap-2">
        <h2 class="h5 mb-0 fw-bold">Historial de ventas</h2>
        <span class="text-muted small"><?= count($ventas) ?> registros</span>
    </div>
    <div class="table-wrap">
        <table class="table table-hover app-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprador</th>
                    <th>Forma de pago</th>
                    <th class="text-center">Items</th>
                    <th class="text-end">Total</th>
                    <th>Observaciones</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ventas)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No hay ventas para los filtros seleccionados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ventas as $v): ?>
                        <tr>
                            <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($v['comprador_nombre']) ?></td>
                            <td><?= htmlspecialchars(Venta::etiquetaFormaPago($v['forma_pago'] ?? null)) ?></td>
                            <td class="text-center"><?= (int) $v['num_items'] ?></td>
                            <td class="text-end money">$<?= number_format((float)$v['total'], 2) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($v['observaciones'] ?? '') ?></td>
                            <td class="text-end text-nowrap">
                                <a href="index.php?page=ventas&action=detalle&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="index.php?page=ventas&action=edit&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-brand" data-bs-toggle="tooltip" title="Editar venta">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold">Nueva venta</h5>
                    <small class="text-white-50">Cargue items, revise el total y confirme la operacion.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="app-card h-100">
                            <div class="app-card-header">
                                <h3 class="h6 mb-0 fw-bold"><i class="fa-solid fa-cart-plus me-1"></i> Agregar minuta</h3>
                            </div>
                            <div class="app-card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select id="minutaSelect" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($minutas as $m): ?>
                                                    <option value="<?= $m['id'] ?>"
                                                        data-normal="<?= $m['precio_normal'] ?>"
                                                        data-urgente="<?= $m['precio_urgente'] ?>"
                                                        data-nombre="<?= htmlspecialchars($m['nombre'], ENT_QUOTES) ?>">
                                                        <?= htmlspecialchars($m['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="minutaSelect">Minuta</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" id="numeroMinuta" class="form-control" min="1" placeholder="Numero">
                                            <label for="numeroMinuta">Numero</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select id="tipoPrecio" class="form-select">
                                                <option value="normal">Comun</option>
                                                <option value="urgente">Urgente</option>
                                            </select>
                                            <label for="tipoPrecio">Tipo</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="button" id="btnAgregarItem" class="btn btn-brand-dark w-100">
                                            <i class="fa-solid fa-plus me-1"></i> Agregar al carrito
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="app-card h-100">
                            <div class="app-card-header d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-bold"><i class="fa-solid fa-basket-shopping me-1"></i> Carrito</h3>
                                <span class="cart-total fw-bold">Total: <span id="totalCarrito">$0.00</span></span>
                            </div>
                            <div class="table-wrap">
                                <table class="table table-sm app-table" id="tablaCarrito">
                                    <thead>
                                        <tr>
                                            <th>Minuta</th>
                                            <th>Numero</th>
                                            <th>Tipo</th>
                                            <th class="text-end">Precio</th>
                                            <th class="text-end">Quitar</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="app-card">
                            <div class="app-card-body">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold" for="compradorNombre">Comprador</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                            <input type="text" id="compradorNombre" class="form-control" placeholder="Nombre del comprador" value="<?= htmlspecialchars($formComprador) ?>">
                                            <button type="button" class="btn btn-outline-secondary" id="btnConsumidorFinal">Consumidor Final</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold" for="formaPago">Forma de pago</label>
                                        <select id="formaPago" class="form-select" required>
                                            <option value="">Seleccionar forma de pago</option>
                                            <option value="efectivo" <?= $formFormaPago === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                            <option value="transferencia" <?= $formFormaPago === 'transferencia' ? 'selected' : '' ?>>Transferencia / depósito bancario</option>
                                        </select>
                                        <div class="form-text">“Transferencia” incluye depósitos bancarios y solo clasifica la venta; no verifica su acreditación.</div>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold" for="observaciones">Observaciones</label>
                                        <textarea id="observaciones" class="form-control" rows="2" placeholder="Detalle opcional"><?= htmlspecialchars($formObservaciones) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </button>
                <button type="button" id="btnGuardarVenta" class="btn btn-brand">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar venta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnGenerarReporte').addEventListener('click', function() {
        const start = document.getElementById('reporteStart').value;
        const end = document.getElementById('reporteEnd').value;
        const formaPago = document.getElementById('filtroFormaPago').value;
        if ((start && !end) || (!start && end)) {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione ambos meses',
                text: 'Indique ambos meses o deje ambos vacíos para incluir todas las fechas.',
                confirmButtonColor: '#950606'
            });
            return;
        }
        const params = new URLSearchParams({ page: 'ventas', action: 'reporte' });
        if (start) params.set('start', start);
        if (end) params.set('end', end);
        if (formaPago) params.set('forma_pago', formaPago);
        window.open('index.php?' + params.toString(), '_blank');
    });
</script>
<?php
$contenido = ob_get_clean();
$ventaFormJs = [
    'carrito' => $carritoInicial,
    'reabrir' => !empty($ventaFormData),
];
$scriptsExtra = '<script>window.ventaFormData = ' . json_encode($ventaFormJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';
$ventaJsVersion = (string)(filemtime(__DIR__ . '/../../assets/js/venta.js') ?: time());
$scriptsExtra .= '<script src="assets/js/venta.js?v=' . rawurlencode($ventaJsVersion) . '"></script>';
if (!empty($_SESSION['imprimir_venta_id'])) {
    $ventaIdParaImprimir = (int) $_SESSION['imprimir_venta_id'];
    unset($_SESSION['imprimir_venta_id']);
    $scriptsExtra .= "<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch('http://localhost/SistemaESCPOS/imprimir_venta.php?venta_id={$ventaIdParaImprimir}')
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.ok) {
                console.error('No se pudo imprimir el ticket:', data.error);
            }
        })
        .catch(function (error) {
            console.error('No se pudo conectar con SistemaESCPOS:', error);
        });
});
</script>";
}
require __DIR__ . '/../layout.php';
