<?php

declare(strict_types=1);
session_start();

// Mostrar todos los errores para depurar (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Minuta.php';
require_once __DIR__ . '/models/Venta.php';
require_once __DIR__ . '/models/Gasto.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/VentaController.php';
require_once __DIR__ . '/controllers/GastoController.php';

// Carga condicional con verificación explícita
$minutaCtrlFile = __DIR__ . '/controllers/MinutaController.php';
if (file_exists($minutaCtrlFile)) {
    require_once $minutaCtrlFile;
} else {
    die('Error: No se encuentra el archivo controllers/MinutaController.php');
}

if (!class_exists('MinutaController')) {
    die('Error: La clase MinutaController no está definida. Revisa el archivo.');
}

$page = $_GET['page'] ?? 'ventas';
$action = $_GET['action'] ?? null;
$loggedIn = isset($_SESSION['usuario_id']);

// Rutas públicas
if ($page === 'login') {
    $auth = new AuthController(getDB());
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->loginPost();
    } else {
        $auth->loginForm();
    }
    exit;
}
if ($page === 'logout') {
    (new AuthController(getDB()))->logout();
    exit;
}

// Protección de sesión
if (!$loggedIn) {
    header('Location: index.php?page=login');
    exit;
}

// Instancias de controladores (con verificación adicional)
try {
    $ventaCtrl = new VentaController(getDB());
    $minutaCtrl = new MinutaController(getDB());
    $gastoCtrl = new GastoController(getDB());
} catch (Throwable $e) {
    die('Error al instanciar controladores: ' . $e->getMessage());
}

// Enrutamiento
if ($page === 'ventas') {
    if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ventaCtrl->store();
    } elseif ($action === 'reporte') {
        $ventaCtrl->reporte();
    } elseif ($action === 'detalle') {
        $ventaCtrl->detalle();
    } elseif ($action === 'edit') {
        $ventaCtrl->edit();
    } elseif ($action === 'update') {
        $ventaCtrl->update();
    } else {
        $ventaCtrl->index();
    }
} elseif ($page === 'minutas') {
    if ($action === 'create') {
        $minutaCtrl->create();
    } elseif ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $minutaCtrl->store();
    } elseif ($action === 'edit') {
        $minutaCtrl->edit();
    } elseif ($action === 'update') {
        $minutaCtrl->update();
    } else {
        $minutaCtrl->index();
    }
} elseif ($page === 'gastos') {
    if ($action === 'create') {
        $gastoCtrl->create();
    } elseif ($action === 'store') {
        $gastoCtrl->store();
    } elseif ($action === 'edit') {
        $gastoCtrl->edit();
    } elseif ($action === 'update') {
        $gastoCtrl->update();
    } elseif ($action === 'reporte') {
        $gastoCtrl->reporte();
    } else {
        $gastoCtrl->index();
    }
} else {
    header('Location: index.php?page=ventas');
    exit;
}
