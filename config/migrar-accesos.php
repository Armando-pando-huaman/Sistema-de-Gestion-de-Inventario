<?php
// Ejecutar una vez: C:/xampp/php/php.exe config/migrar-accesos.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/database.php';
$db = (new Database())->getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS acceso_intentos (
 clave CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
 fallos TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "Limite de acceso preparado.\n";
