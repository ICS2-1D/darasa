<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// 1. Authenticate: Ensure user is a logged-in student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.html");
    exit;
}

// 2. Validate Input: Get the class ID from the URL
$class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
if (!$class_id) {
    die("Invalid class ID provided.");
}

$materials = [];
$class_name = 'Class';
$student_name = 'Student';

try {
    // Get student name for the header
    $stmt_student = $pdo->prepare("SELECT full_name FROM students WHERE user_id = ?");
    $stmt_student->execute([$_SESSION['user_id']]);
    $student = $stmt_student->fetch(PDO::FETCH_ASSOC);
    if ($student) {
        $student_name = $student['full_name'];
    }

    // 3. Fetch Class Info & Materials
    // First, get the class name for the page title
    $stmt_class = $pdo->prepare("SELECT name FROM classes WHERE id = ?");
    $stmt_class->execute([$class_id]);
    $class = $stmt_class->fetch(PDO::FETCH_ASSOC);
    if ($class) {
        $class_name = $class['name'];
    } else {
        die("Class not found.");
    }

    // Now, fetch all materials for that specific class
    $stmt_materials = $pdo->prepare("
        SELECT id, title, file_path, uploaded_at
        FROM class_materials
        WHERE class_id = ?
        ORDER BY uploaded_at DESC
    ");
    $stmt_materials->execute([$class_id]);
    $materials = $stmt_materials->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Helper function to get a Font Awesome icon based on file extension
function getFileIcon($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'pdf': return 'fas fa-file-pdf';
        case 'docx': case 'doc': return 'fas fa-file-word';
        case 'pptx': case 'ppt': return 'fas fa-file-powerpoint';
        case 'jpg': case 'jpeg': case 'png': case 'gif': return 'fas fa-file-image';
        default: return 'fas fa-file-alt';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials for <?= htmlspecialchars($class_name) ?> - Darasa</title>
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
    <link rel="stylesheet" href="../dashboard/student.css">
    <link rel="stylesheet" href="materials.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Student Sidebar -->
        <aside class="sidebar" id="sidebar">
             <div class="sidebar-header">
                <a href="../dashboard/student.php" class="logo">
                    <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                    <span>Darasa</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard/student.php" class="nav-link active"><i class="fas fa-home"></i> <span>My Classes</span></a>
                <a href="../dashboard/grades.php" class="nav-link"><i class="fas fa-chart-line"></i> <span>My Grades</span></a>
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
                    <h1 class="page-title">Materials for <?= htmlspecialchars($class_name) ?></h1>
                     <a href="../dashboard/student.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to My Classes
                    </a>
                </div>

                <div class="materials-container">
                    <?php if (empty($materials)): ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                            <h3>No Materials Here Yet</h3>
                            <p>Your teacher has not uploaded any materials for this class.</p>
                        </div>
                    <?php else: ?>
                        <div class="materials-list">
                            <?php foreach ($materials as $material): ?>
                                <div class="material-card">
                                    <div class="material-icon">
                                        <i class="<?= getFileIcon($material['file_path']) ?>"></i>
                                    </div>
                                    <div class="material-details">
                                        <h3 class="material-title"><?= htmlspecialchars($material['title']) ?></h3>
                                        <p class="material-meta">
                                            Uploaded: <?= (new DateTime($material['uploaded_at']))->format('F j, Y') ?>
                                        </p>
                                    </div>
                                    <div class="material-actions">
                                        <!-- Students only get a download button -->
                                        <a href="../../<?= htmlspecialchars($material['file_path']) ?>" class="btn btn-primary btn-sm" download>
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
