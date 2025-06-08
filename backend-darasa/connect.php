<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get DB credentials from .env
$Db_Server = $_ENV['DB_SERVER'];
$Db_User = $_ENV['DB_USER'];
$Db_Password = $_ENV['DB_PASSWORD'];
$Db_Name = $_ENV['DB_NAME'];

try {
    // Create PDO connection
    $dsn = "mysql:host=$Db_Server;dbname=$Db_Name;charset=utf8mb4";
    $pdo = new PDO($dsn, $Db_User, $Db_Password);

    // Set error mode to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: success message for testing only
    // echo "Connection to the database was successful.";

} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
}
?>
