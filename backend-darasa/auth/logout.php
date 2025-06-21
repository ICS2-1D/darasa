<?php
session_start();
require_once __DIR__ . '/../connect.php';

try {
    // Log the logout event if needed
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET last_logout = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }

    // Clear all session variables
    $_SESSION = array();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: ../../frontend-darasa/auth/login.html?success=Logged out successfully");
    exit();

} catch (PDOException $e) {
    error_log("Logout error: " . $e->getMessage());
    // Still destroy the session even if database update fails
    session_destroy();
    header("Location: ../../frontend-darasa/auth/login.html?success=Logged out successfully");
    exit();
}
?>