<?php
// Ejecutar una vez: C:/xampp/php/php.exe config/migrar-identidad.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/database.php';
$db = (new Database())->getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS botica (
 id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
 nombre VARCHAR(150) NOT NULL,
 registro_habilitado TINYINT(1) NOT NULL DEFAULT 0,
 logo VARCHAR(100) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$columns = $db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('foto', $columns, true)) $db->exec('ALTER TABLE usuarios ADD COLUMN foto VARCHAR(100) NULL');
echo "Migracion de identidad lista.\n";
