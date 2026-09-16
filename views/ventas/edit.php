<?php
declare(strict_types=1);
ob_start();
$editComprador = (string)($formData['comprador_nombre'] ?? $venta['comprador_nombre']);
$editObservaciones = (string)($formData['observaciones'] ?? ($venta['observaciones'] ?? ''));
$editFormaPago = array_key_exists('forma_pago', $formData)
    ? $formData['forma_pago']
    : ($venta['forma_pago'] ?? null);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Editar Venta #<?= (int) $venta['id'] ?></h1>
        <p class="page-subtitle">Actualice comprador, forma de pago, observaciones e items de la venta.</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<form id="formEditarVenta" method="post" action="index.php?page=ventas&action=update" class="needs-validation" novalidate>
    <input type="hidden" name="venta_id" value="<?= (int) $venta['id'] ?>">

    <div class="app-card mb-3">
        <div class="app-card-header">
            <h2 class="h5 mb-0 fw-bold">Datos generales</h2>
        </div>
        <div class="app-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" name="comprador_nombre" id="compradorNombre" class="form-control"
                            value="<?= htmlspecialchars($editComprador) ?>" placeholder="Comprador" required>
                        <label for="compradorNombre">Comprador</label>
                        <div class="invalid-feedback">Ingrese el comprador.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <select name="forma_pago" id="formaPago" class="form-select">
                            <?php if (($venta['forma_pago'] ?? null) === null): ?>
                                <option value="" <?= $editFormaPago === null || $editFormaPago === '' ? 'selected' : '' ?>>Sin especificar (registro histórico)</option>
                            <?php else: ?>
                                <option value="">Seleccionar forma de pago</option>
                            <?php endif; ?>
                            <option value="efectivo" <?= $editFormaPago === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                            <option value="transferencia" <?= $editFormaPago === 'transferencia' ? 'selected' : '' ?>>Transferencia / depósito bancario</option>
                        </select>
                        <label for="formaPago">Forma de pago</label>
                    </div>
                    <div class="form-text">La categoría de transferencia incluye depósitos y no verifica acreditaciones.</div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <textarea name="observaciones" id="observaciones" class="form-control" placeholder="Observaciones" style="height: 58px;"><?= htmlspecialchars($editObservaciones) ?></textarea>
                        <label for="observaciones">Observaciones</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-card mb-3">
        <div class="app-card-header">
            <h2 class="h5 mb-0 fw-bold">Items actuales</h2>
        </div>
        <div class="table-wrap">
            <table class="table table-sm app-table" id="tablaItems">
                <thead>
                    <tr>
                        <th>Minuta</th>
                        <th>Numero</th>
                        <th>Tipo</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Quitar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $it): ?>
                        <tr data-index="<?= $index ?>">
                            <td class="fw-semibold"><?= htmlspecialchars($it['minuta_nombre']) ?></td>
                            <td><?= (int) $it['numero_minuta'] ?></td>
                            <td><?= htmlspecialchars($it['tipo_precio']) ?></td>
                            <td class="text-end money">$<?= number_format((float)$it['precio_unitario'], 2) ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-item" data-bs-toggle="tooltip" title="Quitar item">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="app-card h-100">
                <div class="app-card-header">
                    <h2 class="h5 mb-0 fw-bold">Agregar nuevo item</h2>
                </div>
                <div class="app-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-floating">
                                <select id="minutaSelect" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($minutas as $m): ?>
                                        <option value="<?= $m['id'] ?>"
                                            data-normal="<?= $m['precio_normal'] ?>"
                                            data-urgente="<?= $m['precio_urgente'] ?>"
                                            data-nombre="<?= htmlspecialchars($m['nombre'], ENT_QUOTES) ?>">
                                            <?= htmlspecialchars($m['nombre']) ?> (Nro. int. <?= (int) $m['numero'] ?>)
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
                                    <option value="normal">Normal</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                                <label for="tipoPrecio">Tipo</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" id="btnAgregarItem" class="btn btn-brand-dark w-100">
                                <i class="fa-solid fa-plus me-1"></i> Agregar item
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="app-card h-100">
                <div class="app-card-header">
                    <h2 class="h5 mb-0 fw-bold">Nuevos items</h2>
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
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <a href="index.php?page=ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-xmark me-1"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-brand">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
        </button>
    </div>
</form>

