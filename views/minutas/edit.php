<?php
ob_start();
?>
<h2 style="color:#950606;">Editar Minuta</h2>
<form method="post" action="index.php?page=minutas&action=update">
    <input type="hidden" name="id" value="<?= $minuta['id'] ?>">
    <div class="mb-3">
        <label class="form-label">Nombre / Descripción</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($minuta['nombre']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Número interno (no editable)</label>
        <input type="text" class="form-control" value="<?= $minuta['numero'] ?>" disabled>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Precio Normal</label>
            <input type="number" step="0.01" name="precio_normal" class="form-control" value="<?= $minuta['precio_normal'] ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Precio Urgente</label>
            <input type="number" step="0.01" name="precio_urgente" class="form-control" value="<?= $minuta['precio_urgente'] ?>" required>
        </div>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="activo" id="activo" class="form-check-input" <?= $minuta['activo'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="activo">Activo</label>
    </div>
    <button type="submit" class="btn" style="background-color:#950606; color:white;">Guardar Cambios</button>
    <a href="index.php?page=minutas" class="btn btn-secondary">Cancelar</a>
</form>
<?php
$contenido = ob_get_clean();
$scriptsExtra = '';
require __DIR__ . '/../layout.php';
