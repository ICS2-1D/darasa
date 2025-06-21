<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// 1. Authentication & Authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.html");
    exit;
}

$student_id = null;
$student_name = 'Student';

try {
    // 2. Get Student Information from session user_id
    $stmt = $pdo->prepare("SELECT id, full_name FROM students WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $student_id = $student['id'];
        $student_name = $student['full_name'];
    } else {
        header("Location: ../../backend-darasa/auth/logout.php");
        exit;
    }

    // 3. Fetch all graded and pending submissions for this student
    $stmt = $pdo->prepare("
        SELECT 
            s.grade,
            s.feedback,
            a.title AS assignment_title,
            a.max_points,
            c.name AS class_name
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN classes c ON a.class_id = c.id
        WHERE s.student_id = ?
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([$student_id]);
    $graded_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Grades page error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - Darasa</title>
    <!-- Base layout styles -->
    <link rel="stylesheet" href="../dashboard/student.css">
    <!-- Page-specific styles for grade cards -->
    <link rel="stylesheet" href="grades.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="student.php" class="logo">
                    <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                    <span>Darasa</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard/student.php" class="nav-link"><i class="fas fa-home"></i> <span>My Classes</span></a>
                <a href="grades.php" class="nav-link active"><i class="fas fa-chart-line"></i> <span>My Grades</span></a>
                <a href="#" class="nav-link"><i class="fas fa-user"></i> <span>Profile</span></a>
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
                    <div class="header-user">
                        <span>Welcome, <strong><?= htmlspecialchars($student_name) ?></strong></span>
                    </div>
                </div>
            </header>

            <main class="container">
                <div class="page-header">
                    <h1 class="page-title">My Grades</h1>
                </div>

                <div class="grades-grid">
                    <?php if (empty($graded_assignments)): ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <h3>Nothing to see here yet</h3>
                            <p>Once you submit assignments and your teacher grades them, your grades will appear here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($graded_assignments as $item): ?>
                            <div class="grade-card">
                                <div class="card-main-content">
                                    <h3 class="assignment-title"><?= htmlspecialchars($item['assignment_title']) ?></h3>
                                    <p class="class-name"><?= htmlspecialchars($item['class_name']) ?></p>
                                </div>
                                <div class="card-grade-section">
                                    <?php if (!is_null($item['grade'])): ?>
                                        <div class="grade-display">
                                            <span class="score"><?= htmlspecialchars($item['grade']) ?></span>
                                            <span class="total">/ <?= htmlspecialchars($item['max_points']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="status-badge pending">
                                            <i class="fas fa-clock"></i> Pending
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($item['feedback']) || is_null($item['grade'])): ?>
                                    <div class="card-feedback-section">
                                        <h4>Teacher Feedback:</h4>
                                        <p>
                                            <?= !empty($item['feedback']) ? nl2br(htmlspecialchars($item['feedback'])) : '<em>No feedback yet.</em>' ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
