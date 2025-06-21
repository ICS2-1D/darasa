<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// 1. Authentication and Authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.html");
    exit;
}

// 2. Get Input and Student Info
$class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
if (!$class_id) {
    die("Invalid Class ID.");
}

$student_id = null;
try {
    // Get student_id from the session's user_id
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch();
    if ($student) {
        $student_id = $student['id'];
    } else {
        die("Student profile not found.");
    }

    // 3. Fetch Assignments and Submission Status for this student in this class
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.title, a.description, a.due_date, a.max_points,
            a.file_path AS assignment_file,
            c.name AS class_name,
            s.id AS submission_id,
            s.submission_text,
            s.file_path AS submission_file,
            s.submitted_at,
            s.grade
        FROM assignments a
        JOIN classes c ON a.class_id = c.id
        LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
        WHERE a.class_id = ?
        ORDER BY a.due_date DESC
    ");
    $stmt->execute([$student_id, $class_id]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Class Name for the header, runs only if there are assignments
    $className = empty($assignments) ? '' : $assignments[0]['class_name'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments for <?= htmlspecialchars($className) ?> - Darasa</title>
    <link rel="stylesheet" href="../dashboard/teacher.css"> <!-- Reusing teacher.css for base styles -->
    <link rel="stylesheet" href="assignment.css"> <!-- Main assignment styles -->
    <link rel="stylesheet" href="student-assignment.css"> <!-- Student-specific styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>
<body>
    <div class="page-wrapper">
        <div class="main-content full-width">
            <header class="header">
                 <div class="header-content">
                    <a href="../dashboard/student.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
            </header>
            <main class="container">
                <div class="page-header">
                    <h1 class="page-title">Assignments for <?= htmlspecialchars($className) ?></h1>
                </div>

                <?php if (isset($_GET['submit_status']) && $_GET['submit_status'] === 'success'): ?>
                    <div class="alert success">Your assignment was submitted successfully!</div>
                <?php endif; ?>

                <div class="assignment-list">
                    <?php if (empty($assignments)): ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-smile-beam"></i></div>
                            <h3>All caught up!</h3>
                            <p>There are no assignments for this class right now.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($assignments as $assignment): 
                            $isSubmitted = !is_null($assignment['submission_id']);
                            $dueDate = new DateTime($assignment['due_date']);
                            $isPastDue = (new DateTime() > $dueDate);
                        ?>
                        <div class="student-assignment-card <?= $isSubmitted ? 'submitted' : '' ?> <?= $isPastDue && !$isSubmitted ? 'past-due' : '' ?>">
                            <div class="card-header">
                                <div class="header-info">
                                    <h3 class="card-title"><?= htmlspecialchars($assignment['title']) ?></h3>
                                    <p class="card-due-date">
                                        Due: <?= $dueDate->format('F j, Y, g:i a') ?>
                                        <span>&bull;</span>
                                        <?= $assignment['max_points'] ?? 100 ?> points
                                    </p>
                                </div>
                                <div class="header-status">
                                    <?php if ($isSubmitted): ?>
                                        <span class="status-badge submitted-badge"><i class="fas fa-check-circle"></i> Submitted</span>
                                    <?php elseif ($isPastDue): ?>
                                        <span class="status-badge missing-badge"><i class="fas fa-times-circle"></i> Missing</span>
                                    <?php else: ?>
                                        <span class="status-badge pending-badge"><i class="far fa-clock"></i> Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($assignment['description'])): ?>
                                    <p class="assignment-description"><?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($assignment['assignment_file'])): ?>
                                    <a href="../../<?= htmlspecialchars($assignment['assignment_file']) ?>" class="attachment-link" download>
                                        <i class="fas fa-paperclip"></i>
                                        <?= basename($assignment['assignment_file']) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-submission-area">
                                <?php if ($isSubmitted): ?>
                                    <h4>Your Submission</h4>
                                    <?php if (!empty($assignment['submission_text'])): ?>
                                        <div class="submitted-text"><?= nl2br(htmlspecialchars($assignment['submission_text'])) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($assignment['submission_file'])): ?>
                                        <a href="../../<?= htmlspecialchars($assignment['submission_file']) ?>" class="attachment-link submitted" download>
                                            <i class="fas fa-file-alt"></i> Your uploaded file: <?= basename($assignment['submission_file']) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else: // Show submission form if not submitted ?>
                                    <form action="../../backend-darasa/handlers/assignment_handler.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="submit_student">
                                        <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                        <input type="hidden" name="student_id" value="<?= $student_id ?>">
                                        <input type="hidden" name="class_id" value="<?= $class_id ?>">
                                        
                                        <!-- If assignment is text-based -->
                                        <?php if(empty($assignment['assignment_file'])): ?>
                                        <div class="form-group">
                                            <label for="submission_text_<?= $assignment['id'] ?>">Type your answer here</label>
                                            <textarea name="submission_text" id="submission_text_<?= $assignment['id'] ?>" rows="5" required <?= $isPastDue ? 'disabled' : '' ?>></textarea>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- If assignment is file-based or allows optional upload -->
                                        <div class="form-group">
                                            <label for="submission_file_<?= $assignment['id'] ?>">Or upload your work</label>
                                            <input type="file" name="submission_file" id="submission_file_<?= $assignment['id'] ?>" <?= !empty($assignment['assignment_file']) ? 'required' : '' ?> <?= $isPastDue ? 'disabled' : '' ?>>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary" <?= $isPastDue ? 'disabled' : '' ?>>
                                                <i class="fas fa-paper-plane"></i> <?= $isPastDue ? 'Past Due' : 'Submit Assignment' ?>
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
