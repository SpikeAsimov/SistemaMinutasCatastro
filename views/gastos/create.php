<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Registrar Nuevo Gasto</h1>
        <p class="page-subtitle">Cargue fecha, comprobante e importe del egreso.</p>
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
        <form method="post" action="index.php?page=gastos&action=store" class="needs-validation" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?= date('Y-m-d') ?>" placeholder="Fecha" required>
                        <label for="fecha">Fecha</label>
                        <div class="invalid-feedback">Seleccione una fecha.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="numero_factura" id="numero_factura" class="form-control" maxlength="50" placeholder="Numero de factura" required>
                        <label for="numero_factura">Numero de factura</label>
                        <div class="invalid-feedback">Ingrese el numero de factura.</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-floating">
                        <input type="text" name="descripcion" id="descripcion" class="form-control" maxlength="255" placeholder="Descripcion" required>
                        <label for="descripcion">Descripcion</label>
                        <div class="invalid-feedback">Ingrese una descripcion.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" step="0.01" min="0.01" name="importe" id="importe" class="form-control" placeholder="Importe" required>
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
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar gasto
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$scriptsExtra = '';
require __DIR__ . '/../layout.php';
