<?php
// Ensures a user is logged in before they can access this page.
// You might want to add specific role checks here (e.g., ensure user is a student).
include_once('../../backend-darasa/auth/session_check.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | Darasa</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- Main Stylesheet -->
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
              <a href="#" data-page="home-student">
                <i class="fas fa-home"></i>
                <span>Home</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="announcements-student">
                <i class="fas fa-bullhorn"></i>
                <span>Announcements</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="grades-student">
                <i class="fas fa-graduation-cap"></i>
                <span>Grades</span>
              </a>
            </li>
            <li>
              <a href="#" data-page="calendar-student">
                <i class="fas fa-calendar-alt"></i>
                <span>Calendar</span>
              </a>
            </li>
          </ul>
        </div>
        
        <div class="nav-bottom">
          <ul>
            <li>
              <a href="#" data-page="account-student">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </a>
            </li>
            <li>
              <!-- Static link to the logout script -->
              <a href="../../backend-darasa/auth/logout.php" data-action="logout-student">
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
            <!-- PHP: Fetch and display the logged-in student's name from the session. -->
            Welcome, <?php echo htmlspecialchars($_SESSION["fullname"] ?? 'Student'); ?>
        </div>
        <div class="user-actions">
            <button class="icon-button" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <!-- PHP: Fetch notification count for the student.
                <?php
                  // $notification_count = fetch_student_notification_count($_SESSION['user_id']);
                  // if ($notification_count > 0) {
                  //   echo '<span class="badge">' . $notification_count . '</span>';
                  // }
                ?>
                -->
            </button>
            <button class="icon-button" aria-label="User Profile">
                <i class="fas fa-user-circle"></i>
            </button>
        </div>
      </header>
      
      <main class="page-content">
        <!-- Enrolled Courses Section -->
        <section class="content-section">
          <div class="content-section-header">
            <h2>My Enrolled Courses</h2>
          </div>
          <div class="card-grid">
            <!-- PHP: Backend logic to fetch and loop through the student's enrolled courses. -->
            <?php
              // 1. Fetch enrolled courses for the student from the database.
              // $student_id = $_SESSION['user_id'];
              // $enrolled_courses = fetch_student_courses($student_id);
              
              // 2. Check if there are any courses.
              // if (empty($enrolled_courses)) {
              //   echo '<div class="info-message">You are not currently enrolled in any courses.</div>';
              // } else {
              //   // 3. Loop through the courses and display them as cards.
              //   $color_index = 1;
              //   foreach ($enrolled_courses as $course) {
            ?>
            <!-- Example Course Card (This block should be inside the PHP loop) -->
            <article class="course-card" data-course-id="<?php /* echo $course['id']; */ ?>">
              <div class="course-card-header color-1"> <!-- PHP: You can cycle through colors: color-<?php /* echo ($color_index++ % 5) + 1; */ ?> -->
                <div class="course-number"><?php /* echo htmlspecialchars($course['code']); */ ?>COURSE_CODE</div>
                <h3 class="course-title"><a href="course.php?id=<?php /* echo $course['id']; */ ?>"><?php /* echo htmlspecialchars($course['name']); */ ?>Introduction to Programming</a></h3>
              </div>
              <div class="course-card-content">
                <p class="course-instructor">Lecturer: <?php /* echo htmlspecialchars($course['lecturer_name']); */ ?></p>
                <p class="course-summary"><?php /* echo htmlspecialchars($course['summary']); */ ?></p>
              </div>
              <div class="course-card-actions">
                  <a href="course.php?id=<?php /* echo $course['id']; */ ?>" class="btn btn-text btn-text-primary">View Course</a>
              </div>
            </article>
            <!-- End Example Course Card -->
            <?php
              //   } // End foreach loop
              // } // End else
            ?>
          </div>
        </section>

        <!-- Assignments/To-Do List Section -->
        <section class="content-section">
          <div class="content-section-header">
            <h2>Assignments & To-Do</h2>
          </div>
          <div class="todo-list">
            <!-- PHP: Backend logic to fetch and loop through pending assignments. -->
            <?php
              // 1. Fetch pending assignments for the student.
              // $pending_assignments = fetch_pending_assignments($student_id);

              // 2. Check if there are any assignments.
              // if (empty($pending_assignments)) {
              //   echo '<div class="info-message">You have no pending assignments. Great job!</div>';
              // } else {
              //   // 3. Loop through assignments and display them.
              //   foreach ($pending_assignments as $assignment) {
            ?>
            <!-- Example Assignment Item (This block should be inside the PHP loop) -->
            <div class="list-item">
              <div class="list-item-content">
                <h4><?php /* echo htmlspecialchars($assignment['title']); */ ?> (<?php /* echo htmlspecialchars($assignment['course_code']); */ ?>)</h4>
                <p>Due: <span class="due-date"><?php /* echo date('F j, Y', strtotime($assignment['due_date'])); */ ?></span></p>
              </div>
              <div class="list-item-actions">
                  <a href="assignment.php?id=<?php /* echo $assignment['id']; */ ?>" class="btn-icon" title="View Assignment"><i class="fas fa-chevron-right"></i></a>
              </div>
            </div>
            <?php
              //   } // End foreach
              // } // End else
            ?>
          </div>
        </section>
      </main>
    </div>
  </div>
  <!-- PHP: You can include JS files that might need session data. -->
  <script>
    // Example of passing a PHP variable to JavaScript
    const currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
  </script>
</body>
</html>
