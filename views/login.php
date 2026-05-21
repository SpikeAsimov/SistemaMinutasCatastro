<?php
$contenido = '
<form method="post" action="index.php?page=login" class="col-md-4 mx-auto mt-5">
    <h3 class="text-center mb-4" style="color:#950606;">Iniciar Sesión</h3>
    <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input type="text" name="usuario" id="usuario" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" id="password" class="form-control" required>
    </div>
    <button type="submit" class="btn w-100" style="background-color:#950606; color:#fff;">Ingresar</button>
</form>';
$scriptsExtra = '';
require __DIR__ . '/layout.php';