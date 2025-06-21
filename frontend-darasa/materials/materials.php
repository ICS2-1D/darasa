<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Authenticate teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}

$materials = [];
$teacher_name = 'Teacher';
try {
    // Get teacher info
    $stmt = $pdo->prepare("SELECT id, full_name FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher) {
        $teacher_name = $teacher['full_name'];
        // Fetch materials
        $stmt = $pdo->prepare("
            SELECT cm.id, cm.title, cm.file_path, cm.uploaded_at, c.name AS class_name
            FROM class_materials cm
            JOIN classes c ON cm.class_id = c.id
            WHERE c.teacher_id = ?
            ORDER BY cm.uploaded_at DESC
        ");
        $stmt->execute([$teacher['id']]);
        $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

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
    <title>Class Materials - Darasa</title>
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
    <link rel="stylesheet" href="../dashboard/teacher.css">
    <link rel="stylesheet" href="materials.css">
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
                <a href="materials.php" class="nav-link active"><i class="fas fa-book-open"></i> <span>Materials</span></a>
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
                         <span style="font-weight: 500;">Welcome Back, <?= htmlspecialchars($teacher_name) ?></span>
                    </div>
                </div>
            </header>
            <main class="container">
                <div class="page-header">
                    <h1 class="page-title">Class Materials</h1>
                    <a href="upload-material.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload New Material
                    </a>
                </div>
                
                <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                    <div class="alert alert-success">Material uploaded successfully!</div>
                <?php endif; ?>
                 <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
                    <div class="alert alert-success">Material deleted successfully.</div>
                <?php endif; ?>

                <div class="materials-container">
                    <?php if (empty($materials)): ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                            <h3>No Materials Uploaded Yet</h3>
                            <p>Click "Upload New Material" to share files with your classes.</p>
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
                                            For: <strong><?= htmlspecialchars($material['class_name']) ?></strong> 
                                            &bull; 
                                            Uploaded: <?= (new DateTime($material['uploaded_at']))->format('M j, Y') ?>
                                        </p>
                                    </div>
                                    <div class="material-actions">
                                        <a href="../../<?= htmlspecialchars($material['file_path']) ?>" class="btn btn-secondary btn-sm" download>
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <form action="../../backend-darasa/handlers/material_handler.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this material?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="material_id" value="<?= $material['id'] ?>">
                                            <button type="submit" class="btn-icon danger" title="Delete Material">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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
