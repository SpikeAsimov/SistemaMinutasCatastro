<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Editar Gasto #<?= (int) $gasto['id'] ?></h1>
        <p class="page-subtitle">Actualice los datos del egreso registrado.</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=gastos" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <h2 class="h5 mb-0 fw-bold">Datos del gasto</h2>
    </div>
    <div class="app-card-body">
        <form method="post" action="index.php?page=gastos&action=update" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?= (int) $gasto['id'] ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?= htmlspecialchars($gasto['fecha']) ?>" placeholder="Fecha" required>
                        <label for="fecha">Fecha</label>
                        <div class="invalid-feedback">Seleccione una fecha.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="numero_factura" id="numero_factura" class="form-control" value="<?= htmlspecialchars($gasto['numero_factura']) ?>" maxlength="50" placeholder="Numero de factura" required>
                        <label for="numero_factura">Numero de factura</label>
                        <div class="invalid-feedback">Ingrese el numero de factura.</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-floating">
                        <input type="text" name="descripcion" id="descripcion" class="form-control" value="<?= htmlspecialchars($gasto['descripcion']) ?>" maxlength="255" placeholder="Descripcion" required>
                        <label for="descripcion">Descripcion</label>
                        <div class="invalid-feedback">Ingrese una descripcion.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" step="0.01" min="0.01" name="importe" id="importe" class="form-control" value="<?= htmlspecialchars((string) $gasto['importe']) ?>" placeholder="Importe" required>
                        <label for="importe">Importe ($)</label>
                        <div class="invalid-feedback">Ingrese un importe mayor a 0.</div>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
                <a href="index.php?page=gastos" class="btn btn-outline-secondary">
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
