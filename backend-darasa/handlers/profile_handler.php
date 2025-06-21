<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Authenticate: ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../frontend-darasa/auth/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name']);
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

$table_name = $role === 'teacher' ? 'teachers' : 'students';

// Basic validation
if (empty($full_name)) {
    header("Location: ../../frontend-darasa/profile/profile.php?status=error&message=Full name cannot be empty.");
    exit;
}

try {
    // Update full name
    $stmt = $pdo->prepare("UPDATE {$table_name} SET full_name = ? WHERE user_id = ?");
    $stmt->execute([$full_name, $user_id]);

    // Handle password change if fields are not empty
    if (!empty($new_password) || !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            header("Location: ../../frontend-darasa/profile/profile.php?status=error&message=Passwords do not match.");
            exit;
        }
        if (strlen($new_password) < 6) {
             header("Location: ../../frontend-darasa/profile/profile.php?status=error&message=Password must be at least 6 characters long.");
            exit;
        }

        // Hash the new password and update the users table
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt_pass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt_pass->execute([$hashed_password, $user_id]);
    }

    header("Location: ../../frontend-darasa/profile/profile.php?status=success&message=Profile updated successfully.");
    exit;

} catch (PDOException $e) {
    header("Location: ../../frontend-darasa/profile/profile.php?status=error&message=" . urlencode("Database error: " . $e->getMessage()));
    exit;
}
