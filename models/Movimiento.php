<?php
require_once __DIR__.'/Stock.php';
class Movimiento {
    private $conn;
    private $table = "movimientos";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT m.*, p.nombre AS producto, u.nombre AS usuario
                  FROM " . $this->table . " m
                  LEFT JOIN productos p ON m.producto_id = p.id
                  LEFT JOIN usuarios u ON m.usuario_id = u.id
                  ORDER BY m.fecha DESC LIMIT 100";
        return $this->conn->query($query)->fetchAll();
    }

    public function registrar($producto_id, $tipo, $cantidad, $motivo, $usuario_id, $meta=[]) {
        $id=Operacion::entero($producto_id);$cantidad=Operacion::entero($cantidad);
        if(!isset(self::motivos()[$tipo])||!in_array($motivo,self::motivos()[$tipo],true))throw new RuntimeException('Motivo no válido. Las compras y devoluciones se registran desde su documento.');
        return Operacion::transaccion($this->conn,function()use($id,$tipo,$cantidad,$motivo,$usuario_id,$meta){
            $p=Stock::producto($this->conn,$id);if(!$p['activo'])throw new RuntimeException('Producto inactivo.');
            if($tipo==='entrada'){
                $lote=Stock::entrada($this->conn,$id,$cantidad,Operacion::texto($meta['lote']??'',80),Operacion::fecha($meta['vencimiento']??''),null,true);
                Stock::movimiento($this->conn,$id,$tipo,$cantidad,$motivo,(int)$usuario_id,null,$lote);
            }else{
                $lote=Operacion::entero($meta['lote_id']??null);
                $filas=Stock::salida($this->conn,$id,$cantidad,$lote,$motivo==='Vencimiento');
                foreach($filas as $fila)Stock::movimiento($this->conn,$id,$tipo,$fila['cantidad'],$motivo,(int)$usuario_id,null,$fila['lote_id']);
            }
            Operacion::audit($this->conn,'registrar','movimientos',null,['producto'=>$id,'tipo'=>$tipo,'cantidad'=>$cantidad,'motivo'=>$motivo],(int)$usuario_id);return true;
        });
    }
    public static function motivos() {
        return [
            'entrada' => ['Ajuste de entrada', 'Otra entrada'],
            'salida' => ['Vencimiento', 'Daño / merma', 'Devolución al proveedor', 'Uso interno', 'Ajuste de salida', 'Otra salida']
        ];
    }

    public function historial($filtros, $pagina = 1) {
        $where = []; $params = [];
        foreach (['desde' => '>=', 'hasta' => '<'] as $key => $op) {
            if (!empty($filtros[$key])) {
                $where[] = "m.fecha $op ?";
                $params[] = $key === 'hasta' ? date('Y-m-d', strtotime($filtros[$key] . ' +1 day')) : $filtros[$key];
            }
        }
        $venta = "(m.tipo = 'salida' AND m.motivo = 'Venta')";
        switch ($filtros['clase'] ?? '') {
            case 'entrada': $where[] = "m.tipo = 'entrada'"; break;
            case 'venta': $where[] = $venta; break;
            case 'otra_salida': $where[] = "m.tipo = 'salida' AND COALESCE(m.motivo, '') <> 'Venta'"; break;
        }
        if (!empty($filtros['producto_id'])) { $where[] = 'm.producto_id = ?'; $params[] = $filtros['producto_id']; }
        $sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $s = $this->conn->prepare("SELECT COUNT(*) AS total,
            COALESCE(SUM(m.tipo = 'entrada'),0) AS entradas,
            COALESCE(SUM($venta),0) AS ventas,
            COALESCE(SUM(m.tipo = 'salida' AND COALESCE(m.motivo,'') <> 'Venta'),0) AS otras
            FROM movimientos m" . $sqlWhere);
        $s->execute($params); $resumen = $s->fetch(PDO::FETCH_ASSOC);
        $paginas = max(1, (int)ceil($resumen['total'] / 30));
        $pagina = max(1, min((int)$pagina, $paginas));
        $offset = ($pagina - 1) * 30;
        $s = $this->conn->prepare('SELECT m.*, p.nombre AS producto, u.nombre AS usuario FROM movimientos m
            LEFT JOIN productos p ON p.id=m.producto_id LEFT JOIN usuarios u ON u.id=m.usuario_id' . $sqlWhere . " ORDER BY m.fecha DESC, m.id DESC LIMIT 30 OFFSET $offset");
        $s->execute($params);
        return ['filas' => $s->fetchAll(PDO::FETCH_ASSOC), 'resumen' => $resumen, 'pagina' => $pagina, 'paginas' => $paginas];
    }

    public function resumenPorDia($inicio, $fin) {
        $stmt = $this->conn->prepare("SELECT DATE(fecha) AS dia, COUNT(*) AS total,
            SUM(CASE WHEN tipo = 'entrada' THEN 1 ELSE 0 END) AS entradas,
            SUM(CASE WHEN tipo = 'salida' THEN 1 ELSE 0 END) AS salidas
            FROM movimientos WHERE fecha >= ? AND fecha < ? GROUP BY DATE(fecha)");
        $stmt->execute([$inicio, $fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function resumenPorHora($inicio, $fin) {
        $stmt = $this->conn->prepare("SELECT DATE_FORMAT(fecha, '%Y-%m-%d %H:00:00') AS momento,
            COUNT(*) AS total,
            SUM(CASE WHEN tipo = 'entrada' THEN 1 ELSE 0 END) AS entradas,
            SUM(CASE WHEN tipo = 'salida' THEN 1 ELSE 0 END) AS salidas
            FROM movimientos WHERE fecha >= ? AND fecha < ?
            GROUP BY DATE(fecha), HOUR(fecha) ORDER BY momento");
        $stmt->execute([$inicio, $fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar() {
        return $this->conn->query("SELECT COUNT(*) FROM " . $this->table)->fetchColumn();
    }
}
?>
