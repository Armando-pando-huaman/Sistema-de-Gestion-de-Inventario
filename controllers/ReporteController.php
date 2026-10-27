<?php
require_once '../models/reporte.php';

class ReporteController {

    public function descargar(): void {
        $this->checkAuth();
        require_once __DIR__.'/../config/exportarcsv.php';
        $model = new Reporte();
        $dias = filter_var($_GET['dias'] ?? 30, FILTER_VALIDATE_INT);
        if (!in_array($dias, [7,15,30,60,90,365], true)) $dias = 30;
        $orden = ($_GET['orden_lotes'] ?? '') === 'recientes' ? 'recientes' : 'vencimiento';
        $vista = is_string($_GET['vista'] ?? null) ? $_GET['vista'] : 'index';
        $tipo = is_string($_GET['tipo'] ?? null) ? $_GET['tipo'] : '';
        $esActual = false;
        switch ($tipo) {
            case 'finanzas':
                $f = $model->finanzas($dias);
                $etiquetas = ['bruto'=>'Ventas brutas','reembolsos'=>'Reembolsos','neto'=>'Ventas netas','costo'=>'Costo neto de ventas conocido','gastos'=>'Gastos','pendientes'=>'Detalles con costo pendiente','resultado'=>'Resultado (vacío si faltan costos)'];
                $columnas = ['concepto'=>'Concepto','valor'=>'Valor (importes en PEN; pendientes en registros)'];
                $filas = [];
                foreach ($etiquetas as $clave=>$etiqueta) $filas[] = ['concepto'=>$etiqueta,'valor'=>$f[$clave]];
                break;
            case 'diario':
                $columnas = ['dia'=>'Fecha','cantidad'=>'Ventas','total'=>'Ventas brutas PEN','anterior_dia'=>'Fecha período anterior','anterior'=>'Ventas brutas anteriores PEN'];
                $filas = $model->progreso($dias)['serie']; break;
            case 'movimientos':
                $columnas = ['clase'=>'Clase de movimiento','registros'=>'Registros','unidades'=>'Unidades mínimas'];
                $filas = $model->progreso($dias)['movimientos']; break;
            case 'meses':
                $columnas = ['mes'=>'Mes','cantidad'=>'Ventas','total'=>'Ventas brutas PEN'];
                $filas = $model->ventasPorMes(); break;
            case 'categorias':
                $columnas = ['categoria'=>'Categoría','total_unidades'=>'Unidades mínimas netas','total_ingresos'=>'Ingresos netos PEN'];
                $filas = $model->ventasPorCategoria($dias); break;
            case 'vendedores':
                $columnas = ['usuario'=>'Vendedor','rol'=>'Rol','num_ventas'=>'Ventas','total'=>'Ventas brutas PEN'];
                $filas = $model->ventasPorUsuario($dias); break;
            case 'horas':
                $columnas = ['hora'=>'Hora','cantidad'=>'Ventas','total'=>'Ventas brutas PEN'];
                $filas = $model->ventasPorHora($dias); break;
            case 'distribucion':
                $columnas = ['producto_id'=>'ID producto','nombre'=>'Producto','unidades'=>'Unidades mínimas netas','ingresos'=>'Ingresos netos PEN'];
                $filas = $model->distribucionProductos($dias); break;
            case 'top':
                $columnas = ['id'=>'ID producto','nombre'=>'Producto','categoria'=>'Categoría','total_vendido'=>'Unidades mínimas netas','num_ventas'=>'Ventas','total_ingresos'=>'Ingresos netos PEN'];
                $filas = $model->productosMasVendidos($vista === 'index' ? 5 : 20, $dias); break;
            case 'bajos':
                $columnas = ['id'=>'ID producto','nombre'=>'Producto','categoria'=>'Categoría','stock'=>'Stock actual','total_vendido'=>'Unidades mínimas netas históricas'];
                $filas = $model->productosMenosVendidos($vista === 'inventario' ? 20 : 10);
                $esActual = true; break;
            case 'estado':
                $columnas = ['total_productos'=>'Productos','total_unidades'=>'Unidades mínimas','valor_compra'=>'Valor de compra conocido PEN','unidades_sin_costo'=>'Unidades sin costo','valor_venta'=>'Valor de venta PEN','stock_bajo'=>'Productos con stock bajo','agotados'=>'Productos agotados'];
                $filas = [$model->estadoInventario()]; $esActual = true; break;
            case 'lotes':
                $columnas = ['lote_id'=>'ID lote','nombre'=>'Producto','categoria'=>'Categoría','lote'=>'Lote','stock'=>'Unidades mínimas','fecha_ingreso'=>'Fecha de ingreso','fecha_compra'=>'Fecha de compra','fecha_vencimiento'=>'Vencimiento','dias_restantes'=>'Días restantes (negativo: vencido)','por_verificar'=>'Datos por verificar (1: sí; 0: no)'];
                $filas = $model->lotesInventario($orden); $esActual = true; break;
            default: http_response_code(400); echo 'Selecciona un reporte válido.'; return;
        }
        $periodo = $esActual ? 'actual' : ($tipo === 'meses' ? 'ultimos-12-meses' : $dias.'-dias');
        ExportarCsv::descargar('reporte-'.$tipo.'-'.$periodo.'-'.date('Y-m-d-His'), $columnas, $filas);
    }

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
