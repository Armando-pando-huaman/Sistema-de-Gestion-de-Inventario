<?php
require_once '..    /config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>🔍 Diagnóstico de Login</h2>";

// 1. Verificar conexión
if (!$conn) {
    die("❌ No hay conexión a la BD");
}
echo "✅ Conexión exitosa<br><br>";

// 2. Ver usuarios en la BD
echo "<h3>Usuarios en la base de datos:</h3>";
$usuarios = $conn->query("SELECT id, nombre, email, password, rol FROM usuarios")->fetchAll();

if (empty($usuarios)) {
    echo "❌ <strong>NO HAY USUARIOS EN LA TABLA</strong>. Debes insertar uno.<br>";
} else {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Password (primeros 30)</th><th>Rol</th></tr>";
    foreach ($usuarios as $u) {
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['nombre']}</td>";
        echo "<td><strong>{$u['email']}</strong></td>";
        echo "<td>" . substr($u['password'], 0, 30) . "...</td>";
        echo "<td>{$u['rol']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

// 3. Probar verificación de contraseña
echo "<h3>Prueba de verificación con admin123:</h3>";

if (!empty($usuarios)) {
    foreach ($usuarios as $u) {
        echo "<strong>Usuario:</strong> {$u['email']}<br>";
        
        // ¿El hash es válido?
        $info = password_get_info($u['password']);
        echo "Algoritmo del hash: " . ($info['algoName'] ?? 'desconocido') . "<br>";
        
        if ($info['algoName'] === 'unknown') {
            echo "⚠️ <span style='color:red;'>La contraseña NO está hasheada (está en texto plano o formato inválido)</span><br>";
        } else {
            $verifica = password_verify('admin123', $u['password']);
            echo "¿Coincide con 'admin123'? " . ($verifica ? "✅ SÍ" : "❌ NO") . "<br>";
        }
        echo "<hr>";
    }
}

// 4. Simular el login exactamente como lo hace el modelo
echo "<h3>Simulación del login (como en el modelo):</h3>";
$emailTest = 'admin@botica.com';
$passwordTest = 'admin123';

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $emailTest]);
$user = $stmt->fetch();

if (!$user) {
    echo "❌ No se encontró ningún usuario con email: <strong>$emailTest</strong><br>";
    echo "💡 Emails disponibles: ";
    foreach ($usuarios as $u) echo "<code>{$u['email']}</code> ";
} else {
    echo "✅ Usuario encontrado: {$user['email']}<br>";
    $ok = password_verify($passwordTest, $user['password']);
    echo "Verificación de '$passwordTest': " . ($ok ? "✅ CORRECTA" : "❌ INCORRECTA") . "<br>";
}
?>