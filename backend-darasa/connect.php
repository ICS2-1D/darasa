<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$Db_Server = $_ENV['DB_SERVER'];
$Db_User = $_ENV['DB_USER'];
$Db_Password = $_ENV['DB_PASSWORD'];
$Db_Name = $_ENV['DB_NAME'];

$conn = new mysqli($Db_Server, $Db_User, $Db_Password, $Db_Name);

if ($conn->connect_error) {
    die("Connection to the database failed: " . $conn->connect_error);
} else {
    echo "Connection to the database was successful.";
}
?>
