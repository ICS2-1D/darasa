<?php
// Initialize the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout to 5 minutes (300 seconds)
$timeout_duration = 300; // 5 minutes in seconds

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../../frontend-darasa/auth/login.html?error=Please login first");
    exit();
}

// Check if session has expired due to inactivity
if (isset($_SESSION['last_activity'])) {
    $time_since_last_activity = time() - $_SESSION['last_activity'];
    
    if ($time_since_last_activity > $timeout_duration) {
        // Session expired, destroy it
        session_unset();
        session_destroy();
        header("Location: ../../frontend-darasa/auth/login.html?error=Session expired due to inactivity");
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Optional: Regenerate session ID periodically for security
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>