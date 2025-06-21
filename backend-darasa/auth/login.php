<?php
session_start();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once __DIR__ . '/../connect.php';

    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, set up the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];

            // Redirect based on role
            switch ($user['role']) {
                case 'teacher':
                    header("Location: ../../frontend-darasa/dashboard/teacher.php");
                    break;
                case 'student':
                    header("Location: ../../frontend-darasa/dashboard/student.php");
                    break;
                default:
                    header("Location: ../../frontend-darasa/dashboard/student.php");
            }
            exit();
        } else {
            // Invalid credentials
            header("Location: ../../frontend-darasa/auth/login.html?error=Invalid email or password");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../../frontend-darasa/auth/login.html?error=Database error occurred");
        exit();
    }
}
?>