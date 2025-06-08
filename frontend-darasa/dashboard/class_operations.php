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

// Check request method
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get action from POST data
$action = $_POST['action'] ?? '';

// Get teacher ID
function getTeacherId($conn, $user_id) {
    $query = "SELECT id FROM teachers WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $teacher = mysqli_fetch_assoc($result);
    return $teacher ? $teacher['id'] : null;
}

// Generate unique class code
function generateClassCode($conn) {
    do {
        $class_code = strtoupper(substr(md5(uniqid() . rand()), 0, 6));
        $check_query = "SELECT COUNT(*) as count FROM classes WHERE class_code = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $class_code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
    } while ($row['count'] > 0);
    
    return $class_code;
}

$teacher_id = getTeacherId($conn, $_SESSION['user_id']);
if (!$teacher_id) {
    echo json_encode(['success' => false, 'message' => 'Teacher record not found']);
    exit;
}

switch ($action) {
    case 'create':
        // Validate input
        $class_name = trim($_POST['class_name'] ?? '');
        $class_description = trim($_POST['class_description'] ?? '');
        
        if (empty($class_name)) {
            echo json_encode(['success' => false, 'message' => 'Class name is required']);
            exit;
        }
        
        if (strlen($class_name) > 100) {
            echo json_encode(['success' => false, 'message' => 'Class name is too long (max 100 characters)']);
            exit;
        }
        
        // Generate unique class code
        $class_code = generateClassCode($conn);
        
        // Insert new class
        $query = "INSERT INTO classes (name, description, class_code, teacher_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, "sssi", $class_name, $class_description, $class_code, $teacher_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Class created successfully!',
                'class_id' => mysqli_insert_id($conn),
                'class_name' => $class_name,
                'class_code' => $class_code,
                'class_description' => $class_description
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create class: ' . mysqli_stmt_error($stmt)]);
        }
        
        mysqli_stmt_close($stmt);
        break;
        
    case 'update':
        $class_id = intval($_POST['class_id'] ?? 0);
        $class_name = trim($_POST['class_name'] ?? '');
        $class_description = trim($_POST['class_description'] ?? '');
        
        if (empty($class_name) || $class_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Class name and valid class ID are required']);
            exit;
        }
        
        // Check if class belongs to this teacher
        $check_query = "SELECT id FROM classes WHERE id = ? AND teacher_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "ii", $class_id, $teacher_id);
        mysqli_stmt_execute($check_stmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) === 0) {
            echo json_encode(['success' => false, 'message' => 'Class not found or access denied']);
            exit;
        }
        
        // Update class
        $update_query = "UPDATE classes SET name = ?, description = ? WHERE id = ? AND teacher_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ssii", $class_name, $class_description, $class_id, $teacher_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo json_encode(['success' => true, 'message' => 'Class updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update class']);
        }
        
        mysqli_stmt_close($update_stmt);
        break;
        
    case 'delete':
        $class_id = intval($_POST['class_id'] ?? 0);
        
        if ($class_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid class ID is required']);
            exit;
        }
        
        // Check if class belongs to this teacher
        $check_query = "SELECT id FROM classes WHERE id = ? AND teacher_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "ii", $class_id, $teacher_id);
        mysqli_stmt_execute($check_stmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) === 0) {
            echo json_encode(['success' => false, 'message' => 'Class not found or access denied']);
            exit;
        }
        
        // Delete class
        $delete_query = "DELETE FROM classes WHERE id = ? AND teacher_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "ii", $class_id, $teacher_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            echo json_encode(['success' => true, 'message' => 'Class deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete class']);
        }
        
        mysqli_stmt_close($delete_stmt);
        break;
        
    case 'get':
        // Get all classes for this teacher
        $query = "SELECT id, name, description, class_code, created_at FROM classes WHERE teacher_id = ? ORDER BY created_at DESC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $teacher_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $classes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $classes[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'class_code' => $row['class_code'],
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode(['success' => true, 'classes' => $classes]);
        mysqli_stmt_close($stmt);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
        break;
}

mysqli_close($conn);
?>