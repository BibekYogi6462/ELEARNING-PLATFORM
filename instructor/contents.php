<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    $instructor_id = '';
    header('location: login.php');
    exit;
}

if (isset($_POST['delete_video'])) {
    $delete_id = $_POST['video_id'];
    $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

    // Fetch video details for deletion
    $fetch_video = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
    $fetch_video->bind_param("i", $delete_id);
    $fetch_video->execute();
    $result = $fetch_video->get_result();

    if ($result->num_rows > 0) {
        $video_data = $result->fetch_assoc();

        // Delete thumbnail
        $thumb_path = '../uploaded_files/thumbnails/' . $video_data['thumb'];
        if (file_exists($thumb_path)) {
            unlink($thumb_path);
        }

        // Delete video
        $video_path = '../uploaded_files/videos/' . $video_data['video'];
        if (file_exists($video_path)) {
            unlink($video_path);
        }

        // Delete associated likes
        $delete_likes = $conn->prepare("DELETE FROM `likes` WHERE content_id = ?");
        $delete_likes->bind_param("i", $delete_id);
        $delete_likes->execute();

        // Delete associated comments
        $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
        $delete_comments->bind_param("i", $delete_id);
        $delete_comments->execute();

        // Finally, delete the content
        $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ?");
        $delete_content->bind_param("i", $delete_id);
        $delete_content->execute();

        $message[] = 'Video deleted successfully!';
    } else {
        $message[] = 'Video not found or already deleted!';
    }
}
?>


<style>
  <?php include '../css/admin.css'; ?>
</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Added Playlist</title>
  <!-- Boxicon link -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <!-- css link -->
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <?php include '../components/admin_header.php'; ?>
  <section class="contents">
    <h1 class="heading">Your Contents</h1>
    <div class="box-container">
      <div class="add">
        <a href="add_content.php"><i class="bx bx-plus"></i></a>
      </div>
      <?php
      $select_videos = $conn->prepare("SELECT * FROM `content` WHERE instructor_id = ? ORDER BY date DESC");
      $select_videos->bind_param("i", $instructor_id);
      $select_videos->execute();
      $result_videos = $select_videos->get_result();

      if ($result_videos->num_rows > 0) {
          while ($fetch_video = $result_videos->fetch_assoc()) {
              $video_id = $fetch_video['id'];
      ?>
              <div class="box">
                <div class="flex">
                  <div>
                    <i class="bx bx-dots-vertical-rounded" 
                       style="<?= $fetch_video['status'] === 'active' ? 'color:limegreen' : 'color:red'; ?>"></i>
                    <span style="<?= $fetch_video['status'] === 'active' ? 'color:limegreen' : 'color:red'; ?>">
                      <?= htmlspecialchars($fetch_video['status']); ?>
                    </span>
                  </div>
                  <div>
                    <i class="bx bxs-calendar-alt"></i>
                    <span><?= htmlspecialchars($fetch_video['date']); ?></span>
                  </div>
                </div>
                <img src="../uploaded_files/thumbnails/<?= htmlspecialchars($fetch_video['thumb']); ?>" class="thumb">
                <h3 class="title"><?= htmlspecialchars($fetch_video['title']); ?></h3>
                <form action="" method="POST" class="flex-btn">
                  <input type="hidden" name="video_id" value="<?= $video_id; ?>">
                  <a href="update_content.php?get_id=<?= $video_id; ?>" class="btn">Update</a>
                  <input type="submit" value="Delete Video" name="delete_video" class="btn" onclick="return confirm('Delete this video?');">
                  <a href="view_content.php?get_id=<?= $video_id; ?>" class="btn">View Content</a>
                </form>
              </div>
      <?php
          }
      } else {
          echo '<p class="empty">No videos added in playlists yet!</p>';
      }
      ?>
    </div>
  </section>
  <?php include '../components/footer.php'; ?>
  <script src="../js/admin.js"></script>
</body>
</html>
