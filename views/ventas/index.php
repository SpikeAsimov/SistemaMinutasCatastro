<?php
ob_start();
?>
<h2 style="color:#950606;">Ventas de Minutas</h2>
<div class="d-flex justify-content-between mb-3">
    <button class="btn" style="background-color:#E73121; color:#fff;" data-bs-toggle="modal" data-bs-target="#modalVenta">+ Nueva Venta</button>
    <a href="index.php?page=ventas&action=reporte" class="btn btn-outline-secondary" target="_blank">Imprimir Reporte</a>
    <a href="index.php?page=minutas" class="btn btn-outline-secondary">Administrar Minutas</a>
</div>
<table class="table table-striped table-bordered">
    <thead style="background-color:#950606; color:white;">
        <tr>
            <th>Fecha</th>
            <th>Minuta</th>
            <th>Nº Minuta</th>
            <th>Tipo</th>
            <th>Comprador</th>
            <th>Total</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventas as $v): ?>
            <tr>
                <td><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                <td><?= htmlspecialchars($v['minuta_nombre']) ?></td>
                <td><?= $v['numero_minuta'] ?></td>
                <td><?= $v['tipo_precio'] ?></td>
                <td><?= htmlspecialchars($v['comprador_nombre']) ?></td>
                <td>$<?= number_format($v['total'], 2) ?></td>
                <td><?= htmlspecialchars($v['observaciones'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Modal Nueva Venta -->
<div class="modal fade" id="modalVenta" tabindex="-1">
    <div class="modal-dialog">
        <form id="formVenta" method="post" action="index.php?page=ventas&action=store" class="modal-content">
            <div class="modal-header" style="background-color:#950606; color:#fff;">
                <h5 class="modal-title">Registrar Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Minuta</label>
                    <select name="minuta_id" id="minutaSelect" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($minutas as $m): ?>
                            <option value="<?= $m['id'] ?>"
                                data-normal="<?= $m['precio_normal'] ?>"
                                data-urgente="<?= $m['precio_urgente'] ?>">
                                <?= htmlspecialchars($m['nombre']) ?> (Nº int. <?= $m['numero'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Número de Minuta</label>
                    <input type="number" name="numero_minuta" id="numeroMinuta" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de precio</label>
                    <select name="tipo_precio" id="tipoPrecio" class="form-select">
                        <option value="normal">Normal</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Comprador</label>
                    <div class="input-group">
                        <input type="text" name="comprador_nombre" id="comprador" class="form-control" placeholder="Nombre del comprador">
                        <button type="button" class="btn btn-outline-secondary" id="btnConsumidorFinal">Consumidor Final</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="form-control"></textarea>
                </div>
                <div class="mt-3 fw-bold">
                    Total: $ <span id="totalCalculado">0.00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn" style="background-color:#E73121; color:#fff;">Guardar Venta</button>
            </div>
        </form>
    </div>
</div>
<?php
$contenido = ob_get_clean();
$scriptsExtra = '<script src="assets/js/venta.js"></script>';
require __DIR__ . '/../layout.php';
