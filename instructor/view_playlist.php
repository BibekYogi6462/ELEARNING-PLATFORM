<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    header('location: login.php');
    exit;
}

if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
} else {
    header('location: playlists.php');
    exit;
}

// Fetch playlist details
$select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND instructor_id = ?");
$select_playlist->bind_param("ii", $get_id, $instructor_id);
$select_playlist->execute();
$result_playlist = $select_playlist->get_result();

if ($result_playlist->num_rows > 0) {
    $fetch_playlist = $result_playlist->fetch_assoc();
} else {
    echo '<script>alert("Playlist not found!"); window.location.href="playlists.php";</script>';
    exit;
}

// Handle deletion of playlist
if (isset($_POST['delete'])) {
    $delete_playlist = $conn->prepare("DELETE FROM `playlist` WHERE id = ? AND instructor_id = ?");
    $delete_playlist->bind_param("ii", $get_id, $instructor_id);

    if ($delete_playlist->execute()) {
        // Delete associated thumbnail
        if (!empty($fetch_playlist['thumb']) && file_exists('../uploaded_files/' . $fetch_playlist['thumb'])) {
            unlink('../uploaded_files/' . $fetch_playlist['thumb']);
        }
        echo '<script>alert("Playlist deleted successfully!"); window.location.href="playlists.php";</script>';
        exit;
    } else {
        echo '<script>alert("Failed to delete playlist!");</script>';
    }
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
  <title>View Playlist</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <?php include '../components/admin_header.php'; ?>

  <section class="view-playlist">
    <h1 class="heading">View Playlist</h1>
    <div class="row">
      <div class="thumb">
        <span>
          <?php
          // Count videos in the playlist
          $count_videos = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ?");
          $count_videos->bind_param("i", $get_id);
          $count_videos->execute();
          $result_videos = $count_videos->get_result();
          echo $result_videos->num_rows;
          ?>
        </span>
        <img src="../uploaded_files/<?= htmlspecialchars($fetch_playlist['thumb']); ?>" alt="Playlist Thumbnail">
      </div>
      <div class="details">
        <h3 class="title"><?= htmlspecialchars($fetch_playlist['title']); ?></h3>
        <div class="date">
          <i class="bx bxs-calendar-alt"></i> <span><?= htmlspecialchars($fetch_playlist['date']); ?></span>
        </div>
        <div class="description">
          <?= nl2br(htmlspecialchars($fetch_playlist['description'])); ?>
        </div>
        <form action="" method="POST" class="flex-btn">
          <input type="hidden" name="playlist_id" value="<?= $get_id; ?>">
          <a href="update_playlist.php?get_id=<?= $get_id; ?>" class="btn">Update Playlist</a>
          <input type="submit" class="btn" value="Delete" name="delete" onclick="return confirm('Are you sure you want to delete this playlist?');">
        </form>
      </div>
    </div>
  </section>
  <section class="contents">
    <h1 class="heading">Playlists Videos</h1>
    <div class="box-container">
     
    <div class="add">
    <a href="add_content2.php?playlist_id=<?= htmlspecialchars($get_id); ?>"><i class="bx bx-plus"></i></a>
</div>



      <?php
      $select_videos = $conn->prepare("SELECT * FROM `content` WHERE instructor_id = ? AND playlist_id = ?");
      $select_videos->bind_param("ii", $instructor_id, $get_id); // Fixed type and variable names
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
          echo '
          
            <div class="empty">
      <p style="margin-bottom: 1.5rem;">No videos added yet!</p>
      <a href="add_content.php" class="btn" style="margin-top: 1.5rem;">Add Videos</a>
      </div>
          
          ';
      }
      ?>
    
    </div>
  </section>

  <?php include '../components/footer.php'; ?>
  <script src="../js/admin.js"></script>
</body>
</html>
