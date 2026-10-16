<?php
class Usuario {
    private $conn;
    private $table = "usuarios";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getAll() {
        return $this->conn->query("SELECT id, nombre, email, rol, created_at FROM " . $this->table . " ORDER BY id DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Verifica si un email ya existe (opcionalmente excluye un ID)
     */
    public function existeEmail($email, $excluirId = null) {
        if ($excluirId) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM " . $this->table . " WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $email, ':id' => $excluirId]);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM " . $this->table . " WHERE email = :email");
            $stmt->execute([':email' => $email]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function crear($nombre, $email, $password, $rol) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)");
        return $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':rol' => $rol
        ]);
    }

    public function actualizar($id, $nombre, $email, $rol, $password = null) {
        if ($password) {
            $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET nombre=:nombre, email=:email, rol=:rol, password=:password WHERE id=:id");
            return $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':rol' => $rol,
                ':password' => password_hash($password, PASSWORD_BCRYPT),
                ':id' => $id
            ]);
        } else {
            $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET nombre=:nombre, email=:email, rol=:rol WHERE id=:id");
            return $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':rol' => $rol,
                ':id' => $id
            ]);
        }
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>