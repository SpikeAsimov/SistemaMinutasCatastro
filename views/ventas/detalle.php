<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Detalle de Venta #<?= (int) $venta['id'] ?></h1>
        <p class="page-subtitle">Operacion registrada el <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?>.</p>
    </div>
    <div class="page-actions d-flex flex-wrap gap-2">
        <a href="index.php?page=ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
        <a href="index.php?page=ventas&action=edit&id=<?= (int) $venta['id'] ?>" class="btn btn-outline-brand">
            <i class="fa-solid fa-pen-to-square me-1"></i> Editar
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="app-card h-100">
            <div class="app-card-body">
                <div class="text-muted small">Comprador</div>
                <div class="h5 mb-0 fw-bold"><?= htmlspecialchars($venta['comprador_nombre']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card h-100">
            <div class="app-card-body">
                <div class="text-muted small">Items</div>
                <div class="h5 mb-0 fw-bold"><?= count($items) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card h-100">
            <div class="app-card-body">
                <div class="text-muted small">Total</div>
                <div class="h5 mb-0 fw-bold money">$<?= number_format((float)$venta['total'], 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card h-100">
            <div class="app-card-body">
                <div class="text-muted small">Forma de pago</div>
                <div class="h6 mb-0 fw-bold"><?= htmlspecialchars(Venta::etiquetaFormaPago($venta['forma_pago'] ?? null)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="app-card mb-3">
    <div class="app-card-body">
        <div class="text-muted small mb-1">Observaciones</div>
        <div><?= htmlspecialchars($venta['observaciones'] ?: 'Sin observaciones') ?></div>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header d-flex align-items-center justify-content-between">
        <h2 class="h5 mb-0 fw-bold">Items vendidos</h2>
        <button type="button" id="btnReimprimirTicket" class="btn btn-brand btn-sm">
            <i class="fa-solid fa-print me-1"></i> Reimprimir ticket
        </button>
    </div>
    <div class="table-wrap">
        <table class="table app-table">
            <thead>
                <tr>
                    <th>Minuta</th>
                    <th>Numero interno</th>
                    <th>Numero vendido</th>
                    <th>Tipo precio</th>
                    <th class="text-end">Precio unitario</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($it['minuta_nombre']) ?></td>
                        <td><?= (int) $it['minuta_numero_intr'] ?></td>
                        <td><?= (int) $it['numero_minuta'] ?></td>
                        <td><?= htmlspecialchars($it['tipo_precio']) ?></td>
                        <td class="text-end money">$<?= number_format((float)$it['precio_unitario'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="4" class="text-end">Total</td>
                    <td class="text-end money">$<?= number_format((float)$venta['total'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$ventaIdParaReimprimir = (int) $venta['id'];
$scriptsExtra = "<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnReimprimirTicket = document.getElementById('btnReimprimirTicket');
    if (!btnReimprimirTicket) {
        return;
    }

    btnReimprimirTicket.addEventListener('click', function () {
        const textoOriginal = btnReimprimirTicket.innerHTML;
        btnReimprimirTicket.disabled = true;
        btnReimprimirTicket.innerHTML = '<i class=\"fa-solid fa-spinner fa-spin me-1\"></i> Reimprimiendo';

        fetch('http://localhost/SistemaESCPOS/imprimir_venta.php?venta_id={$ventaIdParaReimprimir}')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.ok) {
                    throw new Error(data.error || 'No se pudo reimprimir el ticket.');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Ticket enviado',
                    text: 'La venta fue enviada nuevamente a impresion.',
                    timer: 1800,
                    showConfirmButton: false
                });
            })
            .catch(function (error) {
                console.error('No se pudo reimprimir el ticket:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo reimprimir',
                    text: 'Verifique que SistemaESCPOS este abierto y conectado.',
                    confirmButtonColor: '#950606'
                });
            })
            .finally(function () {
                btnReimprimirTicket.disabled = false;
                btnReimprimirTicket.innerHTML = textoOriginal;
            });
    });
});
</script>";
require __DIR__ . '/../layout.php';
