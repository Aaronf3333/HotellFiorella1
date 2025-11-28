<?php
// Cargar la configuración desde el archivo .ini
$config = parse_ini_file(__DIR__ . '/../config.ini');

$serverName = $config['DB_SERVER'];
$database = $config['DB_DATABASE'];
$uid = $config['DB_USER'];
$pwd = $config['DB_PASSWORD'];

// Cadena de conexión para PDO SQLSRV
$connectionString = "sqlsrv:server=$serverName;database=$database";

try {
    // Crear una instancia de PDO
    $pdo = new PDO($connectionString, $uid, $pwd);
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la conexión falla, muestra un mensaje de error y detiene el script
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>