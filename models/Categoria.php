<?php
class Categoria {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    public function getAll() {
        return $this->conn->query('SELECT c.id, c.nombre, COUNT(p.id) AS total_productos FROM categorias c LEFT JOIN productos p ON p.categoria_id = c.id GROUP BY c.id, c.nombre ORDER BY c.nombre ASC')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getById($id) {
        $stmt = $this->conn->prepare('SELECT id, nombre FROM categorias WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function existeNombre($nombre, $exceptoId = 0) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM categorias WHERE LOWER(nombre) = LOWER(:nombre) AND id <> :id');
        $stmt->execute([':nombre' => $nombre, ':id' => $exceptoId]);
        return (int)$stmt->fetchColumn() > 0;
    }
    public function crear($nombre) {
        $stmt = $this->conn->prepare('INSERT INTO categorias (nombre) VALUES (:nombre)');
        return $stmt->execute([':nombre' => $nombre]);
    }
    public function actualizar($id, $nombre) {
        $stmt = $this->conn->prepare('UPDATE categorias SET nombre = :nombre WHERE id = :id');
        return $stmt->execute([':nombre' => $nombre, ':id' => $id]);
    }
    public function eliminar($id) {
        $stmt = $this->conn->prepare('DELETE FROM categorias WHERE id = :id AND NOT EXISTS (SELECT 1 FROM productos WHERE categoria_id = :id_check)');
        $stmt->execute([':id' => $id, ':id_check' => $id]);
        return $stmt->rowCount() > 0;
    }
}
