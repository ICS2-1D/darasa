<?php
session_start();

// Include the database connection
require_once __DIR__ . '/../connect.php';


$errors = [];
$full_name = '';
$email = '';
$role = '';


// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Full Name
    if (empty(trim($_POST["full_name"]))) {
        $errors[] = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
        if (!preg_match("/^[a-zA-Z\s'-]+$/", $full_name)) {
            $errors[] = "Full name can only contain letters, spaces, hyphens, and apostrophes.";
        }
    }

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        } else {
            // Check if email domain is strathmore.edu
            if (!preg_match('/@strathmore\.edu$/', $email)) {
                $errors[] = "Please use a valid Strathmore University email address.";
            } else {
                // Automatically determine role based on email format
                if (preg_match('/^[a-zA-Z]+\.[a-zA-Z]+@strathmore\.edu$/', $email)) {
                    $role = 'student';
                } elseif (preg_match('/^[a-zA-Z]+@strathmore\.edu$/', $email)) {
                    $role = 'teacher';
                } else {
                    $errors[] = "Invalid email format. Use firstname.lastname@strathmore.edu for students or firstname@strathmore.edu for teachers.";
                }

                // Check if email already exists
                if (empty($errors)) {
                    $sql_check_email = "SELECT id FROM users WHERE email = ?";
                    if ($stmt_check_email = $conn->prepare($sql_check_email)) {
                        $stmt_check_email->bind_param("s", $param_email);
                        $param_email = $email;
                        if ($stmt_check_email->execute()) {
                            $stmt_check_email->store_result();
                            if ($stmt_check_email->num_rows == 1) {
                                $errors[] = "This email address is already registered.";
                            }
                        } else {
                            $errors[] = "Oops! Something went wrong checking email. Please try again later.";
                        }
                        $stmt_check_email->close();
                    } else {
                        $errors[] = "Database error preparing email check.";
                    }
                }
            }
        }
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $errors[] = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $errors[] = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If there are no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Prepare an insert statement
        $sql_insert_user = "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)";
        
        if ($stmt_insert_user = $conn->prepare($sql_insert_user)) {
            $stmt_insert_user->bind_param("ssss", $param_fullname, $param_email, $param_password_hash, $param_role);
            
            $param_fullname = $full_name;
            $param_email = $email;
            $param_password_hash = $password_hash;
            $param_role = $role;
            
            if ($stmt_insert_user->execute()) {
                // Registration successful - redirect to login with success message
                header("location: ../../frontend-darasa/auth/login.html?success=" . urlencode("Registration successful! You can now log in."));
                exit();
            } else {
                $errors[] = "Oops! Something went wrong. Please try again later.";
                error_log("Error executing user insert: " . $stmt_insert_user->error);
            }
            $stmt_insert_user->close();
        } else {
            $errors[] = "Database error preparing user insert.";
            error_log("Error preparing user insert: " . $conn->error);
        }
    }

    // If there were errors, redirect back with error messages
    if (!empty($errors)) {
        $error_string = implode('. ', $errors);
        header("location: ../../frontend-darasa/auth/register.html?error=" . urlencode($error_string));
        exit();
    }

    $conn->close();
} else {
    // If not a POST request, redirect to registration page
    header("location: ../../frontend-darasa/auth/register.html");
    exit();
}
?>