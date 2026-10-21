<?php
date_default_timezone_set('America/Lima');
class Database {


    private $host = "localhost";
    private $db_name = "if0_40658765_Sistema_de_Gestion_de_Inventario";
    private $username = "root";
    private $password = ""; // ⚠️ Reemplaza con tu contraseña real
    public $conn;
    public function __construct() {
        $this->host = getenv('DB_HOST') ?: $this->host;
        $this->db_name = getenv('DB_NAME') ?: $this->db_name;
        $this->username = getenv('DB_USER') ?: $this->username;
        if (getenv('DB_PASSWORD') !== false) $this->password = getenv('DB_PASSWORD');
    }

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
            $this->conn->exec("SET time_zone='-05:00'");
        } catch(PDOException $e) {
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            if (!headers_sent()) {
                http_response_code(503);
                header('Content-Type: text/html; charset=UTF-8');
                header('Cache-Control: no-store');
                header('Retry-After: 60');
            }
            require __DIR__ . '/../views/errors/database-unavailable.php';
            exit;
        }
        return $this->conn;
    }
}
?>
