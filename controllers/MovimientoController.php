<?php
require_once '../models/movimiento.php';
require_once '../models/producto.php';
class MovimientoController {
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: index.php?c=Auth&a=login'); exit; }
        $movModel = new Movimiento();
        $prodModel = new Producto();
        $_SESSION['mov_csrf'] = $_SESSION['mov_csrf'] ?? bin2hex(random_bytes(32));
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['mov_csrf'], $_POST['csrf'])) throw new RuntimeException('La sesión del formulario venció. Recarga la página.');
                $movModel->registrar($_POST['producto_id'] ?? null, $_POST['tipo'] ?? '', $_POST['cantidad'] ?? null, $_POST['motivo'] ?? '', $_SESSION['user_id'],$_POST);
                $_SESSION['mov_success'] = 'Movimiento registrado y stock actualizado.';
                header('Location: index.php?c=Movimiento&a=index'); exit;
            } catch (PDOException $e) { error_log($e->getMessage()); $error = 'No se pudo registrar el movimiento. Inténtalo nuevamente.'; }
            catch (RuntimeException $e) { $error = $e->getMessage(); }
            catch (Throwable $e) { error_log($e->getMessage()); $error = 'No se pudo registrar el movimiento. Inténtalo nuevamente.'; }
        }
        $filtros = ['clase' => is_string($_GET['clase'] ?? null) ? $_GET['clase'] : '', 'producto_id' => filter_var($_GET['producto_id'] ?? '', FILTER_VALIDATE_INT) ?: '', 'desde' => '', 'hasta' => ''];
        foreach (['desde', 'hasta'] as $key) {
            $value = $_GET[$key] ?? '';
            if (is_string($value) && $value !== '') {
                $date = DateTime::createFromFormat('!Y-m-d', $value);
                if ($date && $date->format('Y-m-d') === $value) $filtros[$key] = $value;
                else $error = 'La fecha del filtro no es válida.';
            }
        }
        if ($filtros['desde'] && $filtros['hasta'] && $filtros['desde'] > $filtros['hasta']) {
            $error = 'La fecha inicial no puede superar la fecha final.';
            $filtros['hasta'] = $filtros['desde'];
        }
        $historial = $movModel->historial($filtros, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $productos = $prodModel->getAll();
        $motivos = Movimiento::motivos();
        $lotes = (new Database())->getConnection()->query('SELECT l.*,p.nombre FROM lotes l JOIN productos p ON p.id=l.producto_id WHERE l.stock>0 ORDER BY p.nombre,l.vencimiento')->fetchAll();
        require '../views/movimientos/index.php';
    }
}
