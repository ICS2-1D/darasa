<?php
// Initialize the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
 
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Store the page they were trying to access to redirect them back after login (optional)
    // $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; 
    
    header("location: ../frontend/login.html"); // Adjust path as necessary from the dashboard file
    exit;
}

// Optional: Check for specific role if the page is role-specific
// For example, on a teacher-only page:
/*
if ($_SESSION["user_role"] !== 'teacher') {
    // Redirect to a 'not authorized' page or student dashboard
    header("location: not_authorized.php"); 
    exit;
}
*/
?>
