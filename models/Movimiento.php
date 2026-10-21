<?php
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

    public function registrar($producto_id, $tipo, $cantidad, $motivo, $usuario_id) {
        $producto_id = filter_var($producto_id, FILTER_VALIDATE_INT);
        $cantidad = filter_var($cantidad, FILTER_VALIDATE_INT);
        if (!$producto_id || !$cantidad || $cantidad < 1 || $cantidad > 1000000 || !isset(self::motivos()[$tipo]) || !in_array($motivo, self::motivos()[$tipo], true)) {
            throw new RuntimeException('Revisa el producto, tipo, cantidad y motivo del movimiento.');
        }
        try {
        $this->conn->beginTransaction();
        $lock = $this->conn->prepare('SELECT stock FROM productos WHERE id = ? FOR UPDATE');
        $lock->execute([$producto_id]);
        $stock = $lock->fetchColumn();
        if ($stock === false) throw new RuntimeException('El producto no existe.');
        if ($tipo === 'salida' && $cantidad > (int)$stock) throw new RuntimeException('Stock insuficiente para registrar la salida.');
        // 1. Registrar movimiento
        $query = "INSERT INTO " . $this->table . " (producto_id, tipo, cantidad, motivo, usuario_id) 
                  VALUES (:producto_id, :tipo, :cantidad, :motivo, :usuario_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':producto_id' => $producto_id,
            ':tipo' => $tipo,
            ':cantidad' => $cantidad,
            ':motivo' => $motivo,
            ':usuario_id' => $usuario_id
        ]);

        // 2. Actualizar stock
        $operador = ($tipo === 'entrada') ? '+' : '-';
        $update = "UPDATE productos SET stock = stock $operador :cantidad WHERE id = :id";
        $stmt2 = $this->conn->prepare($update);
        $stmt2->execute([':cantidad' => $cantidad, ':id' => $producto_id]);
        $this->conn->commit();
        return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        }
    }

    public static function motivos() {
        return [
            'entrada' => ['Compra / reposición', 'Devolución de cliente', 'Ajuste de entrada', 'Otra entrada'],
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

    public function contar() {
        return $this->conn->query("SELECT COUNT(*) FROM " . $this->table)->fetchColumn();
    }
}
?>
