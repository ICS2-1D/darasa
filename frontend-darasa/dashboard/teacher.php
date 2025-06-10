<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}

// Get teacher info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, full_name FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    die("Teacher profile not found.");
}

// Get classes
$stmt = $conn->prepare("
    SELECT c.*, COUNT(cs.student_id) as student_count 
    FROM classes c 
    LEFT JOIN class_students cs ON c.id = cs.class_id 
    WHERE c.teacher_id = ? 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $teacher['id']);
$stmt->execute();
$classes = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Darasa Classroom</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="teacher.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                <span>Darasa Classroom</span>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <span style="color: #5f6368;">Hello, <?= htmlspecialchars($teacher['full_name']) ?>!</span>
                <a href="../../backend-darasa/auth/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div style="background: linear-gradient(135deg, #1a73e8 0%, #4285f4 100%); 
                    color: white; padding: 32px; border-radius: 8px; margin-bottom: 24px; text-align: center;">
            <h1 style="font-size: 28px; font-weight: 400; margin-bottom: 8px;">
                Welcome back, <?= htmlspecialchars($teacher['full_name']) ?>!
            </h1>
            <p style="font-size: 16px; opacity: 0.9;">Manage your classes and connect with your students</p>
        </div>

        <!-- Status Messages -->
        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'error' ?>">
                <i class="fas fa-<?= $_GET['status'] === 'success' ? 'check' : 'exclamation' ?>-circle"></i>
                <?= $_GET['status'] === 'success' ? 'Operation completed successfully!' : 'An error occurred. Please try again.' ?>
            </div>
        <?php endif; ?>

        <!-- Create Class Button -->
        <button class="btn btn-primary" onclick="toggleForm()" style="margin-bottom: 24px;">
            <i class="fas fa-plus"></i> Create New Class
        </button>

        <!-- Create Class Form -->
        <div id="createForm"
            style="display: none; background: white; padding: 24px; border-radius: 8px; 
                                   box-shadow: 0 1px 3px rgba(0,0,0,0.12); margin-bottom: 24px; border: 1px solid #e8eaed;">
            <h3 style="margin-bottom: 20px; color: #1a73e8; font-size: 18px; font-weight: 500;">
                <i class="fas fa-chalkboard-teacher"></i> Create New Class
            </h3>
            <form action="../../backend-darasa/handlers/class_handler.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Class Name</label>
                    <input type="text" name="name" placeholder="e.g., Form 4 Physics" required>
                </div>
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="description" rows="3" placeholder="Brief description of the class"></textarea>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" onclick="toggleForm()" style="background: #f8f9fa; color: #3c4043; border: 1px solid #dadce0; 
                                   padding: 10px 20px; border-radius: 20px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Class
                    </button>
                </div>
            </form>
        </div>

        <!-- Classes Section -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 24px; font-weight: 400; color: #3c4043;">Your Classes</h2>
            <div style="background: white; padding: 12px 16px; border-radius: 8px; border: 1px solid #e8eaed; 
                        display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <i class="fas fa-chalkboard-teacher" style="color: #1a73e8;"></i>
                <div>
                    <strong><?= $classes->num_rows ?></strong>
                    <span style="color: #5f6368; font-size: 14px;"> Total Classes</span>
                </div>
            </div>
        </div>

        <!-- Classes Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <?php if ($classes->num_rows > 0): ?>
                <?php while ($class = $classes->fetch_assoc()): ?>
                    <div class="class-card">
                        <!-- Class Header -->
                        <div class="class-header">
                            <h3><?= htmlspecialchars($class['name']) ?></h3>
                            <?php if (!empty($class['description'])): ?>
                                <p><?= htmlspecialchars($class['description']) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Class Body -->
                        <div class="class-body">
                            <!-- Class Code -->
                            <div class="class-code">
                                <div>
                                    <small style="color: #5f6368;">Class Code</small><br>
                                    <span class="class-code-text"><?= $class['class_code'] ?></span>
                                </div>
                                <button class="copy-btn" onclick="copyCode('<?= $class['class_code'] ?>')"
                                    title="Copy class code">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>

                            <!-- Class Meta -->
                            <div style="display: flex; justify-content: space-between; align-items: center; 
                                        margin-bottom: 12px; font-size: 14px; color: #5f6368;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-users"></i>
                                    <span><?= $class['student_count'] ?> students</span>
                                </div>
                                <small>Created: <?= date('M j, Y', strtotime($class['created_at'])) ?></small>
                            </div>

                            <!-- Class Actions -->
                            <div class="class-actions">
                                <div class="class-tools">
                                    <a href="#" class="tool-btn" onclick="alert('Assignment feature coming soon!')">
                                        <i class="fas fa-tasks"></i> Assignments
                                    </a>
                                    <a href="#" class="tool-btn" onclick="alert('Materials feature coming soon!')">
                                        <i class="fas fa-folder"></i> Materials
                                    </a>
                                </div>
                                <form action="../../backend-darasa/handlers/class_handler.php" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this class?')"
                                    style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div style="text-align: center; padding: 48px 24px; background: white; border-radius: 8px; 
                           border: 1px solid #e8eaed; grid-column: 1 / -1;">
                    <i class="fas fa-chalkboard-teacher" style="font-size: 48px; color: #dadce0; margin-bottom: 16px;"></i>
                    <h3 style="font-size: 20px; font-weight: 400; margin-bottom: 8px; color: #5f6368;">No classes yet</h3>
                    <p style="color: #80868b; margin-bottom: 24px;">Create your first class to get started with teaching!
                    </p>
                    <button class="btn btn-primary" onclick="toggleForm()">
                        <i class="fas fa-plus"></i> Create Your First Class
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="script.js"></script>
</body>

</html>