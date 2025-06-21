<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../frontend-darasa/auth/login.html");
    exit;
}

try {
    // Get student ID
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=Student profile not found");
        exit;
    }

    $student_id = $student['id'];
    $action = $_POST['action'] ?? '';

    // Handle actions
    switch ($action) {
        case 'join':
            $class_code = strtoupper(trim($_POST['class_code'] ?? ''));

            if (empty($class_code) || strlen($class_code) !== 6) {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=Please enter a valid 6-character class code");
                exit;
            }

            // Find class by code
            $stmt = $pdo->prepare("SELECT id, name FROM classes WHERE class_code = ?");
            $stmt->execute([$class_code]);
            $class = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$class) {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=Class not found. Please check the class code");
                exit;
            }

            // Check if already enrolled
            $stmt = $pdo->prepare("SELECT id FROM class_students WHERE class_id = ? AND student_id = ?");
            $stmt->execute([$class['id'], $student_id]);

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=You are already enrolled in this class");
                exit;
            }

            // Enroll student
            $stmt = $pdo->prepare("INSERT INTO class_students (class_id, student_id, enrolled_at) VALUES (?, ?, NOW())");

            if ($stmt->execute([$class['id'], $student_id])) {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=success&message=Successfully joined " . urlencode($class['name']));
            } else {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=Failed to join class. Please try again");
            }
            break;

        case 'leave':
            $class_id = (int) ($_POST['class_id'] ?? 0);

            if ($class_id <= 0) {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=Invalid class ID");
                exit;
            }

            // Verify student is enrolled in this class
            $stmt = $pdo->prepare("SELECT c.name FROM classes c JOIN class_students cs ON c.id = cs.class_id WHERE c.id = ? AND cs.student_id = ?");
            $stmt->execute([$class_id, $student_id]);
            $class = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$class) {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=You are not enrolled in this class");
                exit;
            }

            // Remove student from class
            $stmt = $pdo->prepare("DELETE FROM class_students WHERE class_id = ? AND student_id = ?");

            if ($stmt->execute([$class_id, $student_id])) {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=success&message=Successfully left " . urlencode($class['name']));
            } else {
                header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=Failed to leave class. Please try again");
            }
            break;

        default:
            header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=Invalid action");
            break;
    }
} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("Database error: " . $e->getMessage());
    header("Location: ../../frontend-darasa/dashboard/student.php?status=error&message=A database error occurred. Please try again later.");
    exit;
}
?>