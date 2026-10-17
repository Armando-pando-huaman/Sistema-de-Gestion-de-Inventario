<?php
class Producto {
    private PDO $conn;
    public function __construct() { $this->conn = (new Database())->getConnection(); }
    public function getAll() { return $this->conn->query('SELECT p.*,c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id=c.id ORDER BY p.id DESC')->fetchAll(PDO::FETCH_ASSOC); }
    public function getById($id) { $s=$this->conn->prepare('SELECT * FROM productos WHERE id=?');$s->execute([$id]);return $s->fetch(PDO::FETCH_ASSOC); }
    public function crear($d) { $fields=array_keys($d);$cols=implode(',',array_map(fn($x)=>'`'.$x.'`',$fields));$holders=implode(',',array_fill(0,count($fields),'?'));$s=$this->conn->prepare("INSERT INTO productos ($cols) VALUES ($holders)");return $s->execute(array_values($d)); }
    public function actualizar($d) { $id=$d['id'];unset($d['id']);$sets=implode(',',array_map(fn($x)=>'`'.$x.'`=?',array_keys($d)));$s=$this->conn->prepare("UPDATE productos SET $sets WHERE id=?");return $s->execute([...array_values($d),$id]); }
    public function eliminar($id) { $s=$this->conn->prepare('DELETE FROM productos WHERE id=?');return $s->execute([$id]); }
    public function contar() { return $this->conn->query('SELECT COUNT(*) FROM productos')->fetchColumn(); }
    public function stockBajo() { return $this->conn->query('SELECT * FROM productos WHERE stock<=stock_minimo')->fetchAll(PDO::FETCH_ASSOC); }
}
