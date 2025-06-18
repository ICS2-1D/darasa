<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../frontend-darasa/auth/login.html");
    exit;
}

// Get teacher ID
$stmt = $conn->prepare("SELECT id FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
    exit;
}

$teacher_id = $teacher['id'];
$action = $_POST['action'] ?? '';

// Generate unique class code
function generateClassCode($conn) {
    do {
        $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        $stmt = $conn->prepare("SELECT id FROM classes WHERE class_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($exists);
    return $code;
}

// Handle actions
switch ($action) {
    case 'create':
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
            exit;
        }
        
        // Check for duplicate name
        $stmt = $conn->prepare("SELECT id FROM classes WHERE name = ? AND teacher_id = ?");
        $stmt->bind_param("si", $name, $teacher_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
            exit;
        }
        $stmt->close();
        
        // Create class
        $class_code = generateClassCode($conn);
        $stmt = $conn->prepare("INSERT INTO classes (name, description, class_code, teacher_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $name, $description, $class_code, $teacher_id);
        
        if ($stmt->execute()) {
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=success");
        } else {
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
        }
        $stmt->close();
        break;
        
    case 'delete':
        $class_id = (int)($_POST['class_id'] ?? 0);
        
        if ($class_id <= 0) {
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
            exit;
        }
        
        // Verify class belongs to teacher
        $stmt = $conn->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
        $stmt->bind_param("ii", $class_id, $teacher_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
            exit;
        }
        $stmt->close();
        
        // Delete class and related data
        $conn->begin_transaction();
        try {
            // Delete student enrollments
            $stmt = $conn->prepare("DELETE FROM class_students WHERE class_id = ?");
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete class
            $stmt = $conn->prepare("DELETE FROM classes WHERE id = ? AND teacher_id = ?");
            $stmt->bind_param("ii", $class_id, $teacher_id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=success");
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
        }
        break;
        
    default:
        header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error");
        break;
}

$conn->close();
?>