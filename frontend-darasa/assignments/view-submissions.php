<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// =====================================================
// 1. AUTHENTICATION & AUTHORIZATION
// =====================================================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.html");
    exit;
}

// =====================================================
// 2. INPUT VALIDATION
// =====================================================
$assignment_id = filter_input(INPUT_GET, 'assignment_id', FILTER_VALIDATE_INT);
if (!$assignment_id) {
    die("Error: Invalid Assignment ID.");
}

// =====================================================
// 3. DATABASE OPERATIONS
// =====================================================
try {
    // Fetch Assignment Details
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        die("Error: Assignment not found.");
    }

    // Fetch Enrolled Students and their Submissions
    // This query gets all students from the class associated with the assignment,
    // and LEFT JOINs their submission for this specific assignment.
    $stmt = $pdo->prepare("
        SELECT
            st.id AS student_id,
            st.full_name,
            sub.id AS submission_id,
            sub.submission_text,
            sub.file_path AS submission_file,
            sub.submitted_at,
            sub.grade,
            sub.feedback
        FROM class_students cs
        JOIN students st ON cs.student_id = st.id
        LEFT JOIN submissions sub ON sub.student_id = st.id AND sub.assignment_id = ?
        WHERE cs.class_id = ?
        ORDER BY st.full_name
    ");
    $stmt->execute([$assignment_id, $assignment['class_id']]);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// =====================================================
// 4. HELPER FUNCTIONS
// =====================================================
function formatDateTime($dateString)
{
    return $dateString ? (new DateTime($dateString))->format('M j, Y, g:i a') : 'N/A';
}

function formatDueDate($dateString)
{
    return (new DateTime($dateString))->format('F j, Y, g:i a');
}

function isGraded($grade)
{
    return is_numeric($grade);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Assignment: <?= htmlspecialchars($assignment['title']) ?></title>

    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="../dashboard/teacher.css">
    <link rel="stylesheet" href="assignment.css">
    <link rel="stylesheet" href="grading.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>

<body>
    <div class="page-wrapper">
        <div class="main-content full-width">

            <!-- =====================================================
                 HEADER SECTION
                 ===================================================== -->
            <header class="header">
                <div class="header-content">
                    <a href="view-assignments.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to All Assignments
                    </a>
                </div>
            </header>

            <main class="container">

                <!-- =====================================================
                     ASSIGNMENT OVERVIEW SECTION
                     ===================================================== -->
                <div class="assignment-overview">
                    <h1 class="overview-title">
                        <?= htmlspecialchars($assignment['title']) ?>
                    </h1>

                    <p class="overview-due-date">
                        Due: <?= formatDueDate($assignment['due_date']) ?>
                        <span>&bull;</span>
                        Max Points: <?= htmlspecialchars($assignment['max_points']) ?>
                    </p>

                    <?php if (!empty($assignment['description'])): ?>
                        <p class="overview-description">
                            <?= nl2br(htmlspecialchars($assignment['description'])) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($assignment['file_path'])): ?>
                        <a href="../../<?= htmlspecialchars($assignment['file_path']) ?>" class="btn btn-secondary"
                            download>
                            <i class="fas fa-download"></i> Download Original Assignment File
                        </a>
                    <?php endif; ?>
                </div>

                <!-- =====================================================
                     SUCCESS MESSAGE
                     ===================================================== -->
                <?php if (isset($_GET['grade_status']) && $_GET['grade_status'] === 'success'): ?>
                    <div class="alert success" style="margin-top: 1rem;">
                        Grades saved successfully!
                    </div>
                <?php endif; ?>

                <!-- =====================================================
                     SUBMISSIONS TABLE SECTION
                     ===================================================== -->
                <div class="submissions-container">
                    <h2 class="submissions-title">Student Submissions</h2>

                    <div class="table-wrapper">
                        <table class="submissions-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Submission</th>
                                    <th>Submitted At</th>
                                    <th>Grade</th>
                                    <th>Feedback</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($submissions)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 2rem;">
                                            No students found in this class.
                                        </td>
                                    </tr>

                                <?php else: ?>
                                    <?php foreach ($submissions as $sub): ?>
                                        <?php $is_graded = isGraded($sub['grade']); ?>

                                        <form action="../../backend-darasa/handlers/assignment_handler.php" method="POST">
                                            <input type="hidden" name="action" value="grade_submission">
                                            <input type="hidden" name="submission_id" value="<?= $sub['submission_id'] ?>">
                                            <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">

                                            <tr>
                                                <!-- Student Name -->
                                                <td><?= htmlspecialchars($sub['full_name']) ?></td>

                                                <!-- Submission Content -->
                                                <td>
                                                    <?php if ($sub['submission_id']): ?>
                                                        <?php if (!empty($sub['submission_file'])): ?>
                                                            <a href="../../<?= htmlspecialchars($sub['submission_file']) ?>"
                                                                class="submission-link file" download>
                                                                <i class="fas fa-file-alt"></i> Download File
                                                            </a>
                                                        <?php elseif (!empty($sub['submission_text'])): ?>
                                                            <div class="submission-text-preview" title="Click to view full text"
                                                                onclick="showFullText(this)">
                                                                <?= nl2br(htmlspecialchars($sub['submission_text'])) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="status-badge missing">Not Submitted</span>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- Submission Date -->
                                                <td><?= formatDateTime($sub['submitted_at']) ?></td>

                                                <!-- Grade Input -->
                                                <td>
                                                    <input type="number" name="grade" class="grade-input" placeholder="--"
                                                        value="<?= htmlspecialchars($sub['grade']) ?>"
                                                        max="<?= htmlspecialchars($assignment['max_points']) ?>" min="0"
                                                        <?= (!$sub['submission_id'] || $is_graded) ? 'disabled' : '' ?>>

                                                    <span class="max-points-label">
                                                        / <?= htmlspecialchars($assignment['max_points']) ?>
                                                    </span>
<!-- 
                                                    <?php if ($is_graded): ?>
                                                        <div class="lock-msg">
                                                            Grade already assigned. Contact admin to edit.
                                                        </div>
                                                    <?php endif; ?> -->
                                                </td>

                                                <!-- Feedback Textarea -->
                                                <td>
                                                    <textarea name="feedback" class="feedback-input" rows="1"
                                                        placeholder="Add feedback..." <?= (!$sub['submission_id'] || $is_graded) ? 'disabled' : '' ?>><?= htmlspecialchars($sub['feedback']) ?></textarea>

                                                </td>

                                                <!-- Save Button -->
                                                <td>
                                                    <button type="submit" class="btn btn-primary btn-sm"
                                                        <?= (!$sub['submission_id'] || $is_graded) ? 'disabled' : '' ?>>
                                                        <i class="fas fa-save"></i> Save
                                                    </button>

                                                </td>
                                            </tr>
                                        </form>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- =====================================================
         MODAL FOR FULL TEXT VIEW
         ===================================================== -->
    <div id="fullTextModal" class="modal">
        <div class="modal-content">
            <button class="close-btn btn-icon" onclick="closeModal('fullTextModal')">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="modal-header">Full Submission Text</h3>
            <div id="modalTextContent" class="submitted-text-full"></div>
        </div>
    </div>

    <!-- =====================================================
         JAVASCRIPT
         ===================================================== -->
    <script src="../dashboard/teacher.js"></script>
    <script>
        /**
         * Display full submission text in modal
         * @param {HTMLElement} element - The clicked element containing the text
         */
        function showFullText(element) {
            const modalText = document.getElementById('modalTextContent');
            modalText.innerHTML = element.innerHTML;
            openModal('fullTextModal');
        }
    </script>
</body>

</html>