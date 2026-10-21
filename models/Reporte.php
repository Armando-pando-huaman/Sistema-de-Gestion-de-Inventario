<?php
class Reporte {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * RESUMEN GENERAL DEL DÍA
     */
    public function ventasHoy() {
        $stmt = $this->conn->query("
            SELECT 
                COUNT(*) AS cantidad,
                COALESCE(SUM(total), 0) AS total,
                COALESCE(AVG(total), 0) AS promedio
            FROM ventas 
            WHERE DATE(fecha) = CURDATE()
        ");
        return $stmt->fetch();
    }

    public function progreso($dias = 30) {
        $dias = max(1, min(365, (int)$dias));
        $hoy = new DateTimeImmutable($this->conn->query('SELECT CURDATE()')->fetchColumn());
        $inicio = $hoy->modify('-' . ($dias - 1) . ' days');
        $anterior = $inicio->modify("-$dias days");
        $fin = $hoy->modify('+1 day');
        $s = $this->conn->prepare('SELECT DATE(fecha) AS dia, COUNT(*) AS cantidad, SUM(total) AS total FROM ventas WHERE fecha >= ? AND fecha < ? GROUP BY DATE(fecha) ORDER BY dia');
        $s->execute([$anterior->format('Y-m-d'), $fin->format('Y-m-d')]);
        $porDia = [];
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $fila) $porDia[$fila['dia']] = $fila;
        $serie = []; $actualTotal = 0; $anteriorTotal = 0; $actualCantidad = 0; $anteriorCantidad = 0;
        for ($i = 0; $i < $dias; $i++) {
            $fecha = $inicio->modify("+$i days")->format('Y-m-d');
            $fechaAnterior = $anterior->modify("+$i days")->format('Y-m-d');
            $total = (float)($porDia[$fecha]['total'] ?? 0);
            $previo = (float)($porDia[$fechaAnterior]['total'] ?? 0);
            $cantidad = (int)($porDia[$fecha]['cantidad'] ?? 0);
            $actualTotal += $total; $anteriorTotal += $previo;
            $actualCantidad += $cantidad; $anteriorCantidad += (int)($porDia[$fechaAnterior]['cantidad'] ?? 0);
            $serie[] = ['dia' => $fecha, 'anterior_dia' => $fechaAnterior, 'total' => $total, 'anterior' => $previo, 'cantidad' => $cantidad];
        }
        $s = $this->conn->prepare("SELECT CASE WHEN tipo='entrada' THEN 'entrada' WHEN motivo='Venta' THEN 'venta' ELSE 'otra_salida' END AS clase, COUNT(*) AS registros, SUM(cantidad) AS unidades FROM movimientos WHERE fecha >= ? AND fecha < ? GROUP BY clase");
        $s->execute([$inicio->format('Y-m-d'), $fin->format('Y-m-d')]);
        return ['serie' => $serie, 'total' => $actualTotal, 'anterior' => $anteriorTotal,
            'cantidad' => $actualCantidad, 'cantidad_anterior' => $anteriorCantidad,
            'ticket' => $actualCantidad ? $actualTotal / $actualCantidad : 0,
            'cambio' => $anteriorTotal > 0 ? ($actualTotal - $anteriorTotal) * 100 / $anteriorTotal : null,
            'inicio' => $inicio->format('Y-m-d'), 'fin' => $hoy->format('Y-m-d'),
            'anterior_inicio' => $anterior->format('Y-m-d'), 'anterior_fin' => $inicio->modify('-1 day')->format('Y-m-d'),
            'movimientos' => $s->fetchAll(PDO::FETCH_ASSOC)];
    }

