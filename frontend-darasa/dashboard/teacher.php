<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}

try {
    // Get teacher info
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id, full_name FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $teacher = $stmt->fetch();

    if (!$teacher) {
        die("Teacher profile not found.");
    }

    // Get classes with student count
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(cs.student_id) as student_count 
        FROM classes c 
        LEFT JOIN class_students cs ON c.id = cs.class_id 
        WHERE c.teacher_id = ? 
        GROUP BY c.id 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$teacher['id']]);
    $classes = $stmt->fetchAll();
    $totalClasses = count($classes);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Darasa</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="teacher.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>

<body>
    <div class="page-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                    <span>Darasa</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard/teacher.php" class="nav-link active"><i class="fas fa-home"></i>
                    <span>Home</span></a>
                <a href="../assignments/view-assignments.php" class="nav-link"><i class="fas fa-tasks"></i>
                    <span>Assignments</span></a>
                <a href="../materials/materials.php" class="nav-link"><i class="fas fa-book-open"></i>
                    <span>Materials</span></a>
                <a href="../announcements/announcements.php" class="nav-link"><i class="fas fa-bullhorn"></i>
                    <span>Announcements</span></a>
                <a href="../profile/profile.php" class="nav-link"><i class="fas fa-user"></i> <span>Profile</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="../../backend-darasa/auth/logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="main-content">
            <header class="header">
                <div class="header-content">

                    <div class="header-user">
                        <span style="font-weight: 500;">Welcome Back ,
                            <?= htmlspecialchars($teacher['full_name']) ?></span>
                    </div>
                </div>
            </header>

            <main class="container">
                <div class="page-header">
                    <h1 class="page-title">Darasa</h1>

                    <i onclick="openModal('createClassModal')" title="Create New Class" class="fas fa-plus"
                        style="background-color: #007bff; color: white; padding: 11px; border-radius: 20px;"></i>
                </div>

                <?php if ($totalClasses > 0): ?>
                    <div class="class-grid">
                        <?php foreach ($classes as $class): ?>
                            <div class="class-card">
                                <div class="class-card-header"
                                    style="background-image: url('../assets/images/<?= htmlspecialchars($class['background_image'] ?? 'hero.jpg') ?>');">
                                        <h3><?= htmlspecialchars($class['name']) ?></h3>
                                        <p class="class-description">
                                            Class Code: <span
                                                id="class_code_<?= $class['id'] ?>"><?= htmlspecialchars($class['class_code']) ?></span>
                                            <button type="button" class="btn-icon" title="Copy Class Code"
                                                onclick="copyCode('<?= htmlspecialchars($class['class_code']) ?>')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </p>
                                </div>
                                <div class="class-card-content">
                                    <p><strong>Students:</strong> <?= $class['student_count'] ?></p>
                                </div>
                                <div class="class-card-footer">
                                    <form action="../../backend-darasa/handlers/class_handler.php" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this class? This action is permanent.')"
                                        style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                        <button type="submit" class="btn-icon" title="Delete Class"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h3>Your classroom awaits</h3>
                        <p>Click the 'Create Class' button to get started.</p>
                    </div>
                <?php endif; ?>
        </div>
    </div>

    <div id="createClassModal" class="modal">
        <div class="modal-content">
            <button class="close-btn btn-icon" onclick="closeModal('createClassModal')"><i
                    class="fas fa-times"></i></button>
            <h3 class="modal-header">Create Class</h3>
            <form action="../../backend-darasa/handlers/class_handler.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Class Name</label>
                    <input type="text" name="name" placeholder="e.g., Form 4 Physics" required>
                </div>
                <div class="form-group">
                    <label>Description (optional)</label>
                    <input type="text" name="description" placeholder="A brief description">
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal('createClassModal')"
                        class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>


    <div id="joinClassModal" class="modal">
        <div class="modal-content">
            <button class="close-btn btn-icon" onclick="closeModal('joinClassModal')"><i
                    class="fas fa-times"></i></button>
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
                    <button type="button" onclick="closeModal('joinClassModal')"
                        class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Join</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; document.body.style.overflow = 'hidden'; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; document.body.style.overflow = 'auto'; }
        window.onclick = function (event) {
            if (event.target.classList && event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="flex"]');
                if (openModal) {
                    openModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
        });
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `notification notification-${type}`;
            toast.textContent = message;
            toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 12px 24px; border-radius: 4px;
        color: white; font-weight: 500; z-index: 2000; max-width: 300px; background: #2196f3; opacity: 0.95; font-size: 1rem;`;
            if (type === 'success') toast.style.background = '#4caf50';
            if (type === 'error') toast.style.background = '#f44336';
            if (type === 'warning') toast.style.background = '#ff9800';
            document.body.appendChild(toast);
            setTimeout(() => { toast.remove(); }, 2000);
        }
        function copyCode(code) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code).then(() => {
                    showToast('Class code copied!', 'success');
                }).catch(() => {
                    showToast('Could not copy code', 'error');
                });
            } else {
                showToast('Copy manually: ' + code, 'info');
            }
        }
    </script>
</body>

</html>