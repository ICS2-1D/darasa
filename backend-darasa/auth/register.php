<?php
session_start();

$showAlert = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once __DIR__ . '/../connect.php';

    // Validate and sanitize inputs
    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../frontend-darasa/auth/register.html?error=Invalid email format");
        exit();
    }

    // Validate password strength
    if (strlen($password) < 8) {
        header("Location: ../../frontend-darasa/auth/register.html?error=Password must be at least 8 characters");
        exit();
    }

    // Hash the password before storing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if email already exists
        $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists == 0) {
            // Start transaction to ensure both inserts succeed or both fail
            $pdo->beginTransaction();

            // Simple auto-role assignment
            $role = 'user'; // Default role
            if (preg_match('/^[a-zA-Z]+\.[a-zA-Z]+@strathmore\.edu$/', $email)) {
                $role = 'student';
            } elseif (preg_match('/^[a-zA-Z]+@strathmore\.edu$/', $email)) {
                $role = 'teacher';
            }

            // Insert user into users table with hashed password
            $insertUserQuery = "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($insertUserQuery);
            $stmt->execute([$fullname, $email, $hashedPassword, $role]);

            // Get the newly created user's ID
            $user_id = $pdo->lastInsertId();

            // Insert into respective role table
            if ($role == 'student') {
                $insertRoleQuery = "INSERT INTO students (user_id, full_name, email) VALUES (?, ?, ?)";
                $stmt2 = $pdo->prepare($insertRoleQuery);
                $stmt2->execute([$user_id, $fullname, $email]);
            } elseif ($role == 'teacher') {
                $insertRoleQuery = "INSERT INTO teachers (user_id, full_name, email) VALUES (?, ?, ?)";
                $stmt2 = $pdo->prepare($insertRoleQuery);
                $stmt2->execute([$user_id, $fullname, $email]);
            }

            // If we get here, both inserts succeeded
            $pdo->commit();

            $showAlert = true;
            header("Location: ../../frontend-darasa/auth/login.html?success=Registered successfully!");
            exit();

        } else {
            // Email already exists
            header("Location: ../../frontend-darasa/auth/register.html?error=Email already registered.");
            exit();
        }

    } catch (PDOException $e) {
        // If anything failed, roll back the transaction
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        echo "Error creating account: " . $e->getMessage();
    } catch (Exception $e) {
        // Handle other exceptions
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        echo "Error creating account: " . $e->getMessage();
    }
}
?>