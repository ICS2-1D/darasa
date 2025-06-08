<?php
include_once('../../backend-darasa/auth/session_check.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Dashboard | Darasa</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Inter:wght@400;500;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="icon" href="/frontend-darasa/assets/images/logo_white.png" type="image/png">
</head>

<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="logo">
        <img src="/frontend-darasa/assets/images/logo_blue.png" alt="Darasa Logo">
        <span>Darasa</span>
      </div>

      <nav class="nav-menu">
        <div class="nav-top">
          <ul>
            <li class="active">
              <a href="#" data-page="dashboard-teacher">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="course-management-teacher">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Classes</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="assignments-teacher">
                <i class="fas fa-tasks"></i>
                <span>Assignments</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="grading-teacher">
                <i class="fas fa-marker"></i>
                <span>Grading</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="students-teacher">
                <i class="fas fa-users"></i>
                <span>Students</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="announcements-teacher-post">
                <i class="fas fa-bullhorn"></i>
                <span>Announce</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="nav-bottom">
          <ul>
            <li>
              <a href="#" data-page="account-teacher-settings">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </a>
            </li>
            <li>
              <a href="../../backend-darasa/auth/logout.php" data-action="logout-teacher-system">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </a>
            </li>
          </ul>
        </div>
      </nav>
    </aside>

    <div class="main-content-wrapper">
      <header class="main-header">
        <div class="page-title">
          <!-- PHP: Fetches the teacher's name from the session variable -->
          Welcome, <?php echo htmlspecialchars($_SESSION["fullname"]); ?>!
        </div>

        <div class="user-actions">
          <button class="icon-button" aria-label="Create New" title="Create">
            <i class="fas fa-plus-circle"></i>
          </button>
          <button class="icon-button" aria-label="Notifications" title="Notifications">
            <i class="fas fa-bell"></i>
          </button>
          <button class="icon-button" aria-label="User Profile" title="Profile">
            <i class="fas fa-user-circle"></i>
          </button>
        </div>
      </header>

      <main class="page-content">
        <!-- Quick Actions / Overview Section -->
        <section class="content-section">
          <div class="content-section-header">
            <h2>Quick Actions</h2>
          </div>
          <div class="card-grid quick-actions-grid">
            <!-- PHP: These links should point to the correct pages for each action -->
            <article class="action-card">
              <i class="fas fa-folder-plus main-icon"></i>
              <h3>Post Materials</h3>
              <p>Upload notes, videos, and resources for your courses.</p>
              <a href="materials.php?action=add" class="btn btn-primary">Add Materials</a>
            </article>
            <article class="action-card">
              <i class="fas fa-file-signature main-icon"></i>
              <h3>Create Assignment</h3>
              <p>Design and schedule new assignments for students.</p>
              <a href="assignments.php?action=create" class="btn btn-primary">New Assignment</a>
            </article>
            <article class="action-card">
              <i class="fas fa-user-check main-icon"></i>
              <h3>Grade Students</h3>
              <p>Review submissions and provide feedback and grades.</p>
              <a href="grading.php" class="btn btn-primary">Start Grading</a>
            </article>
            <article class="action-card">
              <i class="fas fa-users-cog main-icon"></i>
              <h3>Manage Classes</h3>
              <p>View student lists, manage enrollment, and track attendance.</p>
              <a href="classes.php" class="btn btn-primary">View Classes</a>
            </article>
          </div>
        </section>

        <!-- List of Active Courses (Minimal) -->
        <section class="content-section">
          <div class="content-section-header">
            <h2>My Active Courses</h2>
            <a href="courses.php?view=all" class="btn btn-text">Manage All Courses</a>
          </div>
          <div class="list-group">
            <!-- PHP: Fetch and loop through the teacher's courses from the database -->
            <?php
              // $teacher_id = $_SESSION['user_id'];
              // $active_courses = fetch_teacher_courses($teacher_id);
              // if (empty($active_courses)) {
              //   echo '<div class="info-message">You have no active courses. <a href="courses.php?action=create">Create a new course?</a></div>';
              // } else {
              //   foreach ($active_courses as $course) {
            ?>
            <!-- Example List Item (This block should be inside the PHP loop) -->
            <a href="course_details.php?id=<?php /* echo $course['id']; */ ?>" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1"><?php /* echo htmlspecialchars($course['name']); */ ?> (<?php /* echo htmlspecialchars($course['code']); */ ?>)</h5>
                <small class="text-muted"><?php /* echo $course['student_count']; */ ?> Students</small>
              </div>
              <p class="mb-1"><?php /* echo htmlspecialchars($course['next_task']); */ ?></p>
              <small class="text-muted"><?php /* echo $course['ungraded_count']; */ ?> Ungraded Assignments</small>
            </a>
            <?php
              //   } // End foreach
              // } // End else
            ?>
          </div>
        </section>
      </main>
    </div>
  </div>
  <!-- PHP: Include any necessary JS files or scripts -->
</body>

</html>
