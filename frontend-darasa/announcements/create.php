<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Authenticate: ensure user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}

$classes = [];
$teacher_name = 'Teacher';
try {
    // Get teacher's info
    $stmt = $pdo->prepare("SELECT id, full_name FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher) {
        $teacher_name = $teacher['full_name'];
        // Fetch the classes taught by this teacher
        $stmt = $pdo->prepare("SELECT id, name FROM classes WHERE teacher_id = ? ORDER BY name");
        $stmt->execute([$teacher['id']]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        die("Teacher profile not found.");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement - Darasa</title>
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
    <link rel="stylesheet" href="../dashboard/teacher.css">
    <link rel="stylesheet" href="announcements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                    <span>Darasa</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard/teacher.php" class="nav-link"><i class="fas fa-home"></i> <span>Home</span></a>
                <a href="../assignment/view-assignments.php" class="nav-link"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
                <a href="../materials/materials.php" class="nav-link"><i class="fas fa-book-open"></i> <span>Materials</span></a>
                <a href="announcements.php" class="nav-link active"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                <a href="../profile/profile.php" class="nav-link"><i class="fas fa-user"></i> <span>Profile</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="../../backend-darasa/auth/logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
             <header class="header">
                <div class="header-content">
                     <span style="font-weight: 500;">Welcome Back, <?= htmlspecialchars($teacher_name) ?></span>
                </div>
            </header>
            <main class="container">
                 <div class="page-header">
                    <h1 class="page-title">Create New Announcement</h1>
                    <a href="announcements.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                </div>

                <div class="form-container">
                    <p class="form-subtitle">Post an update to one of your classes.</p>
                    
                    <form action="../../backend-darasa/handlers/announcement_handler.php" method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label for="class_id">Post to Class</label>
                            <select id="class_id" name="class_id" required>
                                <option value="" disabled selected>Select a class...</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" placeholder="e.g., Upcoming Test on Friday" required>
                        </div>

                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" rows="8" placeholder="Type your announcement here..." required></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Post Announcement</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