    /**
     * VENTAS POR DÍA (últimos N días)
     */
    public function ventasPorDia($dias = 30) {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE(fecha) AS dia,
                COUNT(*) AS cantidad,
                COALESCE(SUM(total), 0) AS total
            FROM ventas 
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY) AND fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            GROUP BY DATE(fecha)
            ORDER BY dia ASC
        ");
        $stmt->bindValue(':dias', max(0, (int)$dias - 1), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * VENTAS POR MES (últimos 12 meses)
     */
    public function ventasPorMes() {
        $stmt = $this->conn->query("
            SELECT 
                DATE_FORMAT(fecha, '%Y-%m') AS mes,
                DATE_FORMAT(fecha, '%b %Y') AS mes_legible,
                COUNT(*) AS cantidad,
                COALESCE(SUM(total), 0) AS total
            FROM ventas 
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')
            ORDER BY mes ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * PRODUCTOS MÁS VENDIDOS
     */
    public function productosMasVendidos($limite = 10, $dias = null) {
        $sql = "
            SELECT 
                p.id,
                p.nombre,
                c.nombre AS categoria,
                SUM(COALESCE(d.cantidad_unidades, d.cantidad)) AS total_vendido,
                SUM(d.subtotal) AS total_ingresos,
                COUNT(DISTINCT d.venta_id) AS num_ventas
            FROM detalle_venta d
            INNER JOIN productos p ON d.producto_id = p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
        ";
        
        if ($dias) {
            $sql .= " WHERE d.id IN (
                SELECT dv.id FROM detalle_venta dv 
                INNER JOIN ventas v ON dv.venta_id = v.id 
                WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY) AND v.fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            )";
        }
        
        $sql .= " GROUP BY p.id ORDER BY total_vendido DESC LIMIT :limite";
        
        $stmt = $this->conn->prepare($sql);
        if ($dias) {
            $stmt->bindValue(':dias', max(0, (int)$dias - 1), PDO::PARAM_INT);
        }
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * PRODUCTOS MENOS VENDIDOS
     */
    public function productosMenosVendidos($limite = 10) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.id,
                p.nombre,
                p.stock,
                c.nombre AS categoria,
                COALESCE(SUM(COALESCE(d.cantidad_unidades, d.cantidad)), 0) AS total_vendido
            FROM productos p
            LEFT JOIN detalle_venta d ON p.id = d.producto_id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            GROUP BY p.id
            ORDER BY total_vendido ASC, p.stock DESC
            LIMIT :limite
        ");
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * VENTAS POR CATEGORÍA
     */
    public function ventasPorCategoria($dias = 30) {
        $stmt = $this->conn->prepare("
            SELECT 
                COALESCE(c.nombre, 'Sin categoría') AS categoria,
                SUM(COALESCE(d.cantidad_unidades, d.cantidad)) AS total_unidades,
                SUM(d.subtotal) AS total_ingresos
            FROM detalle_venta d
            INNER JOIN productos p ON d.producto_id = p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            INNER JOIN ventas v ON d.venta_id = v.id
            WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY) AND v.fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            GROUP BY c.nombre
            ORDER BY total_ingresos DESC
        ");
        $stmt->bindValue(':dias', max(0, (int)$dias - 1), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * VENTAS POR USUARIO (vendedor)
     */
    public function ventasPorUsuario($dias = 30) {
        $stmt = $this->conn->prepare("
            SELECT 
                u.nombre AS usuario,
                u.rol,
                COUNT(v.id) AS num_ventas,
                COALESCE(SUM(v.total), 0) AS total
            FROM ventas v
            INNER JOIN usuarios u ON v.usuario_id = u.id
            WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY) AND v.fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            GROUP BY u.id
            ORDER BY total DESC
        ");
        $stmt->bindValue(':dias', max(0, (int)$dias - 1), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ESTADO DEL INVENTARIO
     */
    public function estadoInventario() {
        $stmt = $this->conn->query("
            SELECT 
                COUNT(*) AS total_productos,
                COALESCE(SUM(stock), 0) AS total_unidades,
                COALESCE(SUM(stock * precio_compra), 0) AS valor_compra,
                COALESCE(SUM(stock * precio_venta), 0) AS valor_venta,
                SUM(CASE WHEN stock <= stock_minimo THEN 1 ELSE 0 END) AS stock_bajo,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS agotados
            FROM productos
        ");
        return $stmt->fetch();
    }

    /**
     * PRODUCTOS POR VENCER
     */
    public function productosPorVencer($dias = 90) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.*,
                c.nombre AS categoria,
                DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_restantes
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.fecha_vencimiento IS NOT NULL 
                AND p.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                AND p.stock > 0
            ORDER BY p.fecha_vencimiento ASC
        ");
        $stmt->bindValue(':dias', (int)$dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * HORAS CON MÁS VENTAS
     */
    public function ventasPorHora($dias = 30) {
        $stmt = $this->conn->prepare("
            SELECT 
                HOUR(fecha) AS hora,
                COUNT(*) AS cantidad,
                COALESCE(SUM(total), 0) AS total
            FROM ventas
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY) AND fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            GROUP BY HOUR(fecha)
            ORDER BY hora ASC
        ");
        $stmt->bindValue(':dias', max(0, (int)$dias - 1), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * COMPARATIVA: ESTE MES vs MES ANTERIOR
     */
    public function comparativaMensual() {
        $stmt = $this->conn->query("
            SELECT 
                (SELECT COALESCE(SUM(total), 0) FROM ventas 
                 WHERE MONTH(fecha) = MONTH(CURDATE()) 
                 AND YEAR(fecha) = YEAR(CURDATE())) AS mes_actual,
                (SELECT COALESCE(SUM(total), 0) FROM ventas 
                 WHERE MONTH(fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                 AND YEAR(fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) AS mes_anterior
        ");
        return $stmt->fetch();
    }
}
?>
