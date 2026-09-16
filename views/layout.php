<?php
$pageActual = $_GET['page'] ?? 'ventas';
$esGastos = $pageActual === 'gastos';
$themeConfirmColor = $esGastos ? '#436947' : '#950606';
$flashError = $_SESSION['error'] ?? null;
$flashExito = $_SESSION['exito'] ?? null;
unset($_SESSION['error'], $_SESSION['exito']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catastro Chilecito - Control de Minutas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>

<body class="page-<?= htmlspecialchars($pageActual) ?>">
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar fixed-top">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php?page=ventas">
                <span class="brand-mark"><i class="fa-solid fa-receipt"></i></span>
                <span>Catastro Chilecito</span>
            </a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Abrir menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-lg-4 me-auto gap-lg-1">
                        <li class="nav-item">
                            <a href="index.php?page=ventas" class="nav-link <?= $pageActual === 'ventas' ? 'active' : '' ?>">
                                <i class="fa-solid fa-cash-register me-1"></i> Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=minutas" class="nav-link <?= $pageActual === 'minutas' ? 'active' : '' ?>">
                                <i class="fa-solid fa-file-lines me-1"></i> Minutas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=gastos" class="nav-link <?= $pageActual === 'gastos' ? 'active' : '' ?>">
                                <i class="fa-solid fa-wallet me-1"></i> Gastos
                            </a>
                        </li>
                    </ul>
                    <div class="navbar-user d-flex align-items-center gap-3 mt-3 mt-lg-0">
                        <span class="text-white-50 small">
                            <i class="fa-solid fa-user me-1"></i><?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                        </span>
                        <a href="index.php?page=logout" class="btn btn-outline-light btn-sm">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Salir
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <main class="app-shell">
        <div class="container-fluid px-3 px-lg-4">
            <?= $contenido ?? '' ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.AppFlash = {
            success: <?= json_encode($flashExito, JSON_UNESCAPED_UNICODE) ?>,
            error: <?= json_encode($flashError, JSON_UNESCAPED_UNICODE) ?>
        };
        window.AppTheme = {
            confirmColor: <?= json_encode($themeConfirmColor) ?>
        };

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });

            document.querySelectorAll('.needs-validation').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Revisar formulario',
                            text: 'Complete los campos obligatorios antes de continuar.',
                            confirmButtonColor: window.AppTheme.confirmColor
                        });
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            if (window.AppFlash.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Operacion exitosa',
                    text: window.AppFlash.success,
                    timer: 2200,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }

            if (window.AppFlash.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo completar',
                    text: window.AppFlash.error,
                    confirmButtonColor: window.AppTheme.confirmColor
                });
            }
        });
    </script>
    <?= $scriptsExtra ?? '' ?>
</body>

</html>
