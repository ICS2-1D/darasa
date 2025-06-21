<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.html");
    exit;
}

try {
    // Get student info
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id, full_name FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        header("Location: ../../backend-darasa/auth/logout.php");
        exit;
    }

    // Get enrolled classes
    $stmt = $pdo->prepare("
        SELECT c.*, t.full_name as teacher_name, cs.enrolled_at
        FROM classes c 
        JOIN class_students cs ON c.id = cs.class_id 
        JOIN teachers t ON c.teacher_id = t.id
        WHERE cs.student_id = ? 
        ORDER BY cs.enrolled_at DESC
    ");
    $stmt->execute([$student['id']]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalClasses = count($classes);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("We are experiencing technical difficulties. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Darasa</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="student.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="logo">
                    <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                    <span>Darasa</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard/student.php" class="nav-link active"><i class="fas fa-home"></i> <span>My Classes</span></a>
                <a href="grades.php" class="nav-link"><i class="fas fa-chart-line"></i> <span>My Grades</span></a>
                <a href="../announcements/announcements.php" class="nav-link"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
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
                    <div class="header-user">
                        <span>Welcome, <strong><?= htmlspecialchars($student['full_name']) ?></strong></span>
                    </div>
                </div>
            </header>

            <main class="container">
                <div class="page-header">
                    <h1 class="page-title">Your Darasa</h1>
                    <button onclick="openModal('joinClassModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Join Class
                    </button>
                </div>

                <!-- Classes Grid -->
                <div class="class-grid">
                    <?php if ($totalClasses > 0): ?>
                        <?php foreach ($classes as $class): ?>
                            <div class="class-card">
                                <div class="class-card-header" style="background-image: url('../assets/images/<?= htmlspecialchars($class['background_image'] ?? 'hero.jpg') ?>');">
                                    <h3><?= htmlspecialchars($class['name']) ?></h3>
                                    <p>Taught by <?= htmlspecialchars($class['teacher_name']) ?></p>
                                </div>
                                <div class="class-card-content">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <!-- <i class="fas fa-barcode"></i>
                                        <span>Code: <strong><?= htmlspecialchars($class['class_code']) ?></strong></span> -->
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Joined: <?= date('M j, Y', strtotime($class['enrolled_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="class-card-footer">
                                    <a href="../assignments/view-assignment-student.php?class_id=<?= $class['id'] ?>" class="btn-icon" title="View Assignments">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <!-- === LINK UPDATED HERE === -->
                                    <a href="../materials/view-materials.php?class_id=<?= $class['id'] ?>" class="btn-icon" title="View Materials">
                                        <i class="fas fa-book"></i>
                                    </a>
                                    <form action="../../backend-darasa/handlers/student_class_handler.php" method="POST"
                                        onsubmit="return confirm('Are you sure you want to leave this class?')" style="display: inline;">
                                        <input type="hidden" name="action" value="leave">
                                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                        <button type="submit" class="btn-icon danger" title="Leave Class">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-school"></i></div>
                            <h3>You haven't joined any classes yet.</h3>
                            <p>Click the 'Join Class' button to enroll using a code from your teacher.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Join Class Modal -->
    <div id="joinClassModal" class="modal">
        <div class="modal-content">
            <button class="close-btn btn-icon" onclick="closeModal('joinClassModal')"><i class="fas fa-times"></i></button>
            <h3 class="modal-header">Join a New Class</h3>
            <form action="../../backend-darasa/handlers/student_class_handler.php" method="POST">
                <input type="hidden" name="action" value="join">
                <div class="form-group">
                    <label for="class_code">Class Code</label>
                    <input type="text" id="class_code" name="class_code" placeholder="Enter the 6-character code"
                        style="text-transform: uppercase;" maxlength="6" required>
                    <small><i class="fas fa-info-circle"></i> This code is provided by your teacher.</small>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal('joinClassModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Join</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        }
    </script>
</body>
</html>
