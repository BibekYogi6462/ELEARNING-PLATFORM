<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    $instructor_id = '';
    header('location: login.php');
    exit;
}

if (isset($_POST['delete'])) {
    $delete_id = $_POST['playlist_id'];
    $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

    // Verify playlist ownership
    $verify_playlist_query = "SELECT * FROM `playlist` WHERE id = ? AND instructor_id = ? LIMIT 1";
    $stmt_verify = $conn->prepare($verify_playlist_query);
    $stmt_verify->bind_param("ii", $delete_id, $instructor_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows > 0) {
        // Fetch playlist thumbnail
        $fetch_thumb_query = "SELECT thumb FROM `playlist` WHERE id = ? LIMIT 1";
        $stmt_thumb = $conn->prepare($fetch_thumb_query);
        $stmt_thumb->bind_param("i", $delete_id);
        $stmt_thumb->execute();
        $result_thumb = $stmt_thumb->get_result();
        $fetch_thumb = $result_thumb->fetch_assoc();

        if ($fetch_thumb && file_exists('../uploaded_files/' . $fetch_thumb['thumb'])) {
            unlink('../uploaded_files/' . $fetch_thumb['thumb']);
        }

        // Delete related bookmarks
        $delete_bookmark_query = "DELETE FROM `bookmark` WHERE playlist_id = ?";
        $stmt_delete_bookmark = $conn->prepare($delete_bookmark_query);
        $stmt_delete_bookmark->bind_param("i", $delete_id);
        $stmt_delete_bookmark->execute();

        // Delete playlist
        $delete_playlist_query = "DELETE FROM `playlist` WHERE id = ?";
        $stmt_delete_playlist = $conn->prepare($delete_playlist_query);
        $stmt_delete_playlist->bind_param("i", $delete_id);
        $stmt_delete_playlist->execute();

        $message[] = 'Playlist deleted';
    } else {
        $message[] = 'Playlist already deleted or does not exist';
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
  <!-- Boxicon link  -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <!-- css link  -->
   <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <?php include '../components/admin_header.php'; ?>
  <section class="playlists">
    <h1 class="heading">Added Playlist</h1>
    <div class="box-container">
      <div class="add">
        <a href="add_playlist.php"><i class="bx bx-plus"></i></a>
      </div>

      <?php
      // Select playlists for the instructor
      $select_playlist_query = "SELECT * FROM `playlist` WHERE instructor_id = ? ORDER BY date DESC";
      $stmt = $conn->prepare($select_playlist_query);
      $stmt->bind_param("i", $instructor_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
          while ($fetch_playlist = $result->fetch_assoc()) {
              $playlist_id = $fetch_playlist['id'];

              // Count videos in the playlist
              $count_videos_query = "SELECT * FROM `content` WHERE playlist_id = ?";
              $count_stmt = $conn->prepare($count_videos_query);
              $count_stmt->bind_param("i", $playlist_id);
              $count_stmt->execute();
              $count_result = $count_stmt->get_result();
              $total_videos = $count_result->num_rows;
      ?>
      <div class="box">
        <div class="flex">
          <div>
            <i class="bx bx-dots-vertical-rounded" style="<?php echo $fetch_playlist['status'] == 'active' ? 'color:limegreen;' : 'color:red;'; ?>"></i> <span style="<?php echo $fetch_playlist['status'] == 'active' ? 'color:limegreen;' : 'color:red;'; ?>">
              <?= $fetch_playlist['status']; ?>
            </span>
          </div>
          <div>
            <i class="bx bx-calendar"></i>
            <span><?= $fetch_playlist['date']; ?></span>
          </div>
        </div>
        <div class="thumb">
          <span><?= $total_videos; ?></span>
          <img src="../uploaded_files/<?= $fetch_playlist['thumb']; ?>" alt="">
        </div>
        <h3 class="title"><?= $fetch_playlist['title']; ?></h3>
        <p class="description"><?= $fetch_playlist['description']; ?></p>
        <form action="" method="post" class="flex-btn">
          <input type="hidden" name="playlist_id" value="<?= $playlist_id; ?>">
          <a href="update_playlist.php?get_id=<?= $playlist_id; ?>" class="btn">Update</a>
          <input type="submit" value="Delete" name="delete" class="btn" onclick="return confirm('Delete this playlist?');">
          <a href="view_playlist.php?get_id=<?= $playlist_id; ?>" class="btn">View</a>
        </form>
          </div>
      <?php
          }
      } else {
          echo '<p class="empty">No Playlist Added Yet</p>';
      }
      ?>
    </div>
  </section>
  <?php include '../components/footer.php'; ?>
  <script src="../js/admin.js"></script>
</body>
</html>
