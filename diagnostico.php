<?php
// ============================================================
//  diagnostico.php — ARCHIVO TEMPORAL, borrar después de usar
// ============================================================
session_start();

echo "<h2>Estado de la sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Variables de entorno BD</h2>";
echo "DB_HOST: " . (getenv('DB_HOST') ? '✓ definida' : '✗ NO definida') . "<br>";
echo "DB_PORT: " . (getenv('DB_PORT') ? '✓ definida' : '✗ NO definida') . "<br>";
echo "DB_NAME: " . (getenv('DB_NAME') ? '✓ definida' : '✗ NO definida') . "<br>";
echo "DB_USER: " . (getenv('DB_USER') ? '✓ definida' : '✗ NO definida') . "<br>";
echo "DB_PASSWORD: " . (getenv('DB_PASSWORD') ? '✓ definida' : '✗ NO definida') . "<br>";

echo "<h2>Test de conexión BD</h2>";
try {
    $host     = getenv('DB_HOST');
    $port     = getenv('DB_PORT') ?: '5432';
    $dbname   = getenv('DB_NAME');
    $user     = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✓ Conexión exitosa<br>";

    echo "<h2>Test de consulta usuario admin</h2>";
    $stmt = $pdo->prepare("SELECT id_empleado, email, activo, password_hash FROM usuario_empleado WHERE email = :email");
    $stmt->execute([':email' => 'admin@aguadelrey.com']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "✓ Usuario encontrado<br>";
        echo "id_empleado: " . $row['id_empleado'] . "<br>";
        echo "activo: " . ($row['activo'] ? 'true' : 'false') . "<br>";
        echo "password_hash existe: " . (!empty($row['password_hash']) ? 'sí' : 'no') . "<br>";
        echo "password_verify('password', hash): " . (password_verify('password', $row['password_hash']) ? '✓ OK' : '✗ FALLA') . "<br>";
    } else {
        echo "✗ Usuario NO encontrado en la BD<br>";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test POST simulado</h2>";
echo "REQUEST_METHOD actual: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "POST vacío: " . (empty($_POST) ? 'sí' : 'no') . "<br>";
?>
