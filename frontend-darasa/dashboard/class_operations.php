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

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
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

// Get form data
$class_name = trim($_POST['class_name']);
$class_description = trim($_POST['class_description']);

// Validate class name
if (empty($class_name)) {
    echo json_encode(['success' => false, 'message' => 'Class name is required']);
    exit;
}

if (strlen($class_name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Class name is too long']);
    exit;
}

// Function to generate unique class code
function generateClassCode($db) {
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // No I, O to avoid confusion
    $numbers = '23456789'; // No 0, 1 to avoid confusion
    $all_chars = $letters . $numbers;
    
    do {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $random_index = rand(0, strlen($all_chars) - 1);
            $code .= $all_chars[$random_index];
        }
        
        // Check if this code already exists
        $check_query = "SELECT COUNT(*) as count FROM classes WHERE class_code = ?";
        $check_stmt = mysqli_prepare($db, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $code);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $check_row = mysqli_fetch_assoc($check_result);
        
    } while ($check_row['count'] > 0); // Keep generating until we get a unique code
    
    return $code;
}

// Generate unique class code
$class_code = generateClassCode($db);

// Insert new class into database
$insert_query = "INSERT INTO classes (name, description, class_code, teacher_id, created_at) VALUES (?, ?, ?, ?, NOW())";
$insert_stmt = mysqli_prepare($db, $insert_query);

if (!$insert_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($db)]);
    exit;
}

mysqli_stmt_bind_param($insert_stmt, "sssi", $class_name, $class_description, $class_code, $teacher_id);

if (mysqli_stmt_execute($insert_stmt)) {
    $new_class_id = mysqli_insert_id($db);
    
    echo json_encode([
        'success' => true,
        'message' => 'Class created successfully!',
        'class_id' => $new_class_id,
        'class_name' => $class_name,
        'class_code' => $class_code,
        'class_description' => $class_description
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create class: ' . mysqli_error($db)]);
}

mysqli_stmt_close($insert_stmt);
mysqli_close($db);
?>