<?php
// db.php - Conexão com banco MySQL

define('DB_HOST', 'localhost');
define('DB_NAME', 'cifras');
define('DB_USER', 'root');
define('DB_PASS', ''); // ajuste conforme seu ambiente

try {
  $pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $e) {
  die("Erro na conexão com banco de dados: " . $e->getMessage());
}
