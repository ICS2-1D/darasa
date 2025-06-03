<?php
// Basic routing logic
$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/':
        require __DIR__ . '/../controllers/HomeController.php';
        break;
    case '/login':
        require __DIR__ . '/../controllers/AuthController.php';
        break;
    // Add more routes here
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
