<?php
session_start();
include_once('../../backend-darasa/connect.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please login as teacher.']);
    exit;
}

// Get the teacher ID from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT id FROM teachers WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query); // Using $conn instead of $db
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($result);

if (!$teacher) {
    echo json_encode(['success' => false, 'message' => 'Teacher record not found']);
    exit;
}

$teacher_id = $teacher['id'];

// Get all classes for this teacher with student count
$classes_query = "
    SELECT 
        c.id, 
        c.name, 
        c.description, 
        c.class_code, 
        c.created_at,
        COUNT(cs.student_id) as student_count
    FROM classes c 
    LEFT JOIN class_students cs ON c.id = cs.class_id 
    WHERE c.teacher_id = ? 
    GROUP BY c.id
    ORDER BY c.created_at DESC
";

$classes_stmt = mysqli_prepare($conn, $classes_query);
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
        'created_at' => $row['created_at'],
        'student_count' => $row['student_count']
    ];
}

echo json_encode(['success' => true, 'classes' => $classes]);

mysqli_stmt_close($classes_stmt);
mysqli_close($conn);
?>