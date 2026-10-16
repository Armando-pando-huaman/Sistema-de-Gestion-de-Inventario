<?php
class Venta {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Registra una venta completa (cabecera + detalle + actualiza stock)
     */
    public function registrar($cliente, $total, $usuario_id, $carrito) {
        try {
            $this->conn->beginTransaction();

            // 1. Insertar cabecera de venta
            $stmt = $this->conn->prepare("INSERT INTO ventas (cliente, total, usuario_id) VALUES (:cliente, :total, :usuario_id)");
            $stmt->execute([
                ':cliente' => $cliente,
                ':total' => $total,
                ':usuario_id' => $usuario_id
            ]);
            $venta_id = $this->conn->lastInsertId();

            // 2. Insertar detalle y actualizar stock
            foreach ($carrito as $item) {
                // Verificar stock disponible
                $checkStock = $this->conn->prepare("SELECT stock FROM productos WHERE id = ?");
                $checkStock->execute([$item['id']]);
                $stockActual = $checkStock->fetchColumn();
                
                if ($stockActual < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto ID " . $item['id']);
                }

                $subtotal = $item['precio'] * $item['cantidad'];
                
                // Insertar detalle
                $stmtDetalle = $this->conn->prepare("INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmtDetalle->execute([$venta_id, $item['id'], $item['cantidad'], $item['precio'], $subtotal]);

                // Actualizar stock
                $stmtStock = $this->conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmtStock->execute([$item['cantidad'], $item['id']]);

                // Registrar movimiento (historial)
                $stmtMov = $this->conn->prepare("INSERT INTO movimientos (producto_id, tipo, cantidad, motivo, usuario_id) VALUES (?, 'salida', ?, 'Venta', ?)");
                $stmtMov->execute([$item['id'], $item['cantidad'], $usuario_id]);
            }

            $this->conn->commit();
            return $venta_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Lista todas las ventas
     */
    public function getAll() {
        $query = "SELECT v.*, u.nombre AS usuario 
                  FROM ventas v 
                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                  ORDER BY v.fecha DESC 
                  LIMIT 100";
        return $this->conn->query($query)->fetchAll();
    }

    /**
     * Obtiene una venta por ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT v.*, u.nombre AS usuario 
                                      FROM ventas v 
                                      LEFT JOIN usuarios u ON v.usuario_id = u.id 
                                      WHERE v.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene el detalle de una venta
     */
    public function getDetalle($venta_id) {
        $stmt = $this->conn->prepare("SELECT d.*, p.nombre AS producto 
                                      FROM detalle_venta d 
                                      LEFT JOIN productos p ON d.producto_id = p.id 
                                      WHERE d.venta_id = :venta_id");
        $stmt->execute([':venta_id' => $venta_id]);
        return $stmt->fetchAll();
    }

    /**
     * Estadísticas para el dashboard
     */
    public function contar() {
        return $this->conn->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
    }

    public function totalVendido() {
        return $this->conn->query("SELECT COALESCE(SUM(total), 0) FROM ventas")->fetchColumn();
    }

    public function ventasHoy() {
        return $this->conn->query("SELECT COUNT(*) FROM ventas WHERE DATE(fecha) = CURDATE()")->fetchColumn();
    }
}
?>