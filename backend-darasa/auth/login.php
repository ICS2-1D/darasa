<?php
// Start session
session_start();

// Include database connection
require_once 'connect.php';

$email = ''; // Initialize for repopulating form if needed
$errors = [];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email/username (assuming email is used for login)
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $errors[] = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no validation errors, proceed to check credentials
    if (empty($errors)) {
        // Prepare a select statement
        $sql = "SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if email exists, if yes then verify password
                if ($stmt->num_rows == 1) {                    
                    // Bind result variables
                    $stmt->bind_result($id, $full_name_db, $email_db, $hashed_password_db, $role_db);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password_db)) {
                            // Password is correct, so start a new session
                            // session_regenerate_id(); // Regenerate session ID for security
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["user_full_name"] = $full_name_db;
                            $_SESSION["user_email"] = $email_db;
                            $_SESSION["user_role"] = $role_db;                            
                            
                            // Redirect user to appropriate dashboard based on role
                            if ($role_db == 'teacher') {
                                header("location: ../frontend/teacher_dashboard.html");
                                exit();
                            } elseif ($role_db == 'student') {
                                header("location: ../frontend/student_dashboard.html");
                                exit();
                            } else {
                                // Should not happen if role is validated
                                $errors[] = "Unknown user role.";
                            }
                        } else {
                            // Password is not valid
                            $errors[] = "The password you entered was not valid.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $errors[] = "No account found with that email address.";
                }
            } else {
                $errors[] = "Oops! Something went wrong. Please try again later.";
                // error_log("Error executing login select: " . $stmt->error);
            }
            // Close statement
            $stmt->close();
        } else {
            $errors[] = "Database error preparing login select.";
            // error_log("Error preparing login select: " . $conn->error);
        }
    }
    
    // If there were errors, store them in session and redirect back to login form
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_form_data'] = ['email' => $email]; // Store email to repopulate
        header("location: ../frontend/login.html"); // Redirect back to login page
        exit();
    }

    // Close connection
    $conn->close();
} else {
    // If not a POST request, redirect to login page
    header("location: ../frontend/login.html");
    exit();
}
?>
