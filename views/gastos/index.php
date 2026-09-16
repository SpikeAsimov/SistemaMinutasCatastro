<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gastos Mensuales</h1>
        <p class="page-subtitle">Control de egresos y reportes por periodo.</p>
    </div>
    <div class="page-actions d-flex flex-wrap gap-2">
        <a href="index.php?page=gastos&action=create" class="btn btn-brand">
            <i class="fa-solid fa-plus me-1"></i> Nuevo gasto
        </a>
        <a href="index.php?page=ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Ventas
        </a>
    </div>
</div>

<div class="toolbar-card mb-3">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3 col-lg-2">
            <label class="form-label fw-semibold" for="reporteStart">Desde</label>
            <input type="month" id="reporteStart" class="form-control form-control-sm" value="<?= date('Y-m') ?>">
        </div>
        <div class="col-12 col-md-3 col-lg-2">
            <label class="form-label fw-semibold" for="reporteEnd">Hasta</label>
            <input type="month" id="reporteEnd" class="form-control form-control-sm" value="<?= date('Y-m') ?>">
        </div>
        <div class="col-12 col-md-auto">
            <button id="btnGenerarReporte" class="btn btn-brand-dark btn-sm">
                <i class="fa-solid fa-calendar-days me-1"></i> Generar reporte
            </button>
        </div>
        <div class="col-12 col-md-auto">
            <a href="index.php?page=gastos&action=reporte" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="fa-solid fa-print me-1"></i> Imprimir todo
            </a>
        </div>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0 fw-bold">Listado de gastos</h2>
        <span class="text-muted small"><?= count($gastos) ?> registros</span>
    </div>
    <div class="table-wrap">
        <table class="table table-hover app-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripcion</th>
                    <th>Numero factura</th>
                    <th class="text-end">Importe</th>
                    <th>Cargado por</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gastos)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">No hay gastos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gastos as $g): ?>
                        <tr>
                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($g['fecha'])) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($g['descripcion']) ?></td>
                            <td><?= htmlspecialchars($g['numero_factura']) ?></td>
                            <td class="text-end money">$<?= number_format((float)$g['importe'], 2) ?></td>
                            <td><?= htmlspecialchars($g['usuario_nombre']) ?></td>
                            <td class="text-end">
                                <a href="index.php?page=gastos&action=edit&id=<?= (int) $g['id'] ?>" class="btn btn-sm btn-outline-brand" data-bs-toggle="tooltip" title="Editar gasto">
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

<script>
document.getElementById('btnGenerarReporte').addEventListener('click', function() {
    const start = document.getElementById('reporteStart').value;
    const end = document.getElementById('reporteEnd').value;
    if (!start || !end) {
        Swal.fire({
            icon: 'warning',
            title: 'Seleccione ambos meses',
            text: 'Indique el mes de inicio y de cierre para generar el reporte.',
            confirmButtonColor: window.AppTheme?.confirmColor || '#436947'
        });
        return;
    }
    window.open('index.php?page=gastos&action=reporte&start=' + start + '&end=' + end, '_blank');
});
</script>

<?php
$contenido = ob_get_clean();
$scriptsExtra = '';
require __DIR__ . '/../layout.php';
