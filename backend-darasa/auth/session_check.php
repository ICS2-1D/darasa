<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../connect.php';

// Set session timeout to 3 minutes (180 seconds)
$timeout_duration = 180;

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    header("Location: ../../frontend-darasa/auth/login.html?error=Please login first");
    exit();
}

try {
    // Verify user still exists in database and has correct role
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User no longer exists in database
        session_unset();
        session_destroy();
        header("Location: ../../frontend-darasa/auth/login.html?error=Invalid session");
        exit();
    }

    // Verify role matches
    if ($user['role'] !== $_SESSION['role']) {
        session_unset();
        session_destroy();
        header("Location: ../../frontend-darasa/auth/login.html?error=Invalid session role");
        exit();
    }

    // Check if session has expired due to inactivity
    if (isset($_SESSION['last_activity'])) {
        $time_since_last_activity = time() - $_SESSION['last_activity'];

        if ($time_since_last_activity > $timeout_duration) {
            // Session expired, destroy it
            session_unset();
            session_destroy();
            header("Location: ../../frontend-darasa/auth/login.html?error=Session expired after 3 minutes of inactivity");
            exit();
        }
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();

    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }

} catch (PDOException $e) {
    error_log("Session check error: " . $e->getMessage());
    session_unset();
    session_destroy();
    header("Location: ../../frontend-darasa/auth/login.html?error=Database error occurred");
    exit();
}
?>