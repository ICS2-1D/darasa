<?php
session_start();
include("connect.php");
function makeClassCode() {
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Letters without I and O to avoid confusion
    $numbers = '23456789'; // Numbers without 0 and 1 to avoid confusion
    $all_chars = $letters . $numbers;
    
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $random_position = rand(0, strlen($all_chars) - 1);
        $code .= $all_chars[$random_position];
    }
    return $code;
}

// Check if a teacher is logged in
function checkTeacherLogin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
        echo json_encode(['success' => false, 'message' => 'Please login as teacher first']);
        exit;
    }
}

// Get teacher's ID from database
function getTeacherId($db, $user_id) {
    $query = $db->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $query->execute([$user_id]);
    return $query->fetchColumn();
}

// CREATE A NEW CLASS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_class') {
    checkTeacherLogin();
    
    $teacher_id = getTeacherId($db, $_SESSION['user_id']);
    $class_name = trim($_POST['class_name']);
    
    // Validate class name
    if (empty($class_name)) {
        echo json_encode(['success' => false, 'message' => 'Class name cannot be empty']);
        exit;
    }
    
    // Generate a unique class code
    $class_code = makeClassCode();
    
    // Check if code is unique (very unlikely to repeat, but we check anyway)
    $check = $db->prepare("SELECT COUNT(*) FROM classes WHERE class_code = ?");
    $check->execute([$class_code]);
    $exists = $check->fetchColumn();
    
    if ($exists > 0) {
        echo json_encode(['success' => false, 'message' => 'Please try again, code already exists']);
        exit;
    }
    
    // Insert new class into database
    try {
        $insert = $db->prepare("INSERT INTO classes (name, class_code, teacher_id) VALUES (?, ?, ?)");
        $insert->execute([$class_name, $class_code, $teacher_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Class created successfully!',
            'class_code' => $class_code
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
    }
    exit;
}

// DELETE A CLASS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_class') {
    checkTeacherLogin();
    
    $teacher_id = getTeacherId($db, $_SESSION['user_id']);
    $class_id = intval($_POST['class_id']);
    
    // Check if class belongs to this teacher
    $check = $db->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
    $check->execute([$class_id, $teacher_id]);
    $valid_class = $check->fetchColumn();
    
    if (!$valid_class) {
        echo json_encode(['success' => false, 'message' => 'Class not found or not yours to delete']);
        exit;
    }
    
    // Delete the class
    try {
        $delete = $db->prepare("DELETE FROM classes WHERE id = ?");
        $delete->execute([$class_id]);
        
        echo json_encode(['success' => true, 'message' => 'Class deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
    }
    exit;
}

// GET ALL CLASSES FOR A TEACHER
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_classes') {
    checkTeacherLogin();
    
    $teacher_id = getTeacherId($db, $_SESSION['user_id']);
    
    try {
        // Get all classes for this teacher
        $query = $db->prepare("
            SELECT id, name, class_code, created_at 
            FROM classes 
            WHERE teacher_id = ?
            ORDER BY created_at DESC
        ");
        $query->execute([$teacher_id]);
        $classes = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'classes' => $classes]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
    }
    exit;
}

// GET STUDENTS IN A CLASS
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_class_students') {
    checkTeacherLogin();
    
    $teacher_id = getTeacherId($db, $_SESSION['user_id']);
    $class_id = intval($_GET['class_id']);
    
    // First check if this class belongs to the teacher
    $check = $db->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
    $check->execute([$class_id, $teacher_id]);
    $valid_class = $check->fetchColumn();
    
    if (!$valid_class) {
        echo json_encode(['success' => false, 'message' => 'Class not found or not yours']);
        exit;
    }
    
    // Get all students in this class
    try {
        $query = $db->prepare("
            SELECT u.id, u.fullname, u.email, s.student_number
            FROM class_students cs
            JOIN students s ON cs.student_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE cs.class_id = ?
            ORDER BY u.fullname
        ");
        $query->execute([$class_id]);
        $students = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'students' => $students]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
    }
    exit;
}

// If no valid action was found
echo json_encode(['success' => false, 'message' => 'Invalid request']);