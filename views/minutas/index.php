<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Administrar Minutas</h1>
        <p class="page-subtitle">Precios, numeros internos y disponibilidad para la venta.</p>
    </div>
    <div class="page-actions d-flex flex-wrap gap-2">
        <a href="index.php?page=minutas&action=create" class="btn btn-brand">
            <i class="fa-solid fa-plus me-1"></i> Agregar minutas
        </a>
        <a href="index.php?page=ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a ventas
        </a>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0 fw-bold">Listado de minutas</h2>
        <span class="text-muted small"><?= count($minutas) ?> registros</span>
    </div>
    <div class="table-wrap">
        <table class="table table-hover app-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Numero interno</th>
                    <th class="text-end">Precio normal</th>
                    <th class="text-end">Precio urgente</th>
                    <th class="text-center">Activo</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($minutas as $m): ?>
                    <tr>
                        <td><?= (int) $m['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($m['nombre']) ?></td>
                        <td><?= (int) $m['numero'] ?></td>
                        <td class="text-end money">$<?= number_format((float)$m['precio_normal'], 2) ?></td>
                        <td class="text-end money">$<?= number_format((float)$m['precio_urgente'], 2) ?></td>
                        <td class="text-center">
                            <span class="status-pill <?= $m['activo'] ? 'on' : 'off' ?>">
                                <i class="fa-solid <?= $m['activo'] ? 'fa-check' : 'fa-ban' ?>"></i>
                                <?= $m['activo'] ? 'Si' : 'No' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="index.php?page=minutas&action=edit&id=<?= (int) $m['id'] ?>" class="btn btn-sm btn-outline-brand" data-bs-toggle="tooltip" title="Editar minuta">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$scriptsExtra = '';
require __DIR__ . '/../layout.php';
