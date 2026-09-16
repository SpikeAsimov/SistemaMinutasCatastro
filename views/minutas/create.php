<?php ob_start(); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Agregar minuta</h1>
        <p class="page-subtitle">Cree un nuevo tipo de minuta para incorporarlo al circuito de ventas.</p>
    </div>
    <div class="page-actions">
        <a href="index.php?page=minutas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <h2 class="h5 mb-0 fw-bold">Datos de la nueva minuta</h2>
    </div>
    <div class="app-card-body">
        <form method="post" action="index.php?page=minutas&action=store" id="formCrearMinuta" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
            <input type="hidden" name="submission_token" value="<?= htmlspecialchars($submissionToken, ENT_QUOTES) ?>">

            <div class="row g-3">
                <div class="col-12">
                    <div class="form-floating">
                        <input type="text" name="nombre" id="nombre" class="form-control" maxlength="100" value="<?= htmlspecialchars((string) ($old['nombre'] ?? '')) ?>" placeholder="Nombre / Descripcion" required autofocus>
                        <label for="nombre">Nombre / Descripcion</label>
                        <div class="invalid-feedback">Ingrese un nombre de hasta 100 caracteres.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" name="numero" id="numero" class="form-control" min="1" max="2147483647" step="1" value="<?= htmlspecialchars((string) ($old['numero'] ?? '')) ?>" placeholder="Numero interno" required>
                        <label for="numero">Numero interno</label>
                        <div class="invalid-feedback">Ingrese un numero entero mayor que cero.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" name="precio_normal" id="precio_normal" class="form-control" min="0" max="99999999.99" step="0.01" value="<?= htmlspecialchars((string) ($old['precio_normal'] ?? '')) ?>" placeholder="Precio normal" required>
                        <label for="precio_normal">Precio normal</label>
                        <div class="invalid-feedback">Ingrese un precio normal valido.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="number" name="precio_urgente" id="precio_urgente" class="form-control" min="0" max="99999999.99" step="0.01" value="<?= htmlspecialchars((string) ($old['precio_urgente'] ?? '')) ?>" placeholder="Precio urgente" required>
                        <label for="precio_urgente">Precio urgente</label>
                        <div class="invalid-feedback">Ingrese un precio urgente valido.</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="activo" id="activo" class="form-check-input" <?= !isset($old['activo']) || (int) $old['activo'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="activo">Minuta activa para ventas</label>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border mt-4 mb-0" role="note">
                <i class="fa-solid fa-circle-info me-1 text-muted"></i>
                Esta operacion solo agrega el tipo al catalogo; no registra una venta ni emite o imprime una minuta.
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
                <a href="index.php?page=minutas" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" id="btnGuardarMinuta" class="btn btn-brand">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar minuta
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$scriptsExtra = <<<'HTML'
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCrearMinuta');
    const submitButton = document.getElementById('btnGuardarMinuta');
    if (!form || !submitButton) {
        return;
    }

    form.addEventListener('submit', function () {
        if (!form.checkValidity() || submitButton.disabled) {
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';
    });
});
</script>
HTML;
require __DIR__ . '/../layout.php';
