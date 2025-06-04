<?php
// Database configuration
define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASSWORD", '@rem$Adrian123');
define("DB_NAME", "darasa");

// Create connection directly (not as a function)
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character handling
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error setting charset: " . $conn->error);
}
?>