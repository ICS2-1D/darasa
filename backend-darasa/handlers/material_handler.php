<?php
session_start();
require_once __DIR__ . '/../connect.php';

// --- Authentication & Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized action.']));
}

$action = $_POST['action'] ?? '';

// --- ACTION: UPLOAD MATERIAL ---
if ($action === 'upload') {
    // --- Validation ---
    if (!isset($_POST['title'], $_POST['class_id']) || empty($_POST['title']) || !isset($_FILES['material_file'])) {
        header("Location: ../../frontend-darasa/materials/upload-material.php?status=error&message=Missing required fields.");
        exit;
    }

    $title = trim($_POST['title']);
    $class_id = filter_var($_POST['class_id'], FILTER_VALIDATE_INT);
    $file = $_FILES['material_file'];

    // --- File Validation ---
    // 1. Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../../frontend-darasa/materials/upload-material.php?status=error&message=File upload error. Code: {$file['error']}");
        exit;
    }

    // 2. Check file size (10MB limit)
    $max_size = 10 * 1024 * 1024; // 10 MB
    if ($file['size'] > $max_size) {
        header("Location: ../../frontend-darasa/materials/upload-material.php?status=error&message=File is too large. Max size is 10MB.");
        exit;
    }

    // 3. Check file type
    $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        header("Location: ../../frontend-darasa/materials/upload-material.php?status=error&message=Invalid file type. Allowed: " . implode(', ', $allowed_types));
        exit;
    }

    // --- File Processing ---
    $upload_dir = __DIR__ . '/../../uploads/materials/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    // Create a unique, safe filename
    $filename = uniqid('material_', true) . '.' . $file_ext;
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // --- Database Insertion ---
        $db_path = 'uploads/materials/' . $filename;
        try {
            $sql = "INSERT INTO class_materials (class_id, title, file_path) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$class_id, $title, $db_path]);
            
            // Redirect to listing page on success
            header("Location: ../../frontend-darasa/materials/materials.php?status=success");
            exit;
        } catch (PDOException $e) {
            // If DB insert fails, delete the uploaded file
            unlink($target_path);
            header("Location: ../../frontend-darasa/materials/upload-material.php?status=error&message=Database error: " . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: ../../frontend-darasa/materials/upload-material.php?status=error&message=Failed to move uploaded file.");
        exit;
    }
}

// --- ACTION: DELETE MATERIAL ---
elseif ($action === 'delete') {
    $material_id = filter_input(INPUT_POST, 'material_id', FILTER_VALIDATE_INT);
    if (!$material_id) { die("Invalid ID."); }

    try {
        // First, get the file path to delete the physical file
        $stmt = $pdo->prepare("SELECT file_path FROM class_materials WHERE id = ?");
        $stmt->execute([$material_id]);
        $material = $stmt->fetch();

        // Delete the database record
        $delete_stmt = $pdo->prepare("DELETE FROM class_materials WHERE id = ?");
        $delete_stmt->execute([$material_id]);

        // If record deleted successfully, delete the file from the server
        if ($delete_stmt->rowCount() > 0 && $material && !empty($material['file_path'])) {
            $physical_file_path = __DIR__ . '/../../' . $material['file_path'];
            if (file_exists($physical_file_path)) {
                unlink($physical_file_path);
            }
        }
        
        header("Location: ../../frontend-darasa/materials/materials.php?deleted=success");
        exit;

    } catch (PDOException $e) {
        header("Location: ../../frontend-darasa/materials/materials.php?status=error&message=Failed to delete material.");
        exit;
    }
}
?>
