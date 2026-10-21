<?php if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de conexión paso a paso</h1>";

$host     = "localhost";
$db_name  = "if0_40658765_Sistema_de_Gestion_de_Inventario";
$username = "root";
$password = "";

// ============ PASO 1: ¿Se resuelve el HOST? ============
echo "<h2>PASO 1: Resolución del HOST</h2>";
$ip = gethostbyname($host);
if ($ip === $host) {
    echo "<p style='color:red'>❌ El host <strong>$host</strong> NO se resuelve.</p>";
    echo "<p>Esto significa que el problema está en el <strong>NOMBRE DEL SERVIDOR</strong>.</p>";
    echo "<p>Posibles causas: el host está mal escrito, el servidor MySQL está caído, o tu cuenta fue suspendida.</p>";
    exit; // Detenemos aquí
} else {
    echo "<p style='color:green'>✅ Host resuelto correctamente. IP: <strong>$ip</strong></p>";
}

// ============ PASO 2: ¿Se puede conectar al servidor MySQL? ============
echo "<h2>PASO 2: Conexión al servidor MySQL (sin base de datos)</h2>";
try {
    $conn = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✅ Usuario y contraseña son CORRECTOS.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Falló la conexión al servidor MySQL.</p>";
    echo "<p><strong>Mensaje de error:</strong> " . $e->getMessage() . "</p>";

    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<p>🔍 <strong>Diagnóstico:</strong> El problema está en el <strong>USUARIO o la CONTRASEÑA</strong>.</p>";
    } elseif (strpos($e->getMessage(), 'Unknown MySQL server host') !== false) {
        echo "<p>🔍 <strong>Diagnóstico:</strong> El problema está en el <strong>HOST</strong>.</p>";
    } else {
        echo "<p>🔍 <strong>Diagnóstico:</strong> Error desconocido, revisa el mensaje arriba.</p>";
    }
    exit;
}

// ============ PASO 3: ¿Existe la BASE DE DATOS? ============
echo "<h2>PASO 3: Selección de la BASE DE DATOS</h2>";
try {
    $conn->exec("USE `$db_name`");
    echo "<p style='color:green'>✅ La base de datos <strong>$db_name</strong> existe y es accesible.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ La base de datos <strong>$db_name</strong> NO existe o no tienes acceso.</p>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p>🔍 <strong>Diagnóstico:</strong> El problema está en el <strong>NOMBRE DE LA BASE DE DATOS</strong>.</p>";
    exit;
}

// ============ PASO 4: ¿Hay tablas? ============
echo "<h2>PASO 4: Contenido de la base de datos</h2>";
$stmt = $conn->query("SHOW TABLES");
$tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<p>Tablas encontradas: <strong>" . count($tablas) . "</strong></p>";
foreach ($tablas as $t) {
    echo "• $t<br>";
}

echo "<h1 style='color:green'>🎉 TODO ESTÁ CORRECTO</h1>";
?>