<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    header('location: login.php');
    exit;
}

// Handle deletion of playlist
if (isset($_POST['delete_playlist'])) {
    $delete_id = $_POST['playlist_id'];
    $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

    // Verify playlist ownership
    $fetch_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND instructor_id = ? LIMIT 1");
    $fetch_playlist->bind_param("ii", $delete_id, $instructor_id);
    $fetch_playlist->execute();
    $result = $fetch_playlist->get_result();

    if ($result->num_rows > 0) {
        $playlist_data = $result->fetch_assoc();

        // Delete thumbnail
        $thumb_path = '../uploaded_files/' . $playlist_data['thumb'];
        if (file_exists($thumb_path)) {
            unlink($thumb_path);
        }

        // Delete related bookmarks
        $delete_bookmarks = $conn->prepare("DELETE FROM `bookmark` WHERE playlist_id = ?");
        $delete_bookmarks->bind_param("i", $delete_id);
        $delete_bookmarks->execute();

        // Delete the playlist
        $delete_playlist = $conn->prepare("DELETE FROM `playlist` WHERE id = ?");
        $delete_playlist->bind_param("i", $delete_id);
        $delete_playlist->execute();

        echo '<script>alert("Playlist deleted successfully!");</script>';
    } else {
        echo '<script>alert("Playlist not found or already deleted!");</script>';
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
  <title>Search Playlist</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <?php include '../components/admin_header.php'; ?>

  <section class="playlists">
    <h1 class="heading">Search Results</h1>
    <div class="box-container">
      <?php
      if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
          $search = '%' . trim($_POST['search']) . '%';

          // Fetch matching playlists
          $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE `title` LIKE ? AND `instructor_id` = ? ORDER BY `date` DESC");
          $select_playlists->bind_param("si", $search, $instructor_id);
          $select_playlists->execute();
          $result_playlists = $select_playlists->get_result();

          if ($result_playlists->num_rows > 0) {
              while ($fetch_playlist = $result_playlists->fetch_assoc()) {
                  $playlist_id = $fetch_playlist['id'];

                  // Count videos in the playlist
                  $count_videos = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ?");
                  $count_videos->bind_param("i", $playlist_id);
                  $count_videos->execute();
                  $result_videos = $count_videos->get_result();
                  $total_videos = $result_videos->num_rows;
                  ?>
                  <div class="box">
                    <div class="flex">
                      <div>
                        <i class="bx bx-dots-vertical-rounded" style="<?= $fetch_playlist['status'] === 'active' ? 'color:limegreen' : 'color:red'; ?>;"></i>
                        <span style="<?= $fetch_playlist['status'] === 'active' ? 'color:limegreen' : 'color:red'; ?>;">
                          <?= htmlspecialchars($fetch_playlist['status']); ?>
                        </span>
                      </div>
                      <div>
                        <i class="bx bxs-calendar-alt"></i>
                        <span><?= htmlspecialchars($fetch_playlist['date']); ?></span>
                      </div>
                    </div>
                    <img src="../uploaded_files/<?= htmlspecialchars($fetch_playlist['thumb']); ?>" class="thumb" alt="Playlist Thumbnail">
                    <h3 class="title"><?= htmlspecialchars($fetch_playlist['title']); ?></h3>
                    <p class="description"> <?= htmlspecialchars($fetch_playlist['description']); ?> </p>
                    <form action="" method="POST" class="flex-btn">
                      <input type="hidden" name="playlist_id" value="<?= $playlist_id; ?>">
                      <a href="update_playlist.php?get_id=<?= $playlist_id; ?>" class="btn">Update</a>
                      <input type="submit" value="Delete Playlist" name="delete_playlist" class="btn" onclick="return confirm('Are you sure you want to delete this playlist?');">
                      <a href="view_playlist.php?get_id=<?= $playlist_id; ?>" class="btn">View Playlist</a>
                    </form>
                  
                  </div>
                  <?php
              }
          } else {
              echo '<p class="empty">No playlist found matching your search!</p>';
          }
      } else {
          echo '<p class="empty">Please enter a search query!</p>';
      }
      ?>
    </div>
  </section>

  <?php include '../components/footer.php'; ?>
  <script src="../js/admin.js"></script>
</body>
</html>
