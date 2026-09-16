<?php

declare(strict_types=1);

class MinutaController
{
    private Minuta $minutaModel;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->minutaModel = new Minuta($db);
    }

    public function index(): void
    {
        $minutas = $this->minutaModel->todas();
        require __DIR__ . '/../views/minutas/index.php';
    }

    public function create(): void
    {
        $csrfToken = $this->csrfToken();
        $submissionToken = bin2hex(random_bytes(32));
        $this->limitarTokensDeEnvio();
        $_SESSION['minuta_submission_tokens'][$submissionToken] = 'pending';
        $this->limitarTokensDeEnvio();

        $old = $_SESSION['minuta_old'] ?? [];
        unset($_SESSION['minuta_old']);

        require __DIR__ . '/../views/minutas/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=minutas');
            exit;
        }

        $csrfToken = (string) ($_POST['csrf_token'] ?? '');
        if (!$this->csrfValido($csrfToken)) {
            $_SESSION['error'] = 'La sesion del formulario vencio. Intente nuevamente.';
            header('Location: index.php?page=minutas&action=create');
            exit;
        }

        $submissionToken = (string) ($_POST['submission_token'] ?? '');
        $submissionStatus = $_SESSION['minuta_submission_tokens'][$submissionToken] ?? null;
        if ($submissionStatus === 'processed') {
            $_SESSION['exito'] = 'La minuta ya fue agregada correctamente.';
            header('Location: index.php?page=minutas');
            exit;
        }
        if ($submissionStatus !== 'pending') {
            $_SESSION['error'] = 'El formulario ya fue enviado o vencio. Revise el listado antes de intentar nuevamente.';
            header('Location: index.php?page=minutas');
            exit;
        }

        $_SESSION['minuta_submission_tokens'][$submissionToken] = 'processing';

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $numeroRaw = trim((string) ($_POST['numero'] ?? ''));
        $precioNormalRaw = trim((string) ($_POST['precio_normal'] ?? ''));
        $precioUrgenteRaw = trim((string) ($_POST['precio_urgente'] ?? ''));
        $activo = isset($_POST['activo']) ? 1 : 0;

        $_SESSION['minuta_old'] = [
            'nombre' => $nombre,
            'numero' => $numeroRaw,
            'precio_normal' => $precioNormalRaw,
            'precio_urgente' => $precioUrgenteRaw,
            'activo' => $activo,
        ];

        $errores = [];
        $largoNombre = function_exists('mb_strlen') ? mb_strlen($nombre) : strlen($nombre);
        if ($nombre === '') {
            $errores[] = 'Ingrese el nombre de la minuta.';
        } elseif ($largoNombre > 100) {
            $errores[] = 'El nombre no puede superar los 100 caracteres.';
        }

        $numero = filter_var($numeroRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        if ($numero === false) {
            $errores[] = 'Ingrese un numero interno entero mayor que cero.';
        }

        $precioNormal = $this->validarPrecio($precioNormalRaw);
        $precioUrgente = $this->validarPrecio($precioUrgenteRaw);
        if ($precioNormal === null) {
            $errores[] = 'Ingrese un precio normal valido entre 0 y 99.999.999,99.';
        }
        if ($precioUrgente === null) {
            $errores[] = 'Ingrese un precio urgente valido entre 0 y 99.999.999,99.';
        }

        if ($errores) {
            unset($_SESSION['minuta_submission_tokens'][$submissionToken]);
            $_SESSION['error'] = implode(' ', $errores);
            header('Location: index.php?page=minutas&action=create');
            exit;
        }

        try {
            $this->minutaModel->crear([
                'nombre' => $nombre,
                'numero' => (int) $numero,
                'precio_normal' => $precioNormal,
                'precio_urgente' => $precioUrgente,
                'activo' => $activo,
            ]);
            $_SESSION['minuta_submission_tokens'][$submissionToken] = 'processed';
            unset($_SESSION['minuta_old']);
            $_SESSION['exito'] = 'Minuta agregada correctamente. Ya esta disponible en el listado.';
            header('Location: index.php?page=minutas');
            exit;
        } catch (DomainException $e) {
            unset($_SESSION['minuta_submission_tokens'][$submissionToken]);
            $_SESSION['error'] = $e->getMessage();
        } catch (PDOException $e) {
            unset($_SESSION['minuta_submission_tokens'][$submissionToken]);
            if ($e->getCode() === '23000') {
                $_SESSION['error'] = 'Ya existe una minuta con ese numero interno.';
            } else {
                $_SESSION['error'] = 'No se pudo agregar la minuta. Intente nuevamente.';
            }
        } catch (Throwable $e) {
            unset($_SESSION['minuta_submission_tokens'][$submissionToken]);
            $_SESSION['error'] = 'No se pudo agregar la minuta. Intente nuevamente.';
        }

        header('Location: index.php?page=minutas&action=create');
        exit;
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $minuta = $this->minutaModel->porId($id);
        if (!$minuta) {
            $_SESSION['error'] = 'Minuta no encontrada.';
            header('Location: index.php?page=minutas');
            exit;
        }
        require __DIR__ . '/../views/minutas/edit.php';
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $precio_normal = (float) ($_POST['precio_normal'] ?? 0);
        $precio_urgente = (float) ($_POST['precio_urgente'] ?? 0);
        // campo "activo" opcional
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($id <= 0 || $nombre === '') {
            $_SESSION['error'] = 'Datos inválidos.';
            header('Location: index.php?page=minutas');
            exit;
        }

        $sql = "UPDATE minutas SET nombre=:n, precio_normal=:pn, precio_urgente=:pu, activo=:a WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':n'  => $nombre,
            ':pn' => $precio_normal,
            ':pu' => $precio_urgente,
            ':a'  => $activo,
            ':id' => $id
        ]);
        $_SESSION['exito'] = 'Minuta actualizada correctamente.';
        header('Location: index.php?page=minutas');
        exit;
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['csrf_token'];
    }

    private function csrfValido(string $token): bool
    {
        return $token !== ''
            && isset($_SESSION['csrf_token'])
            && hash_equals((string) $_SESSION['csrf_token'], $token);
    }

    private function validarPrecio(string $valor): ?string
    {
        if (!preg_match('/^(?:0|[1-9]\\d{0,7})(?:[.,]\\d{1,2})?$/', $valor)) {
            return null;
        }

        $numero = (float) str_replace(',', '.', $valor);
        if (!is_finite($numero) || $numero < 0 || $numero > 99999999.99) {
            return null;
        }

        return number_format($numero, 2, '.', '');
    }

    private function limitarTokensDeEnvio(): void
    {
        if (!isset($_SESSION['minuta_submission_tokens']) || !is_array($_SESSION['minuta_submission_tokens'])) {
            $_SESSION['minuta_submission_tokens'] = [];
            return;
        }

        if (count($_SESSION['minuta_submission_tokens']) > 10) {
            $_SESSION['minuta_submission_tokens'] = array_slice(
                $_SESSION['minuta_submission_tokens'],
                -10,
                null,
                true
            );
        }
    }
}
