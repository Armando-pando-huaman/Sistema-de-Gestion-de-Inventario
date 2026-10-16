<?php
class Database {
   //private $host = "sql100.infinityfree.com";
   // private $db_name = "if0_40658765_Sistema_de_Gestion_de_Inventario";
   // private $username = "if0_40658765";
   //  private $password = "gZGM9LZ9Cy9hR3K"; // ⚠️ Reemplaza con tu contraseña real
   // public $conn;


    private $host = "localhost";
    private $db_name = "if0_40658765_Sistema_de_Gestion_de_Inventario";
    private $username = "root";
    private $password = ""; // ⚠️ Reemplaza con tu contraseña real
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>