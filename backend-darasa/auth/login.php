<?php
session_start();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Database connection
    $dsn = "mysql:host=localhost;dbname=darasa";
    $username = "root";
    $db_password = "";

    try {
        $pdo = new PDO($dsn, $username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Check if user exists in database using prepared statement
        $checkQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // User found, check if password is correct
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
            header("Location: ../../frontend-darasa/auth/login.html?error=User not found.");
            exit();
        }

    } catch (PDOException $e) {
        echo "Login error: " . $e->getMessage();
    }
}
?>