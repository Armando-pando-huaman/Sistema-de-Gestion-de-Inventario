<?php
require_once '../models/Producto.php';
require_once '../models/Movimiento.php';
require_once '../models/Venta.php';

class DashboardController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Auth&a=login");
            exit;
        }

        $productoModel = new Producto();
        $movimientoModel = new Movimiento();
        $ventaModel = new Venta();

        // Estadísticas básicas
        $totalProductos = $productoModel->contar();
        $totalMovimientos = $movimientoModel->contar();
        $productosBajos = $productoModel->stockBajo();
        $ultimosMovimientos = $movimientoModel->getAll();
        $inicioGrafica = date('Y-m-d H:00:00', strtotime('-23 hours'));
        $finGrafica = date('Y-m-d H:00:00', strtotime('+1 hour'));
        $movimientosPorHora = $movimientoModel->resumenPorHora($inicioGrafica, $finGrafica);
        $totalVendidoHoy = (float) $ventaModel->totalVendidoHoy();
        $ventasHoy = (int) $ventaModel->ventasHoy();

        // Productos próximos a vencer (30 días)
        $porVencer = 0;
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->query("SELECT COUNT(DISTINCT producto_id) FROM lotes
                WHERE stock>0 AND vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $porVencer = $stmt->fetchColumn();
        } catch (Exception $e) {
            $porVencer = 0;
        }

        require_once '../views/dashboard/index.php';
    }
}
?>
