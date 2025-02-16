<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['tutor_fetch'])) {

    $tutor_email = $_POST['tutor_email'];
    $tutor_email = filter_var($tutor_email, FILTER_SANITIZE_STRING);

    // Fetch instructor details
    $select_instructor = $conn->prepare("SELECT * FROM `instructors` WHERE email = ?");
    $select_instructor->bind_param("s", $tutor_email);
    $select_instructor->execute();
    $result_instructor = $select_instructor->get_result();

    if ($result_instructor->num_rows > 0) {
        $fetch_instructor = $result_instructor->fetch_assoc();
        $instructor_id = $fetch_instructor['id'];

        // Fetch playlist count
        $total_playlists = $conn->prepare("SELECT COUNT(*) AS count FROM playlist WHERE instructor_id = ?");
        $total_playlists->bind_param("i", $instructor_id);
        $total_playlists->execute();
        $playlists_result = $total_playlists->get_result()->fetch_assoc()['count'];

        // Fetch content count
        $total_contents = $conn->prepare("SELECT COUNT(*) AS count FROM content WHERE instructor_id = ?");
        $total_contents->bind_param("i", $instructor_id);
        $total_contents->execute();
        $contents_result = $total_contents->get_result()->fetch_assoc()['count'];

        // Fetch likes count
        $total_likes = $conn->prepare("SELECT COUNT(*) AS count FROM likes WHERE instructor_id = ?");
        $total_likes->bind_param("i", $instructor_id);
        $total_likes->execute();
        $likes_result = $total_likes->get_result()->fetch_assoc()['count'];

        // Fetch comments count
        $total_comments = $conn->prepare("SELECT COUNT(*) AS count FROM comments WHERE instructor_id = ?");
        $total_comments->bind_param("i", $instructor_id);
        $total_comments->execute();
        $comments_result = $total_comments->get_result()->fetch_assoc()['count'];

        // Display instructor details and counts
    } else {
        echo '<p class="error">Instructor not found.</p>';
    }
} else {
    header('Location: teachers.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Teacher Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

   <!-- banner section  -->
    <div class="banner">
      <div class="detail">
        <div class="title">
          <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>Teacher Profile</span>
        </div>
        <h1>teacher profile</h1>
        <p>Dive in and learn React.js from scratch and way more things ..

        </p>
        <div class="flex-btn">
          <a href="login.php" class="btn">Login To Start</a>
          <a href="contact.php" class="btn">Contact us</a>
        </div>
      </div>
      <img src="image/about.png" alt="" class="aboutimg">
    </div>


    <!-- tutor-prfile section  -->
     
<section class="tutor-profile">
  <div class="heading">
    <h1> tutors profile details</h1>
  </div>
  
  <div class="details">
    <div class="tutor">
      <img src="uploaded_files/<?= $fetch_instructor['image']; ?>" >
      <h3><?= htmlspecialchars($fetch_instructor['name']); ?></h3>
      <span><?= htmlspecialchars($fetch_instructor['specialization']); ?></span>
    </div>
    <div class="flex">
    <p>Playlists: <span><?= $playlists_result; ?></span></p>
                <p>Total Videos: <span><?= $contents_result; ?></span></p>
                <p>Total Likes: <span><?= $likes_result; ?></span></p>
                <p>Total Comments: <span><?= $comments_result; ?></span></p>
    </div>
  </div>
 
</section>

<!-- course section  -->
 <!-- Courses Section -->
<div class="courses">
    <div class="heading">
        <span>Top Popular Courses</span>
        <h1>Gurushishya Courses Students Can Join With Us</h1>
    </div>
    <div class="box-container">
    <?php
    // Fetch active courses for the instructor
    $select_courses = $conn->prepare("SELECT * FROM playlist WHERE instructor_id = ? AND status = ?");
    $status = 'active';
    $select_courses->bind_param("is", $instructor_id, $status);
    $select_courses->execute();
    $result_courses = $select_courses->get_result();

    if ($result_courses->num_rows > 0) {
        while ($fetch_courses = $result_courses->fetch_assoc()) {
            $course_id = $fetch_courses['id'];
            $course_thumb = !empty($fetch_courses['thumb']) ? $fetch_courses['thumb'] : 'default_course.png';
            ?>
            <div class="box">
                <div class="tutor">
                    <img src="uploaded_files/<?= htmlspecialchars($fetch_instructor['image'] ?? 'default_instructor.png'); ?>" alt="Instructor">
                    <div>
                        <h3><?= htmlspecialchars($fetch_instructor['name'] ?? 'Unknown'); ?></h3>
                        <span><?= htmlspecialchars($fetch_courses['date']); ?></span>
                    </div>
                </div>
                <img src="uploaded_files/<?= htmlspecialchars($course_thumb); ?>" alt="Course Thumbnail" class="thumb">
                <h3 class="title"><?= htmlspecialchars($fetch_courses['title']); ?></h3>
                <a href="playlist.php?get_id=<?= htmlspecialchars($course_id); ?>" class="btn">View Playlist</a>
            </div>
            <?php
        }
    } else {
        echo '<p class="empty">No courses added yet.</p>';
    }
    ?>
</div>







<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>