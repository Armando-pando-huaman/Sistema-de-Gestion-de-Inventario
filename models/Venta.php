<?php
class Venta {
    private $conn;
    public function __construct() { $this->conn = (new Database())->getConnection(); }
    public function registrar($cliente, $usuario_id, $carrito, $meta = []) {
        if (!is_array($carrito) || !$carrito) throw new RuntimeException('El carrito está vacío.');
        $tipo = ($meta['tipo_comprobante'] ?? 'ticket') === 'boleta' ? 'boleta' : 'ticket';
        $telefono = trim((string)($meta['telefono'] ?? ''));
        if ($telefono !== '' && !preg_match('/^\+?[0-9]{9,15}$/', $telefono)) throw new RuntimeException('Número de celular inválido.');
        $docTipo = trim((string)($meta['tipo_documento'] ?? ''));
        $docNum = trim((string)($meta['numero_documento'] ?? ''));
        if ($tipo === 'boleta' && $docNum !== '') {
            if (!in_array($docTipo, ['DNI','RUC','CE','PASAPORTE'], true)) throw new RuntimeException('Tipo de documento inválido.');
            if ($docTipo === 'DNI' && !preg_match('/^\d{8}$/', $docNum)) throw new RuntimeException('DNI debe tener 8 dígitos.');
            if ($docTipo === 'RUC' && !preg_match('/^\d{11}$/', $docNum)) throw new RuntimeException('RUC debe tener 11 dígitos.');
        }
        try {
            $this->conn->beginTransaction();
            $lines = []; $totalCent = 0; $needed = [];
            $q = $this->conn->prepare('SELECT id,nombre,stock,precio_venta,precio_unidad,precio_blister,precio_caja,unidades_por_blister,unidades_por_caja,es_fraccionable FROM productos WHERE id=? FOR UPDATE');
            foreach ($carrito as $item) {
                $id = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
                $cantidad = filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT);
                $pres = (string)($item['presentacion'] ?? 'unidad');
                if (!$id || !$cantidad || $cantidad < 1 || $cantidad > 100000 || !in_array($pres,['unidad','blister','caja'],true)) throw new RuntimeException('Producto, cantidad o presentación inválidos.');
                $q->execute([$id]); $p = $q->fetch(PDO::FETCH_ASSOC);
                if (!$p) throw new RuntimeException('Producto inexistente: '.$id);
                if (!(int)$p['es_fraccionable'] && $pres !== 'unidad') throw new RuntimeException('Este producto se vende completo.');
                $factor = $pres === 'caja' ? (int)$p['unidades_por_caja'] : ($pres === 'blister' ? (int)$p['unidades_por_blister'] : 1);
                if ($factor < 1) throw new RuntimeException('Configura unidades por presentación de '.$p['nombre']);
                $unidades = $factor * $cantidad;
                $needed[$id] = ($needed[$id] ?? 0) + $unidades;
                if ($needed[$id] > (int)$p['stock']) throw new RuntimeException('Stock insuficiente para '.$p['nombre']);
                $campo = ['unidad'=>'precio_unidad','blister'=>'precio_blister','caja'=>'precio_caja'][$pres];
                $precio = !(int)$p['es_fraccionable'] ? (float)$p['precio_venta'] : ($p[$campo] !== null ? (float)$p[$campo] : null);
                if ($precio === null || !is_finite($precio) || $precio < 0) throw new RuntimeException('Configura el precio de '.$pres.' para '.$p['nombre']);
                $precioCent = (int)round($precio * 100); $subtotalCent = $precioCent * $cantidad; $totalCent += $subtotalCent;
                $lines[] = [$id,$p['nombre'],$cantidad,$pres,$factor,$unidades,$precioCent,$subtotalCent];
            }
            if ($totalCent <= 0) throw new RuntimeException('El total debe ser mayor a cero.');
            $cab = $this->conn->prepare('INSERT INTO ventas (cliente,total,usuario_id,telefono,tipo_comprobante,tipo_documento,numero_documento,estado_sunat) VALUES (?,?,?,?,?,?,?,?)');
            $cab->execute([$cliente ?: 'Cliente General',$totalCent/100,$usuario_id,$telefono ?: null,$tipo,$docTipo ?: null,$docNum ?: null,$tipo === 'boleta' ? 'pendiente' : 'no_aplica']);
            $ventaId = (int)$this->conn->lastInsertId();
            $detalle = $this->conn->prepare('INSERT INTO detalle_venta (venta_id,producto_id,cantidad,precio_unitario,subtotal,presentacion,factor_unidades,cantidad_unidades,producto_nombre) VALUES (?,?,?,?,?,?,?,?,?)');
            $mov = $this->conn->prepare("INSERT INTO movimientos (producto_id,tipo,cantidad,motivo,usuario_id) VALUES (?,'salida',?,'Venta',?)");
            foreach ($lines as [$id,$nombre,$cantidad,$pres,$factor,$unidades,$precioCent,$subtotalCent]) {
                $detalle->execute([$ventaId,$id,$cantidad,$precioCent/100,$subtotalCent/100,$pres,$factor,$unidades,$nombre]);
                $mov->execute([$id,$unidades,$usuario_id]);
            }
            $upd = $this->conn->prepare('UPDATE productos SET stock=stock-? WHERE id=? AND stock>=?');
            foreach ($needed as $id=>$unidades) { $upd->execute([$unidades,$id,$unidades]); if ($upd->rowCount() !== 1) throw new RuntimeException('Stock insuficiente durante la venta.'); }
            $this->conn->commit(); return $ventaId;
        } catch (Throwable $e) { if ($this->conn->inTransaction()) $this->conn->rollBack(); throw $e; }
    }
    public function getAll() { return $this->conn->query('SELECT v.*,u.nombre AS usuario FROM ventas v LEFT JOIN usuarios u ON v.usuario_id=u.id ORDER BY v.fecha DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC); }
    public function getById($id) { $q=$this->conn->prepare('SELECT v.*,u.nombre AS usuario FROM ventas v LEFT JOIN usuarios u ON v.usuario_id=u.id WHERE v.id=?');$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC); }
    public function getDetalle($id) { $q=$this->conn->prepare('SELECT d.*,COALESCE(d.producto_nombre,p.nombre) AS producto FROM detalle_venta d LEFT JOIN productos p ON d.producto_id=p.id WHERE d.venta_id=?');$q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC); }
    public function contar() {return $this->conn->query('SELECT COUNT(*) FROM ventas')->fetchColumn();}
    public function totalVendido() {return $this->conn->query('SELECT COALESCE(SUM(total),0) FROM ventas')->fetchColumn();}
    public function ventasHoy() {return $this->conn->query('SELECT COUNT(*) FROM ventas WHERE DATE(fecha)=CURDATE()')->fetchColumn();}
}
