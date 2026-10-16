<?php
require_once '../models/Venta.php';
require_once '../models/Producto.php';

class VentaController {

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Auth&a=login");
            exit;
        }
    }

    /**
     * Punto de venta (pantalla principal)
     */
    public function index() {
        $this->checkAuth();
        
        $productoModel = new Producto();
        $productos = $productoModel->getAll();
        
        $ventaModel = new Venta();
        
        // Si viene un POST, registrar la venta
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $carrito = json_decode($_POST['carrito'] ?? '[]', true);
            $total = floatval($_POST['total'] ?? 0);
            $cliente = trim($_POST['cliente'] ?? 'Cliente General');
            
            if (empty($carrito)) {
                $_SESSION['error'] = "El carrito está vacío.";
            } else {
                $venta_id = $ventaModel->registrar($cliente, $total, $_SESSION['user_id'], $carrito);
                if ($venta_id) {
                    $_SESSION['success'] = "Venta #$venta_id registrada por S/ " . number_format($total, 2);
                    header("Location: index.php?c=Venta&a=index");
                    exit;
                } else {
                    $_SESSION['error'] = "Error al registrar la venta. Verifica el stock.";
                }
            }
        }
        
        $ventas = $ventaModel->getAll();
        require_once '../views/ventas/index.php';
    }

    /**
     * Ver detalle de una venta específica
     */
    public function ver() {
        $this->checkAuth();
        
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            header("Location: index.php?c=Venta&a=index");
            exit;
        }
        
        $ventaModel = new Venta();
        $venta = $ventaModel->getById($id);
        $detalle = $ventaModel->getDetalle($id);
        
        require_once '../views/ventas/ver.php';
    }

    /**
     * Historial de ventas
     */
    public function historial() {
        $this->checkAuth();
        
        $ventaModel = new Venta();
        $ventas = $ventaModel->getAll();
        
        require_once '../views/ventas/historial.php';
    }
}
?>