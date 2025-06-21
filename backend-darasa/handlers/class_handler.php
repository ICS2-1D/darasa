<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../frontend-darasa/auth/login.html");
    exit;
}

try {
    // Get teacher ID
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=Teacher profile not found");
        exit;
    }

    $teacher_id = $teacher['id'];
    $action = $_POST['action'] ?? '';

    // Available background images
    function getRandomBackgroundImage() {
        $backgroundImages = [
            'hero.jpg',
            'pen1.jpg', 
            'pen2.jpg',
            'book1.jpg',
            'book2.jpg', 
            'book3.jpg',
            'computer1.jpg'
        ];
        return $backgroundImages[array_rand($backgroundImages)];
    }

    // Generate unique class code
    function generateClassCode($pdo)
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
            $stmt = $pdo->prepare("SELECT id FROM classes WHERE class_code = ?");
            $stmt->execute([$code]);
            $exists = $stmt->fetchColumn() > 0;
        } while ($exists);
        return $code;
    }

    // Handle actions
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=Class name is required");
                exit;
            }

            // Check for duplicate name
            $stmt = $pdo->prepare("SELECT id FROM classes WHERE name = ? AND teacher_id = ?");
            $stmt->execute([$name, $teacher_id]);

            if ($stmt->fetchColumn() > 0) {
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=A class with this name already exists");
                exit;
            }

            // Create class with random background image
            $class_code = generateClassCode($pdo);
            $background_image = getRandomBackgroundImage();
            $stmt = $pdo->prepare("INSERT INTO classes (name, description, class_code, teacher_id, background_image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

            if ($stmt->execute([$name, $description, $class_code, $teacher_id, $background_image])) {
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=success&message=Class created successfully");
            } else {
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=Failed to create class");
            }
            break;

        case 'delete':
            $class_id = (int) ($_POST['class_id'] ?? 0);

            if ($class_id <= 0) {
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=Invalid class ID");
                exit;
            }

            // Verify class belongs to teacher
            $stmt = $pdo->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
            $stmt->execute([$class_id, $teacher_id]);

            if (!$stmt->fetch()) {
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=Class not found or access denied");
                exit;
            }

            // Delete class and related data using transaction
            try {
                $pdo->beginTransaction();

                // Delete student enrollments
                $stmt = $pdo->prepare("DELETE FROM class_students WHERE class_id = ?");
                $stmt->execute([$class_id]);

                // Delete the class
                $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
                $stmt->execute([$class_id]);

                $pdo->commit();
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=success&message=Class deleted successfully");
            } catch (PDOException $e) {
                $pdo->rollBack();
                header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=Failed to delete class");
            }
            break;

        default:
            header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=Invalid action");
            break;
    }
} catch (PDOException $e) {
    error_log("Database error in class_handler.php: " . $e->getMessage());
    header("Location: ../../frontend-darasa/dashboard/teacher.php?status=error&message=A database error occurred");
    exit;
}
?>