<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
} else {
    $get_id = '';
    header('location:index.php');
    exit; // Ensure no further code runs
}

if (isset($_POST['save-list'])) {
  if ($user_id != '') {
      $list_id = $_POST['list_id'];
      $list_id = filter_var($list_id, FILTER_SANITIZE_STRING);

      // Check if the playlist is already bookmarked
      $select_list = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
      $select_list->bind_param("ii", $user_id, $list_id);
      $select_list->execute();
      $result = $select_list->get_result();

      if ($result->num_rows > 0) {
          // Remove the bookmark
          $remove_bookmark = $conn->prepare("DELETE FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
          $remove_bookmark->bind_param("ii", $user_id, $list_id);
          $remove_bookmark->execute();
          $message[] = 'Playlist removed from bookmarks.';
      } else {
          // Add the bookmark
          $insert_bookmark = $conn->prepare("INSERT INTO `bookmark` (user_id, playlist_id) VALUES (?, ?)");
          $insert_bookmark->bind_param("ii", $user_id, $list_id);
          $insert_bookmark->execute();
          $message[] = 'Playlist added to bookmarks.';
      }
  } else {
      $message[] = 'Please login first.';
  }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Playlist Details</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<!-- banner section  -->
<div class="banner">
  <div class="detail">
    <div class="title">
      <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>Playlist</span>
    </div>
    <h1>My Playlist</h1>
    <p>Dive in and learn React.js from scratch and way more things ..</p>

  </div>
</div>

<!-- playlist section  -->
<section class="playlist">
  <div class="heading">
    <h1>Playlist Details</h1>
  </div>
  <div class="row">
    <?php
    // Fetch playlist details
    $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND status = ? LIMIT 1");
    $status = 'active';
    $select_playlist->bind_param("is", $get_id, $status);
    $select_playlist->execute();
    $playlist_result = $select_playlist->get_result();

    if ($playlist_result->num_rows > 0) {
        $fetch_playlist = $playlist_result->fetch_assoc();
        $playlist_id = $fetch_playlist['id'];

        // Count videos in the playlist
        $count_videos = $conn->prepare("SELECT COUNT(*) AS total FROM `content` WHERE playlist_id = ?");
        $count_videos->bind_param("i", $playlist_id);
        $count_videos->execute();
        $videos_result = $count_videos->get_result();
        $total_videos = $videos_result->fetch_assoc()['total'];

        // Fetch instructor details
        $select_instructor = $conn->prepare("SELECT * FROM `instructors` WHERE id = ? LIMIT 1");
        $select_instructor->bind_param("i", $fetch_playlist['instructor_id']);
        $select_instructor->execute();
        $instructor_result = $select_instructor->get_result();
        $fetch_instructor = $instructor_result->fetch_assoc();

        // Check if playlist is bookmarked
        $select_bookmark = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
        $select_bookmark->bind_param("ii", $user_id, $playlist_id);
        $select_bookmark->execute();
        $bookmark_result = $select_bookmark->get_result();
    ?>
    <div class="col">
      <form action="" method="POST" class="save-list">
        <input type="hidden" name="list_id" value="<?= $playlist_id; ?>">
        <?php if ($bookmark_result->num_rows > 0): ?>
          <button type="submit" name="save-list"><i class="bx bxs-bookmarks"></i><span>Saved</span></button>
        <?php else: ?>
          <button type="submit" name="save-list"><i class="bx bxs-bookmarks"></i><span>Save Playlist</span></button>
        <?php endif; ?>
      </form>
      <div class="thumb">
        <span><?= $total_videos; ?></span>
        <img src="uploaded_files/<?= htmlspecialchars($fetch_playlist['thumb']); ?>" alt="">
      </div>
    </div>
    <div class="col">
      <div class="tutor">
        <img src="uploaded_files/<?= htmlspecialchars($fetch_instructor['image']); ?>" alt="">
        <div>
          <h3><?= htmlspecialchars($fetch_instructor['name']); ?></h3>
          <span><?= htmlspecialchars($fetch_instructor['specialization']); ?></span>
        </div>
      </div>
      <div class="detail">
        <h3><?= htmlspecialchars($fetch_playlist['title']); ?></h3>
        <p><?= htmlspecialchars($fetch_playlist['description']); ?></p>
        <div class="date"><i class="bx bxs-calendar-alt"></i><span><?= htmlspecialchars($fetch_playlist['date']); ?></span></div>
      </div>
    </div>
    <?php
    } else {
        echo '<p class="empty">This playlist was not found!</p>';
    }
    ?>
  </div>
</section>

<section class="video-container">
  <div class="heading">
    <h1>Playlist Videos</h1>
    <div class="box-container">
      <?php
        // Fetch content for the given playlist ID and active status
        $status = 'active';
        $select_content = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ? AND status = ?");
        $select_content->bind_param("is", $get_id, $status);
        $select_content->execute();
        $content_result = $select_content->get_result();

        if ($content_result->num_rows > 0) {
          while ($fetch_content = $content_result->fetch_assoc()) {
      ?>
        <a href="watch_video.php?get_id=<?= htmlspecialchars($fetch_content['id']); ?>" class="box">
          <i class="bx bx-play"></i>
          <img src="uploaded_files/thumbnails/<?= htmlspecialchars($fetch_content['thumb']); ?>" alt="">
          <h3><?= htmlspecialchars($fetch_content['title']); ?></h3>
        </a>
      <?php 
          }
        } else {
          echo '<p class="empty">No Videos Added Yet!</p>';
        }
      ?>
    </div>
  </div>
</section>

<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>
