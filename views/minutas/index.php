<?php
ob_start();
?>
<h2 style="color:#950606;">Administrar Minutas</h2>
<a href="index.php?page=ventas" class="btn btn-sm" style="background-color:#E73121; color:#fff;">← Volver a Ventas</a>
<table class="table table-bordered mt-3">
    <thead style="background-color:#950606; color:white;">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Nº Interno</th>
            <th>Precio Normal</th>
            <th>Precio Urgente</th>
            <th>Activo</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($minutas as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['nombre']) ?></td>
                <td><?= $m['numero'] ?></td>
                <td>$<?= number_format($m['precio_normal'], 2) ?></td>
                <td>$<?= number_format($m['precio_urgente'], 2) ?></td>
                <td><?= $m['activo'] ? 'Sí' : 'No' ?></td>
                <td><a href="index.php?page=minutas&action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$contenido = ob_get_clean();
$scriptsExtra = '';
require __DIR__ . '/../layout.php';
