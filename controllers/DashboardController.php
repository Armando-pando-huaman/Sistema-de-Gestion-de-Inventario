<?php
require_once '../models/Producto.php';
require_once '../models/Movimiento.php';

class DashboardController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Auth&a=login");
            exit;
        }

        $productoModel = new Producto();
        $movimientoModel = new Movimiento();

        // Estadísticas básicas
        $totalProductos = $productoModel->contar();
        $totalMovimientos = $movimientoModel->contar();
        $productosBajos = $productoModel->stockBajo();
        $ultimosMovimientos = $movimientoModel->getAll();
        $movimientosPorDia = $movimientoModel->resumenPorDia(date('Y-m-d', strtotime('-6 days')), date('Y-m-d', strtotime('+1 day')));

        // Productos próximos a vencer (30 días)
        $porVencer = 0;
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->query("SELECT COUNT(*) FROM productos 
                WHERE fecha_vencimiento IS NOT NULL 
                AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $porVencer = $stmt->fetchColumn();
        } catch (Exception $e) {
            $porVencer = 0;
        }

        require_once '../views/dashboard/index.php';
    }
}
?>
