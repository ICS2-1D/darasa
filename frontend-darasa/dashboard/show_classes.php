<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Start session and include database connection
session_start();
include_once('../../backend-darasa/connect.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Please login as teacher first']);
    exit;
}

// Get the teacher ID from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT id FROM teachers WHERE user_id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($result);

if (!$teacher) {
    echo json_encode(['success' => false, 'message' => 'Teacher record not found']);
    exit;
}

$teacher_id = $teacher['id'];

// Get all classes for this teacher
$classes_query = "SELECT id, name, description, class_code, created_at FROM classes WHERE teacher_id = ? ORDER BY created_at DESC";
$classes_stmt = mysqli_prepare($db, $classes_query);
mysqli_stmt_bind_param($classes_stmt, "i", $teacher_id);
mysqli_stmt_execute($classes_stmt);
$classes_result = mysqli_stmt_get_result($classes_stmt);

$classes = [];
while ($row = mysqli_fetch_assoc($classes_result)) {
    $classes[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'class_code' => $row['class_code'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode(['success' => true, 'classes' => $classes]);

mysqli_stmt_close($classes_stmt);
mysqli_close($db);
?>