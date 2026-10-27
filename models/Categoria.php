<?php
require_once __DIR__.'/operacion.php';
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
        return Operacion::transaccion($this->conn,function()use($nombre){
        $stmt = $this->conn->prepare('INSERT INTO categorias (nombre) VALUES (:nombre)');
        $ok=$stmt->execute([':nombre' => $nombre]);Operacion::audit($this->conn,'crear','categorias',(int)$this->conn->lastInsertId(),['nombre'=>$nombre]);return $ok;
        });
    }
    public function actualizar($id, $nombre) {
        return Operacion::transaccion($this->conn,function()use($id,$nombre){
        $stmt = $this->conn->prepare('UPDATE categorias SET nombre = :nombre WHERE id = :id');
        $antes=$this->getById($id);$ok=$stmt->execute([':nombre' => $nombre, ':id' => $id]);Operacion::audit($this->conn,'editar','categorias',(int)$id,['antes'=>$antes,'nombre'=>$nombre]);return $ok;
        });
    }
    public function eliminar($id) {
        return Operacion::transaccion($this->conn,function()use($id){
        $stmt = $this->conn->prepare('DELETE FROM categorias WHERE id = :id AND NOT EXISTS (SELECT 1 FROM productos WHERE categoria_id = :id_check)');
        $stmt->execute([':id' => $id, ':id_check' => $id]);
        $ok=$stmt->rowCount()>0;if($ok)Operacion::audit($this->conn,'eliminar','categorias',(int)$id);return $ok;
        });
    }
}
