<?php
// Ensures a user is logged in and is authorized to see this page.
include_once('../../backend-darasa/auth/session_check.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | Darasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../dashboard/dashboard.css">
    <link rel="icon" href="../assets/images/logo_white.png" type="image/png">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logo_blue.png" alt="Darasa Logo">
                <span>Darasa</span>
            </div>
            <nav class="nav-menu">
                <div class="nav-top">
                    <ul>
                        <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                        <li><a href="#"><i class="fas fa-chalkboard-teacher"></i><span>Classes</span></a></li>
                        <li><a href="#"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
                        <li><a href="#"><i class="fas fa-marker"></i><span>Grading</span></a></li>
                        <li><a href="#"><i class="fas fa-users"></i><span>Students</span></a></li>
                        <li><a href="#"><i class="fas fa-bullhorn"></i><span>Announce</span></a></li>
                    </ul>
                </div>
                <div class="nav-bottom">
                    <ul>
                        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                        <li><a href="../../backend-darasa/auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                    </ul>
                </div>
            </nav>
        </aside>

        <div class="main-content-wrapper">
            <header class="main-header">
                <div class="page-title">
                    Welcome, <?php echo htmlspecialchars($_SESSION["fullname"]); ?>!
                </div>
                <div class="user-actions">
                    <button class="icon-button" aria-label="Notifications" title="Notifications"><i class="fas fa-bell"></i></button>
                    <button class="icon-button" aria-label="User Profile" title="Profile"><i class="fas fa-user-circle"></i></button>
                </div>
            </header>

            <main class="page-content">
                <!-- Loading indicator -->
                <div id="loading-indicator" class="loading-indicator" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>

                <!-- Error message container -->
                <div id="error-message" class="error-message" style="display: none;"></div>

                <!-- Success message container -->
                <div id="success-message" class="success-message" style="display: none;"></div>

                <!-- The courses will be displayed here -->
                <section class="content-section">
                    <div class="content-section-header">
                        <h2>My Classes</h2>
                        <div class="class-stats">
                            <span id="class-count">0 classes</span>
                        </div>
                    </div>
                    
                    <!-- This grid will be populated with course cards via JavaScript -->
                    <div class="card-grid" id="class-grid">
                        <div id="no-classes-message" class="info-message" style="display: none;">
                            You have not created any classes yet. Click the + button to get started.
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- Floating Action Button to open the modal -->
    <div class="fab-container">
        <button class="fab" id="create-class-btn" aria-label="Create Class">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Modal for Creating a New Class -->
    <div class="modal-overlay" id="create-class-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Class</h2>
                <button class="modal-close-btn" id="modal-close-btn">&times;</button>
            </div>
            <!-- This form will submit data asynchronously using JavaScript -->
            <form id="create-class-form">
                <div class="form-group">
                    <label for="class-name">Class Name (required)</label>
                    <input type="text" id="class-name" name="class_name" required maxlength="100" placeholder="e.g., Introduction to Web Development">
                </div>
                <div class="form-group">
                    <label for="class-description">Description (optional)</label>
                    <textarea id="class-description" name="class_description" rows="3" placeholder="Brief description of the class"></textarea>
                </div>
                <div class="form-note">
                    <small><i class="fas fa-info-circle"></i> A unique class code will be generated automatically for students to join.</small>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="modal-cancel-btn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="create-class-submit">
                        <span class="btn-text">Create Class</span>
                        <span class="btn-loading" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Class Options Modal -->
    <div class="modal-overlay" id="class-options-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Class Options</h2>
                <button class="modal-close-btn" id="options-modal-close-btn">&times;</button>
            </div>
            <div class="class-options-menu">
                <button class="option-btn" id="view-class-btn">
                    <i class="fas fa-eye"></i> View Class Details
                </button>
                <button class="option-btn" id="edit-class-btn">
                    <i class="fas fa-edit"></i> Edit Class
                </button>
                <button class="option-btn" id="view-students-btn">
                    <i class="fas fa-users"></i> View Students
                </button>
                <button class="option-btn danger" id="delete-class-btn">
                    <i class="fas fa-trash"></i> Delete Class
                </button>
            </div>
        </div>
    </div>

    <script src="../dashboard/teacher_dashboard.js"></script>
</body>
</html>