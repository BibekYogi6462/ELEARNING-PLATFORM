<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Bookmark Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<!-- Banner Section -->
<div class="banner">
  <div class="detail">
    <div class="title">
      <a href="index.php">home</a><span><i class="bx bx-chevron-right"></i>Bookmark</span>
    </div>
    <h1>Bookmarks</h1>
    <p>Explore your saved playlists and dive back into learning!</p>
    <div class="flex-btn">
      <a href="login.php" class="btn">Login To Start</a>
      <a href="contact.php" class="btn">Contact us</a>
    </div>
  </div>
  <img src="image/about.png" alt="About Us" class="aboutimg">
</div>

<!-- Bookmarked Playlists Section -->
<section class="courses">
  <div class="heading">
    <h1>Bookmarked Playlists</h1>
  </div>
  <div class="box-container">
    <?php
    // Fetch bookmarks for the current user
    $select_bookmark = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ?");
    $select_bookmark->bind_param("i", $user_id);
    $select_bookmark->execute();
    $result_bookmark = $select_bookmark->get_result();

    if ($result_bookmark->num_rows > 0) {
      while ($fetch_bookmark = $result_bookmark->fetch_assoc()) {
        $playlist_id = $fetch_bookmark['playlist_id'];

        // Fetch the playlist details
        $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND status = 'active' ORDER BY date DESC");
        $select_courses->bind_param("i", $playlist_id);
        $select_courses->execute();
        $result_courses = $select_courses->get_result();

        if ($result_courses->num_rows > 0) {
          while ($fetch_course = $result_courses->fetch_assoc()) {
            $course_id = $fetch_course['id'];

            // Fetch instructor details
            $select_instructor = $conn->prepare("SELECT * FROM `instructors` WHERE id = ?");
            $select_instructor->bind_param("i", $fetch_course['instructor_id']);
            $select_instructor->execute();
            $result_instructor = $select_instructor->get_result();
            $fetch_instructor = $result_instructor->fetch_assoc();
    ?>
    <div class="box">
      <div class="tutor">
        <img src="uploaded_files/<?= htmlspecialchars($fetch_instructor['image']); ?>" alt="<?= htmlspecialchars($fetch_instructor['name']); ?>">
        <div>
          <h3><?= htmlspecialchars($fetch_instructor['name']); ?></h3>
          <span><?= htmlspecialchars($fetch_instructor['specialization']); ?></span>
        </div>
      </div>
      <img src="uploaded_files/<?= htmlspecialchars($fetch_course['thumb']); ?>" class="thumb" alt="Course Thumbnail">
      <h3 class="title"><?= htmlspecialchars($fetch_course['title']); ?></h3>
      <a href="playlist.php?get_id=<?= $course_id; ?>" class="btn">View Playlist</a>
    </div>
    <?php
          }
        } else {
          echo '<p class="empty">No active playlists found.</p>';
        }
      }
    } else {
      echo '<p class="empty">No bookmarks yet.</p>';
    }
    ?>
  </div>
</section>

<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>
