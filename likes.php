<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
    exit;
}
if (isset($_POST['remove'])) {
  if ($user_id != '') {
      $content_id = $_POST['content_id'];
      $content_id = filter_var($content_id, FILTER_SANITIZE_STRING);

      // Verify if the like exists
      $verify_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
      $verify_likes->bind_param("ii", $user_id, $content_id);
      $verify_likes->execute();
      $result_verify = $verify_likes->get_result();

      if ($result_verify->num_rows > 0) {
          // Remove the like
          $remove_likes = $conn->prepare("DELETE FROM `likes` WHERE user_id = ? AND content_id = ?");
          $remove_likes->bind_param("ii", $user_id, $content_id);
          $remove_likes->execute();
          $message[] = 'Removed from likes';
      } else {
          $message[] = 'Like not found';
      }
  } else {
      $message[] = 'Please login first';
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Liked Videos</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<!-- Banner Section -->
<div class="banner">
  <div class="detail">
    <div class="title">
      <a href="index.php">home</a><span><i class="bx bx-chevron-right"></i>Liked Videos</span>
    </div>
    <h1>Liked Videos</h1>
    <p>Explore your saved videos and dive back into learning!</p>
    <div class="flex-btn">
      <a href="login.php" class="btn">Login To Start</a>
      <a href="contact.php" class="btn">Contact us</a>
    </div>
  </div>
  <img src="image/about.png" alt="About Us" class="aboutimg">
</div>

<!-- Liked Videos Section -->
<section class="courses">
  <div class="heading">
    <h1>Liked Videos</h1>
  </div>
  <div class="box-container">
    <?php  
       // Fetch liked videos for the current user
       $select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ?");
       $select_likes->bind_param("i", $user_id);
       $select_likes->execute();
       $result_likes = $select_likes->get_result();

       if ($result_likes->num_rows > 0) {
          while ($fetch_likes = $result_likes->fetch_assoc()) {
              $content_id = $fetch_likes['content_id'];

              // Fetch content details
              $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? ORDER BY date DESC");
              $select_content->bind_param("i", $content_id);
              $select_content->execute();
              $result_content = $select_content->get_result();

              if ($result_content->num_rows > 0) {
                  while ($fetch_content = $result_content->fetch_assoc()) {
                      $instructor_id = $fetch_content['instructor_id'];

                      // Fetch instructor details
                      $select_instructor = $conn->prepare("SELECT * FROM `instructors` WHERE id = ?");
                      $select_instructor->bind_param("i", $instructor_id);
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
      <img src="uploaded_files/thumbnails/<?= htmlspecialchars($fetch_content['thumb']); ?>" class="thumb" alt="Content Thumbnail">
      <h3 class="title"><?= htmlspecialchars($fetch_content['title']); ?></h3>
      <form action="" method="POST">
        <input type="hidden" name="content_id" value="<?= $fetch_content['id']; ?>">
        <a href="watch_video.php?get_id=<?= $fetch_content['id']; ?>" class="btn">Watch Video</a>
        <input type="submit" name="remove" value="Remove" class="btn">
      </form>
    </div>
    <?php
                  }
              } else {
                  echo '<p class="empty">No content found for the liked video.</p>';
              }
          }
       } else {
          echo '<p class="empty">You have not liked any videos yet.</p>';
       }
    ?>
  </div>
</section>

<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>
