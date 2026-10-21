<?php
require_once __DIR__.'/Stock.php';
require_once __DIR__.'/Caja.php';
class Venta {
    private $conn;
    public function __construct(?PDO $db=null) { $this->conn = $db??(new Database())->getConnection(); }
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
            $caja=Caja::abierta($this->conn,(int)$usuario_id);
            $metodo=$meta['metodo_pago']??'efectivo';
            if(!isset(Caja::metodos()[$metodo]))throw new RuntimeException('Medio de pago inválido.');
            usort($carrito,fn($a,$b)=>(int)($a['id']??0)<=>(int)($b['id']??0));
            $lines = []; $totalCent = 0; $needed = [];
            $q = $this->conn->prepare('SELECT * FROM productos WHERE id=? AND activo=1 FOR UPDATE');
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
                if ($unidades > (int)$p['stock']) throw new RuntimeException('Stock insuficiente para '.$p['nombre']);
                $campo = ['unidad'=>'precio_unidad','blister'=>'precio_blister','caja'=>'precio_caja'][$pres];
                $precio = !(int)$p['es_fraccionable'] ? (float)$p['precio_venta'] : ($p[$campo] !== null ? (float)$p[$campo] : null);
                if ($precio === null || !is_finite($precio) || $precio < 0) throw new RuntimeException('Configura el precio de '.$pres.' para '.$p['nombre']);
                $precioCent = (int)round($precio * 100); $subtotalCent = $precioCent * $cantidad; $totalCent += $subtotalCent;
                $lotes=Stock::salida($this->conn,$id,$unidades,null,false);
                $costo=0;foreach($lotes as $l){if($l['costo_unidad']===null){$costo=null;break;}$costo+=$l['cantidad']*(float)$l['costo_unidad'];}
                $nombrePres=$p['nombre_'.$pres]??($pres==='blister'?$p['nombre_paquete']:$pres);
                $lines[] = [$id,$p['nombre'],$cantidad,$pres,$factor,$unidades,$precioCent,$subtotalCent,$lotes,$costo,$nombrePres];
            }
            if ($totalCent <= 0) throw new RuntimeException('El total debe ser mayor a cero.');
            $cab = $this->conn->prepare('INSERT INTO ventas (cliente,total,usuario_id,telefono,tipo_comprobante,tipo_documento,numero_documento,estado_sunat,metodo_pago,caja_id,clave_unica) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $cab->execute([$cliente ?: 'Cliente General',$totalCent/100,$usuario_id,$telefono ?: null,$tipo,$docTipo ?: null,$docNum ?: null,$tipo === 'boleta' ? 'pendiente' : 'no_aplica',$metodo,$caja['id'],$meta['clave_unica']??null]);
            $ventaId = (int)$this->conn->lastInsertId();
            $detalle = $this->conn->prepare('INSERT INTO detalle_venta (venta_id,producto_id,cantidad,precio_unitario,subtotal,presentacion,factor_unidades,cantidad_unidades,producto_nombre,costo_total,presentacion_nombre) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            foreach ($lines as [$id,$nombre,$cantidad,$pres,$factor,$unidades,$precioCent,$subtotalCent,$lotes,$costo,$nombrePres]) {
                $detalle->execute([$ventaId,$id,$cantidad,$precioCent/100,$subtotalCent/100,$pres,$factor,$unidades,$nombre,$costo,$nombrePres]);$detalleId=(int)$this->conn->lastInsertId();
                foreach($lotes as $l){$this->conn->prepare('INSERT INTO venta_lotes(detalle_id,lote_id,cantidad,costo_unidad) VALUES(?,?,?,?)')->execute([$detalleId,$l['lote_id'],$l['cantidad'],$l['costo_unidad']]);Stock::movimiento($this->conn,$id,'salida',$l['cantidad'],'Venta',(int)$usuario_id,$ventaId,$l['lote_id']);}
            }
            Caja::registrar($this->conn,(int)$usuario_id,'venta',$metodo,$totalCent,'Venta #'.$ventaId,$ventaId);
            Operacion::audit($this->conn,'registrar','ventas',$ventaId,['total'=>$totalCent/100],(int)$usuario_id);
            $this->conn->commit(); return $ventaId;
        } catch (Throwable $e) { if ($this->conn->inTransaction()) $this->conn->rollBack(); throw $e; }
    }
    public function getAll() { return $this->conn->query('SELECT v.*,u.nombre AS usuario FROM ventas v LEFT JOIN usuarios u ON v.usuario_id=u.id ORDER BY v.fecha DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC); }
    public function pagina(string $q='',int $pagina=1): array {
        $args=[];$where='';if($q!==''){$where=' WHERE v.cliente LIKE ? OR CAST(v.id AS CHAR)=?';$args=['%'.$q.'%',$q];}
        $s=$this->conn->prepare('SELECT COUNT(*) FROM ventas v'.$where);$s->execute($args);$total=(int)$s->fetchColumn();$paginas=max(1,(int)ceil($total/30));$pagina=max(1,min($pagina,$paginas));$offset=($pagina-1)*30;
        $s=$this->conn->prepare('SELECT v.*,u.nombre AS usuario FROM ventas v LEFT JOIN usuarios u ON u.id=v.usuario_id'.$where.' ORDER BY v.fecha DESC,v.id DESC LIMIT 30 OFFSET '.$offset);$s->execute($args);
        return ['filas'=>$s->fetchAll(),'total'=>$total,'pagina'=>$pagina,'paginas'=>$paginas];
    }
    public function getById($id) { $q=$this->conn->prepare('SELECT v.*,u.nombre AS usuario FROM ventas v LEFT JOIN usuarios u ON v.usuario_id=u.id WHERE v.id=?');$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC); }
    public function getDetalle($id) { $q=$this->conn->prepare('SELECT d.*,COALESCE(d.producto_nombre,p.nombre) AS producto FROM detalle_venta d LEFT JOIN productos p ON d.producto_id=p.id WHERE d.venta_id=?');$q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC); }
    public function contar() {return $this->conn->query('SELECT COUNT(*) FROM ventas')->fetchColumn();}
    public function totalVendido() {return $this->conn->query('SELECT COALESCE(SUM(total),0) FROM ventas')->fetchColumn();}
    public function ventasHoy() {return $this->conn->query('SELECT COUNT(*) FROM ventas WHERE DATE(fecha)=CURDATE()')->fetchColumn();}
}
