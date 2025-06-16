<?php
include_once('../../backend-darasa/connect.php');
include_once('../../backend-darasa/auth/session_check.php');

// Initialize messages
$success_message = '';
$error_message = '';

// Handle form submission for creating a class
if ($_POST && isset($_POST['class_name'])) {
    $class_name = trim($_POST['class_name']);
    $class_description = trim($_POST['class_description']);
    
    if (!empty($class_name)) {
        // Validate session
        if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
            $error_message = "Session expired. Please login again.";
            header("Location: ../../frontend-darasa/auth/login.html");
            exit;
        } else {
            try {
                // Generate a unique class code
                do {
                    $class_code = strtoupper(substr(md5(uniqid()), 0, 6));
                    // Check if code exists
                    $checkCode = "SELECT id FROM classes WHERE class_code = ?";
                    $stmt = $pdo->prepare($checkCode);
                    $stmt->execute([$class_code]);
                    $codeExists = $stmt->rowCount() > 0;
                } while ($codeExists);

                // Get the teacher_id from the teachers table using the session user_id
                $getTeacherQuery = "SELECT id, full_name, email FROM teachers WHERE user_id = ?";
                $stmt = $pdo->prepare($getTeacherQuery);
                $stmt->execute([$_SESSION['user_id']]);
                $teacher_row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($teacher_row) {
                    $teacher_id = $teacher_row['id'];

                    // Clean and validate the description
                    $class_description = !empty($_POST['class_description']) ? trim($_POST['class_description']) : '';

                    // Insert the new class
                    $insertClassQuery = "INSERT INTO classes (name, description, class_code, teacher_id, created_at) VALUES (?, ?, ?, ?, NOW())";
                    $stmt = $pdo->prepare($insertClassQuery);
                    
                    if ($stmt->execute([$class_name, $class_description, $class_code, $teacher_id])) {
                        $success_message = "Class created successfully! Class code: " . $class_code;
                    } else {
                        $error_message = "Failed to create class.";
                    }
                } else {
                    // Log the error for debugging
                    error_log("Teacher record not found for user_id: " . $_SESSION['user_id']);
                    $error_message = "Error: Unable to find teacher record. Please ensure you're logged in with a teacher account.";
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $error_message = "Database error occurred. Please try again.";
            }
        }
    } else {
        $error_message = "Class name is required.";
    }
}

// Fetch classes from database for the current teacher
$classes = [];
if (isset($_SESSION['user_id'])) {
    try {
        $getClassesQuery = "
            SELECT c.id, c.name, c.description, c.class_code, c.created_at 
            FROM classes c 
            INNER JOIN teachers t ON c.teacher_id = t.id 
            WHERE t.user_id = ? 
            ORDER BY c.created_at DESC
        ";

        $stmt = $pdo->prepare($getClassesQuery);
        $stmt->execute([$_SESSION['user_id']]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error fetching classes: " . $e->getMessage());
        $error_message = "Error loading classes. Please refresh the page.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | Darasa</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../dashboard/dashboard.css">
    <link rel="stylesheet" href="../dashboard/teacher.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                <span>Darasa</span>
            </div>
            <nav class="nav-menu">
                <div class="nav-top">
                    <ul>
                        <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                        </li>
                        <li><a href="#"><i class="fas fa-chalkboard-teacher"></i><span>Classes</span></a></li>
                        <li><a href="#"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
                        <li><a href="#"><i class="fas fa-marker"></i><span>Grading</span></a></li>
                        <li><a href="#"><i class="fas fa-users"></i><span>Students</span></a></li>
                        <li><a href="#"><i class="fas fa-bullhorn"></i><span>Announce</span></a></li>
                    </ul>
                </div>
                <div class="nav-bottom">
                    <ul>
                        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                        <li><a href="../../backend-darasa/auth/logout.php"><i
                                    class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                    </ul>
                </div>
            </nav>
        </aside>

        <div class="main-content-wrapper">
            <header class="main-header">
                <div class="page-title">
                    Welcome,
                    <?php echo isset($_SESSION["fullname"]) ? htmlspecialchars($_SESSION["fullname"]) : 'Teacher'; ?>!
                </div>
                <div class="user-actions">
                    <button class="icon-button" aria-label="Notifications" title="Notifications"><i
                            class="fas fa-bell"></i></button>
                    <button class="icon-button" aria-label="User Profile" title="Profile"><i
                            class="fas fa-user-circle"></i></button>
                </div>
            </header>

            <main class="page-content">
                <!-- Messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <section class="content-section">
                    <div class="content-section-header">
                        <h2>My Classes</h2>
                        <div class="class-stats">
                            <span><?php echo count($classes); ?> classes</span>
                        </div>
                    </div>

                    <!-- Create Class Button -->
                    <button class="btn btn-primary" onclick="toggleCreateForm()">
                        <i class="fas fa-plus"></i> Create New Class
                    </button>

                    <!-- Create Class Form (initially hidden) -->
                    <div class="create-class-form" id="createClassForm" style="display: none;">
                        <h3>Create New Class</h3>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="class_name">Class Name *</label>
                                <input type="text" id="class_name" name="class_name" required maxlength="100"
                                    placeholder="e.g., Introduction to Web Development">
                            </div>

                            <div class="form-group">
                                <label for="class_description">Description (Optional)</label>
                                <textarea id="class_description" name="class_description" rows="3"
                                    placeholder="Brief description of the class"></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Class
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="toggleCreateForm()">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Classes Display -->
                    <div class="classes-container">
                        <?php if (empty($classes)): ?>
                            <p>You have not created any classes yet. Click "Create New Class" to get started.</p>
                        <?php else: ?>
                            <?php foreach ($classes as $class): ?>
                                <div class="class-card">
                                    <h3><?php echo htmlspecialchars($class['name']); ?></h3>
                                    <?php if (!empty($class['description'])): ?>
                                        <p><?php echo htmlspecialchars($class['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="class-code">
                                        Code: <strong><?php echo htmlspecialchars($class['class_code']); ?></strong>
                                        <button
                                            onclick="copyToClipboard('<?php echo htmlspecialchars($class['class_code']); ?>')"
                                            style="margin-left: 10px; background: none; border: none; cursor: pointer;"
                                            title="Copy class code">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <small>Created: <?php echo date('M j, Y', strtotime($class['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- <script>
        // Toggle create form visibility
        function toggleCreateForm() {
            const form = document.getElementById('createClassForm');
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                document.getElementById('class_name').focus();
            } else {
                form.style.display = 'none';
                // Reset form
                document.getElementById('class_name').value = '';
                document.getElementById('class_description').value = '';
            }
        }

        // Copy to clipboard function
        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    alert('Class code copied to clipboard: ' + text);
                }).catch(function () {
                    // Fallback
                    copyToClipboardFallback(text);
                });
            } else {
                copyToClipboardFallback(text);
            }
        }

        // Fallback copy function
        function copyToClipboardFallback(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Class code copied to clipboard: ' + text);
            } catch (err) {
                alert('Class code: ' + text);
            }
            document.body.removeChild(textArea);
        }
    </script> -->
</body>

</html>