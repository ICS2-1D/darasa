<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Authenticate: ensure user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized action.']));
}

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    // Validation
    if (!isset($_POST['class_id'], $_POST['title'], $_POST['content']) || empty(trim($_POST['title'])) || empty(trim($_POST['content']))) {
        die("Error: All fields are required.");
    }

    $class_id = filter_var($_POST['class_id'], FILTER_VALIDATE_INT);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $teacher_id = null;

    try {
        // Get teacher_id from session user_id
        $stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $teacher = $stmt->fetch();

        if (!$teacher) {
            die("Error: Teacher profile not found.");
        }
        $teacher_id = $teacher['id'];

        // Insert into database
        $sql = "INSERT INTO announcements (class_id, teacher_id, title, content) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$class_id, $teacher_id, $title, $content]);

        // Redirect back to announcements page with a success message
        header("Location: ../../frontend-darasa/announcements/announcements.php?status=success");
        exit;

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    // Handle other future actions like 'delete' if needed
    http_response_code(400);
    die("Invalid action.");
}
