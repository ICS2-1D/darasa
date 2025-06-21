<?php
session_start();
require_once __DIR__ . '/../../backend-darasa/connect.php';

// Authenticate: ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.html");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$user_data = null;
$table_name = $role === 'teacher' ? 'teachers' : 'students';

try {
    // Fetch user's current data
    $stmt = $pdo->prepare("SELECT u.email, t.full_name FROM users u JOIN {$table_name} t ON u.id = t.user_id WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        die("User profile not found.");
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
    <title>My Profile - Darasa</title>
     <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
    <link rel="stylesheet" href="../dashboard/<?= $role === 'teacher' ? 'teacher.css' : 'student.css' ?>">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar (Dynamically generated) -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header"><div class="logo"><span>Darasa</span></div></div>
            <nav class="sidebar-nav">
                 <?php if ($role === 'teacher'): ?>
                    <a href="../dashboard/teacher.php" class="nav-link"><i class="fas fa-home"></i> <span>Home</span></a>
                    <a href="../assignment/view-assignments.php" class="nav-link"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
                    <a href="../materials/materials.php" class="nav-link"><i class="fas fa-book-open"></i> <span>Materials</span></a>
                    <a href="../announcements/announcements.php" class="nav-link"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                    <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> <span>Profile</span></a>
                <?php else: ?>
                    <a href="../dashboard/student.php" class="nav-link"><i class="fas fa-home"></i> <span>My Classes</span></a>
                    <a href="../dashboard/grades.php" class="nav-link"><i class="fas fa-chart-line"></i> <span>My Grades</span></a>
                    <a href="../announcements/announcements.php" class="nav-link"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a>
                    <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> <span>Profile</span></a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer"><a href="../../backend-darasa/auth/logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="header"><div class="header-content"><span>Welcome, <strong><?= htmlspecialchars($user_data['full_name']) ?></strong></span></div></header>
            <main class="container">
                <div class="page-header"><h1 class="page-title">My Profile</h1></div>

                <?php if (isset($_GET['status'])): ?>
                    <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'error' ?>">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <div class="profile-container">
                    <form action="../../backend-darasa/handlers/profile_handler.php" method="POST">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user_data['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" disabled>
                            <small>Email address cannot be changed.</small>
                        </div>

                        <hr class="form-divider">

                        <h3 class="form-section-title">Change Password</h3>
                        <p class="form-subtitle">Leave these fields blank to keep your current password.</p>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
