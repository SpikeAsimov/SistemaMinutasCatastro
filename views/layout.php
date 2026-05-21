<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catastro Chilecito - Control de Minutas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body style="background-color: #F2F0EF;">
<nav class="navbar navbar-expand-lg" style="background-color: #950606;">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php?page=ventas">Catastro Chilecito</a>
        <?php if (isset($_SESSION['usuario_id'])): ?>
        <div class="ms-auto">
            <span class="text-white me-3"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
            <a href="index.php?page=logout" class="btn btn-outline-light btn-sm">Salir</a>
        </div>
        <?php endif; ?>
    </div>
</nav>
<div class="container mt-4">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['exito'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['exito']) ?></div>
        <?php unset($_SESSION['exito']); ?>
    <?php endif; ?>
    <?= $contenido ?? '' ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= $scriptsExtra ?? '' ?>
</body>
</html>