<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Authenticate: ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.html");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$announcements = [];
$page_title = "Announcements";
$user_name = '';

try {
    if ($role === 'teacher') {
        // Fetch teacher's info
        $stmt = $pdo->prepare("SELECT id, full_name FROM teachers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_name = $teacher['full_name'];
        
        // Fetch announcements created by this teacher
        $stmt = $pdo->prepare("
            SELECT a.*, c.name AS class_name 
            FROM announcements a
            JOIN classes c ON a.class_id = c.id
            WHERE a.teacher_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$teacher['id']]);
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($role === 'student') {
        // Fetch student's info
        $stmt = $pdo->prepare("SELECT id, full_name FROM students WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_name = $student['full_name'];

        // Fetch announcements for the classes the student is enrolled in
        $stmt = $pdo->prepare("
            SELECT a.*, c.name AS class_name, t.full_name AS teacher_name
            FROM announcements a
            JOIN classes c ON a.class_id = c.id
            JOIN teachers t ON a.teacher_id = t.id
            WHERE a.class_id IN (SELECT class_id FROM class_students WHERE student_id = ?)
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$student['id']]);
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Announcements - Darasa</title>
     <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
    <link rel="stylesheet" href="../dashboard/<?= $role === 'teacher' ? 'teacher.css' : 'student.css' ?>">
    <link rel="stylesheet" href="announcements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header"><div class="logo"><span>Darasa</span></div></div>
            <nav class="sidebar-nav">
                <?php if ($role === 'teacher'): ?>
                    <a href="../dashboard/teacher.php" class="nav-link"><i class="fas fa-home"></i> <span>Home</span></a>
                    <a href="../assignment/view-assignments.php" class="nav-link"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
                    <a href="../materials/materials.php" class="nav-link"><i class="fas fa-book-open"></i> <span>Materials</span></a>
                    <a href="announcements.php" class="nav-link active"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                    <a href="../profile/profile.php" class="nav-link"><i class="fas fa-user"></i> <span>Profile</span></a>
                <?php else: ?>
                    <a href="../dashboard/student.php" class="nav-link"><i class="fas fa-home"></i> <span>My Classes</span></a>
                    <a href="../dashboard/grades.php" class="nav-link"><i class="fas fa-chart-line"></i> <span>My Grades</span></a>
                    <a href="announcements.php" class="nav-link active"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                    <a href="../profile/profile.php" class="nav-link"><i class="fas fa-user"></i> <span>Profile</span></a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer"><a href="../../backend-darasa/auth/logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="header"><div class="header-content"><span>Welcome, <strong><?= htmlspecialchars($user_name) ?></strong></span></div></header>
            <main class="container">
                <div class="page-header">
                    <h1 class="page-title"><?= $page_title ?></h1>
                    <?php if ($role === 'teacher'): ?>
                        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Announcement</a>
                    <?php endif; ?>
                </div>

                <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                    <div class="alert alert-success">Announcement posted successfully!</div>
                <?php endif; ?>

                <div class="announcements-list">
                    <?php if (empty($announcements)): ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
                            <h3>No Announcements Yet</h3>
                            <p><?= $role === 'teacher' ? 'Click "Create Announcement" to post an update.' : 'Check back later for updates from your teachers.' ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): ?>
                            <div class="announcement-card">
                                <div class="card-header">
                                    <h3 class="card-title"><?= htmlspecialchars($ann['title']) ?></h3>
                                    <span class="card-class-name"><?= htmlspecialchars($ann['class_name']) ?></span>
                                </div>
                                <div class="card-content">
                                    <p><?= nl2br(htmlspecialchars($ann['content'])) ?></p>
                                </div>
                                <div class="card-footer">
                                    <span class="card-author">
                                        <?php if ($role === 'student'): ?>
                                            Posted by <strong><?= htmlspecialchars($ann['teacher_name']) ?></strong>
                                        <?php endif; ?>
                                    </span>
                                    <span class="card-timestamp">
                                        <i class="far fa-clock"></i> <?= date('M j, Y, g:i a', strtotime($ann['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