<script>
const itemsExistentes = <?= json_encode(array_values(array_map(function($it) {
    return [
        'minuta_id' => $it['minuta_id'],
        'numero_minuta' => $it['numero_minuta'],
        'tipo_precio' => $it['tipo_precio'],
        'precio' => (float) $it['precio_unitario'],
        'nombre_minuta' => $it['minuta_nombre']
    ];
}, $items)), JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const minutaSelect = document.getElementById('minutaSelect');
    const numeroMinutaInput = document.getElementById('numeroMinuta');
    const tipoPrecioSelect = document.getElementById('tipoPrecio');
    const btnAgregar = document.getElementById('btnAgregarItem');
    const tablaCarritoBody = document.getElementById('tablaCarrito').querySelector('tbody');
    const tablaItems = document.getElementById('tablaItems');
    const form = document.getElementById('formEditarVenta');
    let carritoNuevos = [];

    function money(value) {
        return '$' + Number(value || 0).toFixed(2);
    }

    function notify(icon, title, text) {
        Swal.fire({ icon, title, text, confirmButtonColor: '#950606' });
    }

    function td(text, className) {
        const cell = document.createElement('td');
        cell.textContent = text;
        if (className) cell.className = className;
        return cell;
    }

    function renderCarrito() {
        tablaCarritoBody.replaceChildren();
        if (carritoNuevos.length === 0) {
            const row = document.createElement('tr');
            const empty = td('No hay items nuevos.', 'text-center text-muted py-4');
            empty.colSpan = 5;
            row.appendChild(empty);
            tablaCarritoBody.appendChild(row);
            return;
        }

        carritoNuevos.forEach((item, idx) => {
            const row = document.createElement('tr');
            row.appendChild(td(item.nombreMinuta, 'fw-semibold'));
            row.appendChild(td(item.numero));
            row.appendChild(td(item.tipo === 'urgente' ? 'Urgente' : 'Normal'));
            row.appendChild(td(money(item.precio), 'text-end money'));
            const actions = td('', 'text-end');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-danger btnEliminarNuevo';
            btn.dataset.index = idx;
            btn.innerHTML = '<i class="fa-solid fa-trash"></i>';
            actions.appendChild(btn);
            row.appendChild(actions);
            tablaCarritoBody.appendChild(row);
        });

        document.querySelectorAll('.btnEliminarNuevo').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.index, 10);
                Swal.fire({
                    icon: 'question',
                    title: 'Quitar item',
                    text: 'Esta minuta se quitara de los nuevos items.',
                    showCancelButton: true,
                    confirmButtonText: 'Si, quitar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#950606'
                }).then(result => {
                    if (result.isConfirmed) {
                        carritoNuevos.splice(idx, 1);
                        renderCarrito();
                    }
                });
            });
        });
    }

    btnAgregar.addEventListener('click', function() {
        const minutaId = minutaSelect.value;
        const numero = parseInt(numeroMinutaInput.value, 10);
        const tipo = tipoPrecioSelect.value;

        if (!minutaId || Number.isNaN(numero) || numero < 1) {
            notify('warning', 'Datos incompletos', 'Seleccione una minuta y un numero valido.');
            return;
        }

        const existeEnExistentes = itemsExistentes.some((it, idx) => {
            const fila = tablaItems.querySelector('tbody tr[data-index="' + idx + '"]');
            if (!fila || fila.dataset.removed === '1') return false;
            return it.minuta_id == minutaId && it.numero_minuta == numero;
        });
        const existeEnCarrito = carritoNuevos.some(it => it.minutaId == minutaId && it.numero == numero);

        if (existeEnExistentes || existeEnCarrito) {
            notify('warning', 'Minuta duplicada', 'Esa minuta con ese numero ya esta en la venta.');
            return;
        }

        const option = minutaSelect.options[minutaSelect.selectedIndex];
        const precioNormal = parseFloat(option.dataset.normal) || 0;
        const precioUrgente = parseFloat(option.dataset.urgente) || 0;
        const precio = tipo === 'urgente' ? precioUrgente : precioNormal;

        carritoNuevos.push({
            minutaId: minutaId,
            numero: numero,
            tipo: tipo,
            precio: precio,
            nombreMinuta: option.dataset.nombre || option.textContent.trim()
        });

        numeroMinutaInput.value = '';
        minutaSelect.selectedIndex = 0;
        tipoPrecioSelect.value = 'normal';
        renderCarrito();
    });

    tablaItems.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-eliminar-item');
        if (!btn) return;
        const fila = btn.closest('tr');
        Swal.fire({
            icon: 'question',
            title: 'Quitar item',
            text: 'El item se quitara al guardar los cambios.',
            showCancelButton: true,
            confirmButtonText: 'Si, quitar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#950606'
        }).then(result => {
            if (result.isConfirmed && fila) {
                fila.dataset.removed = '1';
                fila.classList.add('table-danger');
                fila.style.opacity = '.55';
            }
        });
    });

    form.addEventListener('submit', function(e) {
        document.querySelectorAll('.item-input').forEach(el => el.remove());

        function crearInput(name, value) {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = name;
            i.value = value;
            i.classList.add('item-input');
            return i;
        }

        let countItems = 0;
        tablaItems.querySelectorAll('tbody tr').forEach((row) => {
            if (row.dataset.removed === '1') return;
            const idx = parseInt(row.dataset.index, 10);
            if (Number.isNaN(idx) || idx >= itemsExistentes.length) return;
            const it = itemsExistentes[idx];
            form.appendChild(crearInput('items_minuta_id[]', it.minuta_id));
            form.appendChild(crearInput('items_numero[]', it.numero_minuta));
            form.appendChild(crearInput('items_tipo[]', it.tipo_precio));
            form.appendChild(crearInput('items_precio[]', it.precio));
            countItems++;
        });

        carritoNuevos.forEach(it => {
            form.appendChild(crearInput('items_minuta_id[]', it.minutaId));
            form.appendChild(crearInput('items_numero[]', it.numero));
            form.appendChild(crearInput('items_tipo[]', it.tipo));
            form.appendChild(crearInput('items_precio[]', it.precio));
            countItems++;
        });

        if (countItems === 0) {
            e.preventDefault();
            notify('warning', 'Venta sin items', 'Debe haber al menos un item en la venta.');
            return false;
        }
    });

    renderCarrito();
});
</script>

<?php
$contenido = ob_get_clean();
$scriptsExtra = '';
require __DIR__ . '/../layout.php';
