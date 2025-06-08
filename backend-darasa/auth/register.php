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
    $checkQuery = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $numRows = mysqli_num_rows($result);

    if ($numRows == 0) {
        // Start transaction to ensure both inserts succeed or both fail
        mysqli_begin_transaction($conn);

        try {
            // Simple auto-role assignment
            $role = 'user'; // Default role
            if (preg_match('/^[a-zA-Z]+\.[a-zA-Z]+@strathmore\.edu$/', $email)) {
                $role = 'student';
            } elseif (preg_match('/^[a-zA-Z]+@strathmore\.edu$/', $email)) {
                $role = 'teacher';
            }

            // Insert user into users table with hashed password
            $insertUserQuery = "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertUserQuery);
            mysqli_stmt_bind_param($stmt, "ssss", $fullname, $email, $hashedPassword, $role);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create user");
            }

            // Get the newly created user's ID
            $user_id = mysqli_insert_id($conn);            // Insert into respective role table
            if ($role == 'student') {
                $insertRoleQuery = "INSERT INTO students (user_id, full_name, email) VALUES (?, ?, ?)";
                $stmt2 = mysqli_prepare($conn, $insertRoleQuery);
                mysqli_stmt_bind_param($stmt2, "iss", $user_id, $fullname, $email);

                if (!mysqli_stmt_execute($stmt2)) {
                    throw new Exception("Failed to create student record");
                }
            } elseif ($role == 'teacher') {
                $insertRoleQuery = "INSERT INTO teachers (user_id, full_name, email) VALUES (?, ?, ?)";
                $stmt2 = mysqli_prepare($conn, $insertRoleQuery);
                mysqli_stmt_bind_param($stmt2, "iss", $user_id, $fullname, $email);

                if (!mysqli_stmt_execute($stmt2)) {
                    throw new Exception("Failed to create teacher record");
                }
            }

            // If we get here, both inserts succeeded
            mysqli_commit($conn);

            $showAlert = true;
            header("Location: ../../frontend-darasa/auth/login.html?success=Registered successfully!");
            exit();

        } catch (Exception $e) {
            // If anything failed, roll back the transaction
            mysqli_rollback($conn);
            echo "Error creating account: " . $e->getMessage();
        }
    } else {
        // Email already exists
        header("Location: ../../frontend-darasa/auth/register.html?error=Email already registered.");
        exit();
    }
    mysqli_close($conn);
}
?>