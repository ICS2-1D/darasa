<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Authenticate: ensure user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}

$teacher_id = null;
$classes = [];
$teacher_name = 'Teacher';

try {
    // Get teacher info
    $stmt = $pdo->prepare("SELECT id, full_name FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher) {
        $teacher_id = $teacher['id'];
        $teacher_name = $teacher['full_name'];
        // Fetch the classes taught by this teacher
        $stmt = $pdo->prepare("SELECT id, name FROM classes WHERE teacher_id = ? ORDER BY name");
        $stmt->execute([$teacher_id]);
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
    <title>Upload Material - Darasa</title>
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
                <a href="../assignment/view-assignments.php" class="nav-link"><i class="fas fa-tasks"></i>
                    <span>Assignments</span></a>
                <a href="materials.php" class="nav-link active"><i class="fas fa-book-open"></i>
                    <span>Materials</span></a>
                <a href="../profile/profile.php" class="nav-link active"><i class="fas fa-user"></i>
                    <span>Profile</span></a>
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
                    <h1 class="page-title">Upload New Material</h1>
                    <a href="materials.php" class="btn btn-secondary">Cancel</a>
                </div>

                <div class="upload-container">
                    <p class="form-subtitle">Share notes, documents, and other files with your students.</p>

                    <!-- Status Message Display -->
                    <?php if (isset($_GET['status'])): ?>
                        <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'error' ?>">
                            <?= htmlspecialchars($_GET['message']) ?>
                        </div>
                    <?php endif; ?>

                    <form action="../../backend-darasa/handlers/material_handler.php" method="POST"
                        enctype="multipart/form-data" id="uploadMaterialForm">
                        <input type="hidden" name="action" value="upload">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Material Title</label>
                                <input type="text" id="title" name="title"
                                    placeholder="e.g., Chapter 3 Photosynthesis Notes" required>
                            </div>
                            <div class="form-group">
                                <label for="class_id">Select Class</label>
                                <select id="class_id" name="class_id" required>
                                    <option value="" disabled selected>Choose a class...</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Material File</label>
                            <div class="drop-zone" id="dropZone">
                                <div class="drop-zone-prompt">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p><b>Drag & Drop</b> your file here or <b>click to select</b></p>
                                    <small>Max file size: 10MB. Allowed types: PDF, DOCX, PPTX, JPG, PNG</small>
                                </div>
                                <input type="file" name="material_file" id="materialFile" class="drop-zone-input"
                                    required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Material
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('materialFile');
            const prompt = dropZone ? dropZone.querySelector('.drop-zone-prompt') : null;
            if (!dropZone || !fileInput || !prompt) return;

            dropZone.addEventListener('click', function () { fileInput.click(); });
            fileInput.addEventListener('change', function () {
                if (fileInput.files.length > 0) showFile(fileInput.files[0]);
            });
            dropZone.addEventListener('dragover', function (e) {
                e.preventDefault(); dropZone.classList.add('drag-over');
            });
            ['dragleave', 'dragend'].forEach(type => dropZone.addEventListener(type, function () {
                dropZone.classList.remove('drag-over');
            }));
            dropZone.addEventListener('drop', function (e) {
                e.preventDefault(); dropZone.classList.remove('drag-over');
                if (e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    showFile(e.dataTransfer.files[0]);
                }
            });
            function showFile(file) {
                prompt.classList.add('file-selected');
                prompt.innerHTML = `<i class='fas fa-check-circle'></i><p>${file.name}</p><small>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB. Click to change.</small>`;
            }
        });
    </script>
</body>

</html>