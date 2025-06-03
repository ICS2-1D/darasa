<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
if (session_destroy()) {
    // Optionally, clear session cookie if used explicitly
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}
 
// Redirect to login page
header("location: ../frontend/login.html");
exit;
?>
