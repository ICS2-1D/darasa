<?php
session_start();

$showAlert = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once __DIR__ . '/../connect.php';
    
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password before storing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkQuery);
    $numRows = mysqli_num_rows($result);

    if ($numRows == 0) {
        // Simple auto-role assignment
        $role = 'user'; // Default role
        if (preg_match('/^[a-zA-Z]+\.[a-zA-Z]+@strathmore\.edu$/', $email)) {
            $role = 'student';
        } elseif (preg_match('/^[a-zA-Z]+@strathmore\.edu$/', $email)) {
            $role = 'teacher';
        }

        // Insert user into database with hashed password
        $insertQuery = "INSERT INTO users (fullname, email, password, role)
                VALUES ('$fullname', '$email', '$hashedPassword', '$role')";


        if (mysqli_query($conn, $insertQuery)) {
            $showAlert = true;
            header("Location: ../../frontend-darasa/auth/login.html?success=Registered successfully!");
            exit();
        } else {
            echo "Error inserting user: " . mysqli_error($conn);
        }
    } else {
        // Email already exists
        header("Location: ../../frontend-darasa/auth/register.html?error=Email already registered.");
        exit();
    }

    mysqli_close($conn);
}
?>