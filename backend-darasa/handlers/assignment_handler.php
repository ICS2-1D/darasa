<?php
session_start();
require_once __DIR__ . '/../connect.php';

// --- Universal Checks ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Authentication required.']));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Invalid request method.']));
}

// --- Action and Role-Based Routing ---
$action = $_POST['action'] ?? '';
$role = $_SESSION['role'];

// == TEACHER ACTIONS =======================================================

if ($action === 'create' && $role === 'teacher') {
    // Logic for creating assignments... (as before)
    if (!isset($_POST['title'], $_POST['class_id'], $_POST['due_date'], $_POST['max_points'])) die("Missing fields.");
    $title = trim($_POST['title']);
    $class_id = filter_var($_POST['class_id'], FILTER_VALIDATE_INT);
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'];
    $max_points = filter_var($_POST['max_points'], FILTER_VALIDATE_INT);
    $file_path = null;
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['assignment_file'];
        $upload_dir = __DIR__ . '/../../uploads/assignments/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $filename = uniqid('assign_', true) . '-' . basename($file['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $file_path = 'uploads/assignments/' . $filename;
        } else {
            die("Error: Could not upload assignment file.");
        }
    }
    try {
        $sql = "INSERT INTO assignments (class_id, title, description, due_date, max_points, file_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$class_id, $title, $description, $due_date, $max_points, $file_path]);
        header("Location: ../../frontend-darasa/assignments/view-assignments.php?status=success");
        exit;
    } catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }
} 
elseif ($action === 'delete' && $role === 'teacher') {
    // Logic for deleting assignments... (as before)
    $assignment_id = filter_var($_POST['assignment_id'], FILTER_VALIDATE_INT);
    if(!$assignment_id) die("Invalid assignment ID.");
    try {
        $stmt = $pdo->prepare("SELECT file_path FROM assignments WHERE id = ?");
        $stmt->execute([$assignment_id]);
        $assignment = $stmt->fetch();
        if ($assignment && !empty($assignment['file_path'])) {
            $filepath = __DIR__ . '/../../' . $assignment['file_path'];
            if (file_exists($filepath)) { unlink($filepath); }
        }
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
        $stmt->execute([$assignment_id]);
        header("Location: ../../frontend-darasa/assignments/view-assignments.php?deleted=success");
        exit;
    } catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }
}
elseif ($action === 'grade_submission' && $role === 'teacher') {
    // --- NEW LOGIC FOR GRADING ---
    $submission_id = filter_var($_POST['submission_id'], FILTER_VALIDATE_INT);
    $assignment_id = filter_var($_POST['assignment_id'], FILTER_VALIDATE_INT); // For redirect
    $grade = filter_var($_POST['grade'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $feedback = trim($_POST['feedback'] ?? '');

    if (!$submission_id || !$assignment_id) {
        die("Error: Missing submission or assignment ID.");
    }
    // Allow grade to be 0, but check for false on failure
    if ($grade === false) {
        $grade = null; // Store as NULL if input is invalid or empty
    }

    try {
        $sql = "UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$grade, $feedback, $submission_id]);

        // Redirect back to the grading page with a success message
        header("Location: ../../frontend-darasa/assignments/view-submissions.php?assignment_id=$assignment_id&grade_status=success");
        exit;

    } catch (PDOException $e) {
        error_log("Grade submission failed: " . $e->getMessage());
        die("An error occurred while saving the grade.");
    }
}

// == STUDENT ACTIONS =======================================================
elseif ($action === 'submit_student' && $role === 'student') {
    // Logic for student submissions... (as before)
    if (!isset($_POST['assignment_id'], $_POST['student_id'], $_POST['class_id'])) die("Missing fields.");
    $assignment_id = filter_var($_POST['assignment_id'], FILTER_VALIDATE_INT);
    $student_id = filter_var($_POST['student_id'], FILTER_VALIDATE_INT);
    $class_id = filter_var($_POST['class_id'], FILTER_VALIDATE_INT);
    $submission_text = trim($_POST['submission_text'] ?? '');
    $submission_file_path = null;
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['submission_file'];
        $upload_dir = __DIR__ . '/../../uploads/submissions/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $filename = uniqid('sub_', true) . '-' . $student_id . '-' . basename($file['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $submission_file_path = 'uploads/submissions/' . $filename;
        } else {
            die("Error: Could not upload submission file.");
        }
    }
    if (empty($submission_text) && is_null($submission_file_path)) {
        header("Location: ../../frontend-darasa/assignments/view-assignment-student.php?class_id=$class_id&submit_status=error&message=No+content+submitted");
        exit;
    }
    try {
        $sql = "INSERT INTO submissions (assignment_id, student_id, submission_text, file_path, submitted_at) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE submission_text = VALUES(submission_text), file_path = VALUES(file_path), submitted_at = NOW()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$assignment_id, $student_id, $submission_text, $submission_file_path]);
        header("Location: ../../frontend-darasa/assignments/view-assignment-student.php?class_id=$class_id&submit_status=success");
        exit;
    } catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }
}

// == FALLBACK ================================================================
else {
    http_response_code(400);
    die("Invalid action or insufficient permissions for this request.");
}
