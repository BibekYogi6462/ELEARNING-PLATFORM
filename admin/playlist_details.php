<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Get the playlist ID from the URL
if (isset($_GET['id'])) {
    $playlist_id = $_GET['id'];

    // Fetch playlist details
    $select_playlist_query = "SELECT * FROM `playlist` WHERE id = ?";
    $stmt_playlist = $conn->prepare($select_playlist_query);
    $stmt_playlist->bind_param("i", $playlist_id);
    $stmt_playlist->execute();
    $playlist_result = $stmt_playlist->get_result();

    if ($playlist_result->num_rows > 0) {
        $playlist = $playlist_result->fetch_assoc();

        // Fetch instructor details
        $instructor_id = $playlist['instructor_id'];
        $select_instructor_query = "SELECT * FROM `instructors` WHERE id = ?";
        $stmt_instructor = $conn->prepare($select_instructor_query);
        $stmt_instructor->bind_param("i", $instructor_id);
        $stmt_instructor->execute();
        $instructor_result = $stmt_instructor->get_result();
        $instructor = $instructor_result->fetch_assoc();
    } else {
        $playlist = null;
    }
} else {
    header('location: playlists.php'); // Redirect if no playlist ID is provided
    exit;
}

// Delete the playlist
if (isset($_POST['delete'])) {
    $delete_query = "DELETE FROM `playlist` WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param("i", $playlist_id);
    $stmt_delete->execute();

    header('location: playlists.php'); // Redirect to the playlists page after deletion
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Details</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/adminplaylist.css">
</head>
<body>

<?php include '../components/admin2header.php'; ?>

<section class="project-details">
    <h1 class="heading">Project Details</h1>

    <?php if ($playlist): ?>
      <div class="details-container">
    <!-- Instructor Info -->
    <div class="instructor-info">
        <h3>Instructor</h3>
        <img src="../uploaded_files/<?= htmlspecialchars($instructor['image']); ?>" alt="Instructor Image">
        <p><strong>Name:</strong> <?= htmlspecialchars($instructor['name']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($instructor['email']); ?></p>
    </div>

    <!-- Playlist Info -->
    <div class="playlist-info">
        <h2><?= htmlspecialchars($playlist['title']); ?></h2>
        <img src="../uploaded_files/<?= htmlspecialchars($playlist['thumb']); ?>" alt="Playlist Thumbnail">
        <p><strong>Description:</strong> <?= htmlspecialchars($playlist['description']); ?></p>
        <p><strong>Date Created:</strong> <?= htmlspecialchars($playlist['date']); ?></p>
    </div>
</div>


    <div class="actions">
        <a href="edit_playlist.php?id=<?= $playlist['id']; ?>" class="btn edit">Edit Playlist</a>
        <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this playlist?');" class="inline-form">
            <button type="submit" name="delete" class="btn delete">Delete Playlist</button>
        </form>
    </div>
    <?php else: ?>
        <p class="empty">Playlist not found</p>
    <?php endif; ?>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>

</body>
</html>
