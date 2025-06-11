<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.html");
    exit;
}

// Get student info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, full_name FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student profile not found.");
}

// Get enrolled classes
$stmt = $conn->prepare("
    SELECT c.*, t.full_name as teacher_name, cs.enrolled_at
    FROM classes c 
    JOIN class_students cs ON c.id = cs.class_id 
    JOIN teachers t ON c.teacher_id = t.id
    WHERE cs.student_id = ? 
    ORDER BY cs.enrolled_at DESC
");
$stmt->bind_param("i", $student['id']);
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
    <title>Darasa Classroom - Student</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="student.css">
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
                <span style="color: #5f6368;">Hello, <?= htmlspecialchars($student['full_name']) ?>!</span>
                <a href="../../backend-darasa/auth/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div style="background: linear-gradient(135deg, #34a853 0%, #4caf50 100%); 
                    color: white; padding: 32px; border-radius: 8px; margin-bottom: 24px; text-align: center;">
            <h1 style="font-size: 28px; font-weight: 400; margin-bottom: 8px;">
                Welcome back, <?= htmlspecialchars($student['full_name']) ?>!
            </h1>
            <p style="font-size: 16px; opacity: 0.9;">Join classes and access your assignments</p>
        </div>

        <!-- Status Messages -->
        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'error' ?>">
                <i class="fas fa-<?= $_GET['status'] === 'success' ? 'check' : 'exclamation' ?>-circle"></i>
                <?php if ($_GET['status'] === 'success'): ?>
                    <?= isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Operation completed successfully!' ?>
                <?php else: ?>
                    <?= isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An error occurred. Please try again.' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Join Class Button -->
        <button class="btn btn-primary" onclick="toggleJoinForm()" style="margin-bottom: 24px;">
            <i class="fas fa-plus"></i> Join Class
        </button>

        <!-- Join Class Form -->
        <div id="joinForm"
            style="display: none; background: white; padding: 24px; border-radius: 8px; 
                   box-shadow: 0 1px 3px rgba(0,0,0,0.12); margin-bottom: 24px; border: 1px solid #e8eaed;">
            <h3 style="margin-bottom: 20px; color: #34a853; font-size: 18px; font-weight: 500;">
                <i class="fas fa-graduation-cap"></i> Join a Class
            </h3>
            <form action="../../backend-darasa/handlers/student_class_handler.php" method="POST">
                <input type="hidden" name="action" value="join">
                <div class="form-group">
                    <label>Class Code</label>
                    <input type="text" name="class_code" placeholder="Enter the 6-character class code" 
                           style="text-transform: uppercase;" maxlength="6" required>
                    <small style="color: #5f6368; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Ask your teacher for the class code
                    </small>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" onclick="toggleJoinForm()" 
                            style="background: #f8f9fa; color: #3c4043; border: 1px solid #dadce0; 
                                   padding: 10px 20px; border-radius: 20px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Join Class
                    </button>
                </div>
            </form>
        </div>

        <!-- Classes Section -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 24px; font-weight: 400; color: #3c4043;">Your Classes</h2>
            <div style="background: white; padding: 12px 16px; border-radius: 8px; border: 1px solid #e8eaed; 
                        display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <i class="fas fa-graduation-cap" style="color: #34a853;"></i>
                <div>
                    <strong><?= $classes->num_rows ?></strong>
                    <span style="color: #5f6368; font-size: 14px;"> Enrolled Classes</span>
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
                            <div class="teacher-info">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span><?= htmlspecialchars($class['teacher_name']) ?></span>
                            </div>
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
                                    <i class="fas fa-calendar"></i>
                                    <span>Joined: <?= date('M j, Y', strtotime($class['enrolled_at'])) ?></span>
                                </div>
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
                                    <a href="#" class="tool-btn" onclick="alert('Grades feature coming soon!')">
                                        <i class="fas fa-chart-line"></i> Grades
                                    </a>
                                </div>
                                <form action="../../backend-darasa/handlers/student_class_handler.php" method="POST"
                                    onsubmit="return confirm('Are you sure you want to leave this class?')"
                                    style="display: inline;">
                                    <input type="hidden" name="action" value="leave">
                                    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                    <button type="submit" class="btn btn-danger" title="Leave class">
                                        <i class="fas fa-sign-out-alt"></i>
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
                    <i class="fas fa-graduation-cap" style="font-size: 48px; color: #dadce0; margin-bottom: 16px;"></i>
                    <h3 style="font-size: 20px; font-weight: 400; margin-bottom: 8px; color: #5f6368;">No classes yet</h3>
                    <p style="color: #80868b; margin-bottom: 24px;">Join your first class to get started with learning!
                    </p>
                    <button class="btn btn-primary" onclick="toggleJoinForm()">
                        <i class="fas fa-plus"></i> Join Your First Class
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleJoinForm() {
            const form = document.getElementById('joinForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                // Show temporary success message
                const btn = event.target.closest('.copy-btn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.style.color = '#34a853';
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.color = '';
                }, 2000);
            }).catch(() => {
                alert('Failed to copy code');
            });
        }

        // Auto-uppercase class code input
        document.addEventListener('DOMContentLoaded', function() {
            const classCodeInput = document.querySelector('input[name="class_code"]');
            if (classCodeInput) {
                classCodeInput.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }
        });
    </script>
</body>

</html>