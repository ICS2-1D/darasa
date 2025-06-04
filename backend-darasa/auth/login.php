<?php
session_start();

// Include database connection
require_once __DIR__ . '/../connect.php';

$errors = [];
$email = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $errors[] = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If there are no errors, proceed with login
    if (empty($errors)) {
        // Prepare a select statement to get user details
        $sql_login = "SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?";
        
        if ($stmt_login = $conn->prepare($sql_login)) {
            $stmt_login->bind_param("s", $param_email);
            $param_email = $email;
            
            if ($stmt_login->execute()) {
                $stmt_login->store_result();
                
                if ($stmt_login->num_rows == 1) {
                    // Bind result variables
                    $stmt_login->bind_result($id, $full_name, $email, $password_hash, $role);
                    
                    if ($stmt_login->fetch()) {
                        // Verify password
                        if (password_verify($password, $password_hash)) {
                            // Password is correct, start a new session
                            session_regenerate_id(true);
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["email"] = $email;
                            $_SESSION["role"] = $role;
                            
                            // Redirect user to dashboard based on role
                            if ($role == 'student') {
                                header("location: ../../frontend-darasa/dashboard/student.html");
                            } elseif ($role == 'teacher') {
                                header("location: ../../frontend-darasa/dashboard/teacher.html");
                            } else {
                                header("location: ../../frontend-darasa/dashboard/dashboard.html");
                            }
                            exit();
                        } else {
                            // Password is not valid
                            $errors[] = "Invalid email or password.";
                        }
                    }
                } else {
                    // No user found with this email
                    $errors[] = "Invalid email or password.";
                }
            } else {
                $errors[] = "Oops! Something went wrong. Please try again later.";
                error_log("Error executing login query: " . $stmt_login->error);
            }
            $stmt_login->close();
        } else {
            $errors[] = "Database error preparing login query.";
            error_log("Error preparing login query: " . $conn->error);
        }
    }

    // If there were errors, redirect back with error messages
    if (!empty($errors)) {
        $error_string = implode('. ', $errors);
        header("location: ../../frontend-darasa/auth/login.html?error=" . urlencode($error_string));
        exit();
    }

    $conn->close();
} else {
    // If not a POST request, redirect to login page
    header("location: ../../frontend-darasa/auth/login.html");
    exit();
}
?>