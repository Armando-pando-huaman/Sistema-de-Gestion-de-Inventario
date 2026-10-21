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

    public function finanzas(int $dias=30): array {
        $dias=max(1,min(365,$dias));
        $hoy=new DateTimeImmutable($this->conn->query('SELECT CURDATE()')->fetchColumn());
        $args=[$hoy->modify('-'.($dias-1).' days')->format('Y-m-d'),$hoy->modify('+1 day')->format('Y-m-d')];
        $s=$this->conn->prepare('SELECT COALESCE(SUM(total),0) FROM ventas WHERE fecha>=? AND fecha<?');$s->execute($args);$bruto=(float)$s->fetchColumn();
        $s=$this->conn->prepare('SELECT COALESCE(SUM(total),0) FROM devoluciones WHERE fecha>=? AND fecha<?');$s->execute($args);$reembolsos=(float)$s->fetchColumn();
        $s=$this->conn->prepare('SELECT COALESCE(SUM(d.costo_total),0) AS costo,COALESCE(SUM(d.costo_total IS NULL),0) AS pendientes FROM detalle_venta d JOIN ventas v ON v.id=d.venta_id WHERE v.fecha>=? AND v.fecha<?');$s->execute($args);$costos=$s->fetch();
        $s=$this->conn->prepare('SELECT COALESCE(SUM(d.costo_reintegrado),0) AS costo,COALESCE(SUM(d.costo_reintegrado IS NULL),0) AS pendientes FROM devolucion_detalles d JOIN devoluciones v ON v.id=d.devolucion_id WHERE v.fecha>=? AND v.fecha<?');$s->execute($args);$retornos=$s->fetch();
        $s=$this->conn->prepare('SELECT COALESCE(SUM(importe),0) FROM gastos WHERE fecha>=? AND fecha<?');$s->execute($args);$gastos=(float)$s->fetchColumn();
        $pendientes=(int)$costos['pendientes']+(int)$retornos['pendientes'];
        $costo=(float)$costos['costo']-(float)$retornos['costo'];
        return ['bruto'=>$bruto,'reembolsos'=>$reembolsos,'neto'=>$bruto-$reembolsos,'costo'=>$costo,'gastos'=>$gastos,'pendientes'=>$pendientes,'resultado'=>$pendientes?null:($bruto-$reembolsos-$costo-$gastos)];
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
    public function distribucionProductos(int $dias=30): array {
        $s=$this->conn->prepare("SELECT d.producto_id,COALESCE(p.nombre,d.producto_nombre,'Producto retirado') AS nombre,
            SUM(COALESCE(d.cantidad_unidades,d.cantidad*COALESCE(d.factor_unidades,1))-d.devuelto_unidades) AS unidades,
            SUM(d.subtotal-d.reembolsado) AS ingresos
            FROM detalle_venta d JOIN ventas v ON v.id=d.venta_id LEFT JOIN productos p ON p.id=d.producto_id
            WHERE v.fecha>=DATE_SUB(CURDATE(),INTERVAL :dias DAY) AND v.fecha<DATE_ADD(CURDATE(),INTERVAL 1 DAY)
            GROUP BY d.producto_id,COALESCE(p.nombre,d.producto_nombre,'Producto retirado')
            HAVING unidades>0 ORDER BY unidades DESC,nombre ASC");
        $s->bindValue(':dias',max(0,min(365,$dias)-1),PDO::PARAM_INT);$s->execute();return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public function productosMasVendidos($limite = 10, $dias = null) {
        $sql = "
            SELECT 
                p.id,
                p.nombre,
                c.nombre AS categoria,
                SUM(COALESCE(d.cantidad_unidades, d.cantidad)-d.devuelto_unidades) AS total_vendido,
                SUM(d.subtotal-d.reembolsado) AS total_ingresos,
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
                COALESCE(SUM(COALESCE(d.cantidad_unidades, d.cantidad)-d.devuelto_unidades), 0) AS total_vendido
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
                SUM(COALESCE(d.cantidad_unidades, d.cantidad*COALESCE(d.factor_unidades,1))-d.devuelto_unidades) AS total_unidades,
                SUM(d.subtotal-d.reembolsado) AS total_ingresos
            FROM detalle_venta d
            LEFT JOIN productos p ON d.producto_id = p.id
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
                COALESCE(SUM(stock * precio_compra / GREATEST(unidades_por_caja,1)), 0) AS valor_compra,
                COALESCE(SUM(stock * precio_venta / GREATEST(unidades_por_caja,1)), 0) AS valor_venta,
                SUM(CASE WHEN stock <= stock_minimo THEN 1 ELSE 0 END) AS stock_bajo,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS agotados
            FROM productos
        ");
        $estado=$stmt->fetch();
        $costos=$this->conn->query('SELECT COALESCE(SUM(stock*costo_unidad),0) AS valor,COALESCE(SUM(CASE WHEN costo_unidad IS NULL THEN stock ELSE 0 END),0) AS sin_costo FROM lotes WHERE stock>0')->fetch();
        $estado['valor_compra']=$costos['valor'];$estado['unidades_sin_costo']=$costos['sin_costo'];
        return $estado;
    }

    /**
     * PRODUCTOS POR VENCER
     */
    public function lotesInventario(string $orden = 'vencimiento'): array {
        $ordenSql=$orden==='recientes'?'l.creado DESC,l.id DESC':'l.vencimiento IS NULL,l.vencimiento ASC,l.creado DESC,l.id DESC';
        return $this->conn->query("SELECT l.id AS lote_id,l.codigo AS lote,p.nombre,c.nombre AS categoria,
            l.stock,l.vencimiento AS fecha_vencimiento,l.creado AS fecha_ingreso,l.por_verificar,
            DATEDIFF(l.vencimiento,CURDATE()) AS dias_restantes,
            (SELECT MAX(co.fecha) FROM compra_detalles cd JOIN compras co ON co.id=cd.compra_id WHERE cd.lote_id=l.id) AS fecha_compra
            FROM lotes l JOIN productos p ON p.id=l.producto_id
            LEFT JOIN categorias c ON c.id=p.categoria_id
            WHERE l.stock>0 ORDER BY $ordenSql")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function productosPorVencer($dias = 90) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.*, l.codigo AS lote,l.stock AS stock,l.vencimiento AS fecha_vencimiento,
                c.nombre AS categoria,
                DATEDIFF(l.vencimiento, CURDATE()) AS dias_restantes
            FROM productos p
            JOIN lotes l ON l.producto_id=p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE l.vencimiento IS NOT NULL
                AND l.vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                AND l.stock > 0
            ORDER BY l.vencimiento ASC
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
