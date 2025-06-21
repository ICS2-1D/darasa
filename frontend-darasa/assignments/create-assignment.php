<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment - Darasa</title>
    <link rel="stylesheet" href="../dashboard/teacher.css">
    <link rel="stylesheet" href="assignment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="view-assignments.php" class="back-link"><i class="fas fa-arrow-left"></i> Cancel</a>
                </div>
            </header>
            <main class="container">
                <div class="form-container">
                    <h1 class="form-title">Create a New Assignment</h1>
                    <p class="form-subtitle">Fill out the details below to assign work to your class.</p>

                    <!-- The form sends data to the PHP handler using POST -->
                    <!-- `enctype="multipart/form-data"` is crucial for file uploads -->
                    <form action="../../backend-darasa/handlers/assignment_handler.php" method="POST"
                        enctype="multipart/form-data" id="createAssignmentForm">
                        <input type="hidden" name="action" value="create">

                        <!-- Title -->
                        <div class="form-group">
                            <label for="title">Assignment Title</label>
                            <input type="text" id="title" name="title" placeholder="e.g., Chapter 5 Review Questions"
                                required>
                        </div>

                        <!-- Class Selection -->
                        <!-- Class Selection -->
                        <div class="form-group">
                            <label for="class_id">Select Class</label>
                            <select id="class_id" name="class_id" required>
                                <option value="" disabled selected>Choose a class...</option>
                                <?php
                                require_once __DIR__ . '/../../backend-darasa/connect.php';
                                session_start();

                                if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
                                    echo "<option disabled>Please log in as a teacher</option>";
                                } else {
                                    $user_id = $_SESSION['user_id'];

                                    // Get the teacher's internal ID
                                    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
                                    $stmt->execute([$user_id]);
                                    $teacher = $stmt->fetch();

                                    if ($teacher) {
                                        $teacher_id = $teacher['id'];
                                        $stmt = $pdo->prepare("SELECT id, name FROM classes WHERE teacher_id = ?");
                                        $stmt->execute([$teacher_id]);

                                        $classes = $stmt->fetchAll();
                                        if (empty($classes)) {
                                            echo "<option disabled>No classes found</option>";
                                        } else {
                                            foreach ($classes as $class) {
                                                echo "<option value='" . $class['id'] . "'>" . htmlspecialchars($class['name']) . "</option>";
                                            }
                                        }
                                    } else {
                                        echo "<option disabled>Teacher profile not found</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>


                        <!-- Description/Instructions -->
                        <div class="form-group">
                            <label for="description">Description / Instructions (Optional)</label>
                            <textarea id="description" name="description" rows="4"
                                placeholder="e.g., Answer all questions in full sentences."></textarea>
                        </div>

                        <!-- Due Date and Points -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="due_date">Due Date & Time</label>
                                <input type="datetime-local" id="due_date" name="due_date" required>
                            </div>
                            <div class="form-group">
                                <label for="max_points">Max Points</label>
                                <input type="number" id="max_points" name="max_points" value="100" min="0" required>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="form-group">
                            <label for="assignment_file">Upload File (Optional)</label>
                            <p class="form-hint">You can attach a PDF, Word document, or image.</p>
                            <input type="file" id="assignment_file" name="assignment_file">
                        </div>

                        <!-- Submission Button -->
                        <div class="form-actions">
                            <a href="view-assignments.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Assign
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        // Set minimum date for due date input to today
        document.addEventListener('DOMContentLoaded', function () {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('due_date').min = now.toISOString().slice(0, 16);
        });
    </script>
</body>

</html>