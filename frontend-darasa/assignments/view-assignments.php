<?php
session_start();
// Include your database connection file
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Authentication check: Ensure user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}

$teacher_id = null;
try {
    // Get the teacher's ID from the users table session
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        $teacher_id = $teacher['id'];
    } else {
        // Handle case where teacher profile doesn't exist
        die("Teacher profile not found.");
    }

    // Fetch all assignments created by this teacher
    // We join with the classes table to get the class name
    // A subquery is used to count submissions for each assignment
    $stmt = $pdo->prepare("
        SELECT 
            a.id, 
            a.title, 
            a.description, 
            a.due_date, 
            a.file_path,
            c.name AS class_name,
            (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) AS submission_count,
            (SELECT COUNT(*) FROM class_students cs WHERE cs.class_id = a.class_id) AS total_students
        FROM assignments a
        JOIN classes c ON a.class_id = c.id
        WHERE c.teacher_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$teacher_id]);
    $assignments = $stmt->fetchAll();

} catch (PDOException $e) {
    // Handle database errors gracefully
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignments - Darasa</title>
    <!-- Link to the main teacher stylesheet and a new one for assignments -->
    <link rel="stylesheet" href="../dashboard/teacher.css">
    <link rel="stylesheet" href="assignment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>

<body>
    <div class="page-wrapper">
        <!-- You can include your sidebar here if needed, or link back to the main dashboard -->
        <!-- For simplicity, I'm focusing on the main content -->

        <div class="main-content full-width">
            <header class="header">
                <div class="header-content">
                    <a href="../dashboard/teacher.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to
                        Dashboard</a>
                </div>
            </header>

            <main class="container">
                <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
                    <div class="alert success">Assignment deleted successfully.</div>
                <?php endif; ?>

                <div class="page-header">
                    <h1 class="page-title">Assignments</h1>
                    <a href="create-assignment.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Assignment
                    </a>
                </div>

                <?php if (empty($assignments)): ?>
                    <!-- Empty State: Shown when no assignments are found -->
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                        <h3>No assignments yet</h3>
                        <p>Click "Create Assignment" to get started and assign work to your students.</p>
                        <a href="create-assignment.php" class="btn btn-primary" style="margin-top: 1rem;">Create
                            Assignment</a>
                    </div>
                <?php else: ?>
                    <!-- Assignment List -->
                    <div class="assignment-list">
                        <?php foreach ($assignments as $assignment): ?>
                            <?php
                            $missing_submissions = $assignment['total_students'] - $assignment['submission_count'];
                            $dueDate = new DateTime($assignment['due_date']);
                            ?>
                            <div class="assignment-card">
                                <div class="card-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?= htmlspecialchars($assignment['title']) ?></h3>
                                    <p class="card-class"><?= htmlspecialchars($assignment['class_name']) ?></p>
                                    <p class="card-due-date">Due: <?= $dueDate->format('F j, Y, g:i a') ?></p>
                                    <?php if (!empty($assignment['description'])): ?>
                                        <p class="card-notes">Notes: <?= htmlspecialchars($assignment['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-stats">
                                    <div class="stat">
                                        <span class="stat-number"><?= $assignment['submission_count'] ?></span>
                                        <span class="stat-label">Submitted</span>
                                    </div>
                                    <div class="stat">
                                        <span
                                            class="stat-number"><?= $missing_submissions > 0 ? $missing_submissions : 0 ?></span>
                                        <span class="stat-label">Missing</span>
                                    </div>
                                </div>
                                <!-- View Submissions Icon -->
                                <form method="GET" action="view-submissions.php"
                                    style="position: absolute; top: 10px; right: 50px;">
                                    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                    <button type="submit" title="View Submissions"
                                        style="background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-eye" style="color: #007bff; font-size: 1.2rem;"></i>
                                    </button>
                                </form>

                                <!-- Delete Icon -->
                                <form method="POST" action="../../backend-darasa/handlers/assignment_handler.php"
                                    onsubmit="return confirm('Are you sure you want to delete this assignment?');"
                                    style="position: absolute; top: 10px; right: 10px;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                    <button type="submit" title="Delete Assignment"
                                        style="background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-trash" style="color: red; font-size: 1.2rem;"></i>
                                    </button>
                                </form>


                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>

</html>