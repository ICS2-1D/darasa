<?php
session_start();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Include database connection
    require_once __DIR__ . '/../connect.php';

    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists in database
    $checkQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkQuery);
    $numRows = mysqli_num_rows($result);

    if ($numRows == 1) {
        // User found, get user data
        $user = mysqli_fetch_assoc($result);
          // Check if password is correct
        if (password_verify($password, $user['password'])) {
            // Password is correct, login the user
            $_SESSION["loggedin"] = true;
            $_SESSION['last_activity'] = time();
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["id"] = $user['id'];
            $_SESSION["fullname"] = $user['fullname'];
            $_SESSION["email"] = $user['email'];
            $_SESSION["role"] = $user['role'];
            
            // Redirect based on user role
            if ($user['role'] == 'student') {
                header("Location: ../../frontend-darasa/dashboard/student.php");
            } elseif ($user['role'] == 'teacher') {
                header("Location: ../../frontend-darasa/dashboard/teacher.php");
            } else {
                header("Location: ../../frontend-darasa/dashboard/dashboard.html");
            }
            exit();
        } else {
            // Wrong password
            header("Location: ../../frontend-darasa/auth/login.html?error=Wrong password.");
            exit();
        }
    } else {
        // User not found
        mysqli_close($conn);
        header("Location: ../../frontend-darasa/auth/login.html?error=User not found.");
        exit();
    }
}
?>