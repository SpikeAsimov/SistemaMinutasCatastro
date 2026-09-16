<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Editar Minuta</h1>
        <p class="page-subtitle">Actualice descripcion, precios y estado de disponibilidad.</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=minutas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <h2 class="h5 mb-0 fw-bold">Datos de la minuta</h2>
    </div>
    <div class="app-card-body">
        <form method="post" action="index.php?page=minutas&action=update" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?= (int) $minuta['id'] ?>">
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-floating">
                        <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($minuta['nombre']) ?>" placeholder="Nombre / Descripcion" required>
                        <label for="nombre">Nombre / Descripcion</label>
                        <div class="invalid-feedback">Ingrese el nombre de la minuta.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" id="numero" class="form-control" value="<?= (int) $minuta['numero'] ?>" placeholder="Numero interno" disabled>
                        <label for="numero">Numero interno</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" step="0.01" min="0" name="precio_normal" id="precio_normal" class="form-control" value="<?= $minuta['precio_normal'] ?>" placeholder="Precio normal" required>
                        <label for="precio_normal">Precio normal</label>
                        <div class="invalid-feedback">Ingrese un precio normal valido.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" step="0.01" min="0" name="precio_urgente" id="precio_urgente" class="form-control" value="<?= $minuta['precio_urgente'] ?>" placeholder="Precio urgente" required>
                        <label for="precio_urgente">Precio urgente</label>
                        <div class="invalid-feedback">Ingrese un precio urgente valido.</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="activo" id="activo" class="form-check-input" <?= $minuta['activo'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="activo">Minuta activa para ventas</label>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
                <a href="index.php?page=minutas" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-brand">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$scriptsExtra = '';
require __DIR__ . '/../layout.php';
