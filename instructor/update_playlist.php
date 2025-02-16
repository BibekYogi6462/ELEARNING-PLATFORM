<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    $instructor_id = '';
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
$fetch_playlist_query = "SELECT * FROM `playlist` WHERE id = ? AND instructor_id = ?";
$stmt_fetch_playlist = $conn->prepare($fetch_playlist_query);
$stmt_fetch_playlist->bind_param("ii", $get_id, $instructor_id);
$stmt_fetch_playlist->execute();
$result_fetch_playlist = $stmt_fetch_playlist->get_result();

if ($result_fetch_playlist->num_rows > 0) {
    $fetch_playlist = $result_fetch_playlist->fetch_assoc();
} else {
    echo '<script>alert("Playlist not found!"); window.location.href="playlists.php";</script>';
    exit;
}

// Handle form submission for updating the playlist
$message = [];

if (isset($_POST['submit'])) {
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

    // Check for duplicate title in other playlists of the same instructor
    $check_duplicate_query = "SELECT id FROM `playlist` WHERE title = ? AND instructor_id = ? AND id != ?";
    $stmt_check_duplicate = $conn->prepare($check_duplicate_query);
    $stmt_check_duplicate->bind_param("sii", $title, $instructor_id, $get_id);
    $stmt_check_duplicate->execute();
    $result_check_duplicate = $stmt_check_duplicate->get_result();

    if ($result_check_duplicate->num_rows > 0) {
        $message[] = 'Playlist title already exists. Please choose a different title.';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $image = filter_var($image, FILTER_SANITIZE_STRING);
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            $rename = time() . '.' . $ext; // Rename file using a timestamp
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_folder = '../uploaded_files/' . $rename;

            // Remove the existing thumbnail if available
            if (!empty($fetch_playlist['thumb']) && file_exists('../uploaded_files/' . $fetch_playlist['thumb'])) {
                unlink('../uploaded_files/' . $fetch_playlist['thumb']);
            }
            move_uploaded_file($image_tmp_name, $image_folder);
        } else {
            $rename = $fetch_playlist['thumb']; // Keep existing thumbnail
        }

        // Update playlist details
        $update_playlist_query = "UPDATE `playlist` SET title = ?, description = ?, thumb = ?, status = ? WHERE id = ? AND instructor_id = ?";
        $stmt_update_playlist = $conn->prepare($update_playlist_query);
        $stmt_update_playlist->bind_param("ssssii", $title, $description, $rename, $status, $get_id, $instructor_id);

        if ($stmt_update_playlist->execute()) {
            $message[] = 'Playlist updated successfully!';
            $fetch_playlist['title'] = $title;
            $fetch_playlist['description'] = $description;
            $fetch_playlist['thumb'] = $rename;
            $fetch_playlist['status'] = $status;
        } else {
            $message[] = 'Failed to update playlist: ' . $conn->error;
        }
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
  <title>Update Playlist</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <?php include '../components/admin_header.php'; ?>

  <section class="playlist-form">
    <h1 class="heading">Update Playlist</h1>


    <form action="" method="post" enctype="multipart/form-data">
      <p>Playlist Status <span>*</span></p>
      <select name="status" class="box" required>
        <option value="<?= $fetch_playlist['status']; ?>" selected><?= ucfirst($fetch_playlist['status']); ?></option>
        <option value="active">Active</option>
        <option value="deactive">Deactive</option>
      </select>

      <p>Playlist Title <span>*</span></p>
      <input type="text" name="title" class="box" maxlength="150" required value="<?= htmlspecialchars($fetch_playlist['title']); ?>" placeholder="Enter playlist title">

      <p>Playlist Description <span>*</span></p>
      <textarea name="description" class="box" placeholder="Write description" maxlength="1000" cols="30" rows="10" required><?= htmlspecialchars($fetch_playlist['description']); ?></textarea>

      <p>Playlist Thumbnail <span>*</span></p>
      <img src="../uploaded_files/<?= htmlspecialchars($fetch_playlist['thumb']); ?>" alt="Current Thumbnail" style="max-width: 200px; margin-top: 10px;">
      <input type="file" name="image" accept="image/*" class="box">

      <div class="flex-btn">
        <input type="submit" name="submit" value="Update Playlist" class="btn">
        <input type="submit" name="delete" value="Delete Playlist" class="btn" onclick="return confirm('Are you sure you want to delete this playlist?');">
        <a href="view_playlist.php?get_id=<?= $playlist_id; ?>" class="btn">View Playlist</a>
      </div>
    </form>
  </section>

  <?php include '../components/footer.php'; ?>
  <script src="../js/admin.js"></script>
</body>
</html>
