<?php
require_once '../models/Reporte.php';

class ReporteController {

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Auth&a=login");
            exit;
        }
        // Solo admins ven reportes completos
        if ($_SESSION['user_rol'] !== 'admin') {
            $_SESSION['error'] = "Solo administradores pueden ver reportes.";
            header("Location: index.php?c=Dashboard&a=index");
            exit;
        }
    }

    /**
     * Panel principal de reportes
     */
    public function index() {
        $this->checkAuth();
        
        $model = new Reporte();
        
        // Filtro de días (por defecto 30)
        $dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 30;
        if (!in_array($dias, [7, 15, 30, 60, 90, 365])) $dias = 30;
        
        $data = [
            'progreso' => $model->progreso($dias),
            'finanzas' => $model->finanzas($dias),
            'ventasHoy' => $model->ventasHoy(),
            'comparativa' => $model->comparativaMensual(),
            'ventasPorDia' => $model->ventasPorDia($dias),
            'productosTop' => $model->productosMasVendidos(5, $dias),
            'ventasPorCategoria' => $model->ventasPorCategoria($dias),
            'estadoInventario' => $model->estadoInventario(),
            'dias' => $dias
        ];
        
        require_once '../views/reportes/index.php';
    }

    /**
     * Reporte de ventas detallado
     */
    public function ventas() {
        $this->checkAuth();
        
        $model = new Reporte();
        $dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 30;
        if (!in_array($dias, [7, 15, 30, 60, 90, 365])) $dias = 30;
        
        $data = [
            'progreso' => $model->progreso($dias),
            'finanzas' => $model->finanzas($dias),
            'ventasPorDia' => $model->ventasPorDia($dias),
            'ventasPorMes' => $model->ventasPorMes(),
            'distribucionProductos' => $model->distribucionProductos($dias),
            'ventasPorCategoria' => $model->ventasPorCategoria($dias),
            'ventasPorUsuario' => $model->ventasPorUsuario($dias),
            'ventasPorHora' => $model->ventasPorHora($dias),
            'comparativa' => $model->comparativaMensual(),
            'dias' => $dias
        ];
        
        require_once '../views/reportes/ventas.php';
    }

    /**
     * Reporte de productos
     */
    public function productos() {
        $accionLotes='productos';$ordenLotes=($_GET['orden_lotes']??'')==='recientes'?'recientes':'vencimiento';
        $this->checkAuth();
        
        $model = new Reporte();
        $dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 30;
        if (!in_array($dias, [7, 15, 30, 60, 90, 365])) $dias = 30;
        
        $data = [
            'productosTop' => $model->productosMasVendidos(20, $dias),
            'productosBajos' => $model->productosMenosVendidos(10),
            'lotes' => $model->lotesInventario($ordenLotes),
            'dias' => $dias
        ];
        
        require_once '../views/reportes/productos.php';
    }

    /**
     * Reporte de inventario
     */
    public function inventario() {
        $accionLotes='inventario';$ordenLotes=($_GET['orden_lotes']??'')==='recientes'?'recientes':'vencimiento';
        $this->checkAuth();
        
        $model = new Reporte();
        
        $data = [
            'estado' => $model->estadoInventario(),
            'lotes' => $model->lotesInventario($ordenLotes),
            'productosBajos' => $model->productosMenosVendidos(20)
        ];
        
        require_once '../views/reportes/inventario.php';
    }
}
?>
