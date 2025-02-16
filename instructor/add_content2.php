<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    header('location: login.php');
    exit;
}

// Fetch playlist details
$playlist_id = isset($_GET['playlist_id']) ? intval($_GET['playlist_id']) : 0;
$select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND instructor_id = ?");
$select_playlist->bind_param("ii", $playlist_id, $instructor_id);
$select_playlist->execute();
$playlist = $select_playlist->get_result()->fetch_assoc();

if (!$playlist) {
    echo '<script>alert("Playlist not found!"); window.location.href="playlists.php";</script>';
    exit;
}

// Handle content submission
if (isset($_POST['submit'])) {
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $status = $playlist['status']; // Fetch playlist status automatically

    // Thumbnail upload
    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename_image = time() . '_thumb.' . $image_ext;
    $image_folder = '../uploaded_files/thumbnails/' . $rename_image;

    // Video upload
    $video = $_FILES['video']['name'];
    $video = filter_var($video, FILTER_SANITIZE_STRING);
    $video_tmp_name = $_FILES['video']['tmp_name'];
    $video_ext = pathinfo($video, PATHINFO_EXTENSION);
    $rename_video = time() . '_video.' . $video_ext;
    $video_folder = '../uploaded_files/videos/' . $rename_video;

    // Check file sizes
    if ($_FILES['image']['size'] > 2000000) {
        $message[] = 'Image size too large!';
    } elseif ($_FILES['video']['size'] > 600 * 1024 * 1024) {
        $message[] = 'Video size too large! Maximum allowed size is 600MB.';
    } else {
        // Insert content
        $add_content = $conn->prepare("INSERT INTO `content` (instructor_id, playlist_id, title, description, video, thumb, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $add_content->bind_param("iisssss", $instructor_id, $playlist_id, $title, $description, $rename_video, $rename_image, $status);

        if ($add_content->execute()) {
            if (move_uploaded_file($image_tmp_name, $image_folder) && move_uploaded_file($video_tmp_name, $video_folder)) {
                $message[] = 'New content uploaded successfully!';
            } else {
                $message[] = 'Failed to move uploaded files!';
            }
        } else {
            $message[] = 'Failed to upload content: ' . $conn->error;
        }
        $add_content->close();
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
  <title>Add Content</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <?php include '../components/admin_header.php'; ?>
  <section class="video-form">
    <h1 class="heading">Upload Content</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <p>Playlist Title <span>*</span></p>
        <input type="text" value="<?= htmlspecialchars($playlist['title']); ?>" class="box" readonly>
        
        <p>Playlist Status <span>*</span></p>
        <input type="text" value="<?= htmlspecialchars($playlist['status']); ?>" class="box" readonly>
        
        <p>Video Title <span>*</span></p>
        <input type="text" name="title" maxlength="150" required placeholder="Enter video title" class="box">
        
        <p>Video Description <span>*</span></p>
        <textarea name="description" class="box" placeholder="Write description" maxlength="1000" cols="30" rows="10"></textarea>
        
        <p>Select Thumbnail <span>*</span></p>
        <input type="file" name="image" accept="image/*" required class="box">
        
        <p>Select Video <span>*</span></p>
        <input type="file" name="video" accept="video/*" required class="box">
        
        <input type="submit" name="submit" value="Upload Video" class="btn">
    </form>
  </section>

  <?php include '../components/footer.php'; ?>
  <script src="../js/admin.js"></script>
</body>
</html>
