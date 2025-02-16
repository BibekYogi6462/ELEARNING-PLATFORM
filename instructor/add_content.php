<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    header('location: login.php');
    exit;
}


//delete video


if (isset($_POST['submit'])) {
    // Sanitizing inputs
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
    $playlist_id = filter_var($_POST['playlist'], FILTER_SANITIZE_STRING);

    // Thumbnail Upload
    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $image_ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename_image = time() . '_thumb.' . $image_ext;
    $image_folder = '../uploaded_files/thumbnails/' . $rename_image;

    // Video Upload
    $video = $_FILES['video']['name'];
    $video = filter_var($video, FILTER_SANITIZE_STRING);
    $video_size = $_FILES['video']['size'];
    $video_ext = pathinfo($video, PATHINFO_EXTENSION);
    $rename_video = time() . '_video.' . $video_ext;
    $video_tmp_name = $_FILES['video']['tmp_name'];
    $video_folder = '../uploaded_files/videos/' . $rename_video;
    
// Supported file extensions and MIME types
$allowed_image_extensions = ['jpg', 'jpeg', 'png'];
$allowed_image_mime_types = ['image/jpeg', 'image/png'];
$allowed_video_extensions = ['mp4', 'avi', 'mkv', 'mov'];
$allowed_video_mime_types = ['video/mp4', 'video/x-msvideo', 'video/x-matroska', 'video/quicktime'];

// Validate image file
if (!in_array(strtolower($image_ext), $allowed_image_extensions)) {
    $message[] = 'Invalid image format! Only JPG, JPEG, and PNG are allowed.';
} elseif (!in_array(mime_content_type($image_tmp_name), $allowed_image_mime_types)) {
    $message[] = 'Invalid image MIME type!';
} elseif ($image_size > 2000000) {
    $message[] = 'Image size too large!';
}

// Validate video file
if (!in_array(strtolower($video_ext), $allowed_video_extensions)) {
    $message[] = 'Invalid video format! Only MP4, AVI, MKV, and MOV are allowed.';
} elseif (!in_array(mime_content_type($video_tmp_name), $allowed_video_mime_types)) {
    $message[] = 'Invalid video MIME type!';
} elseif ($video_size > 600 * 1024 * 1024) { // 600MB size limit
    $message[] = 'Video size too large! Maximum allowed size is 600MB.';
}

// Check if there are validation errors
if (empty($message)) {
    // Prepare and execute the SQL statement
    $add_content = $conn->prepare("INSERT INTO `content` (instructor_id, playlist_id, title, description, video, thumb, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $add_content->bind_param("iisssss", $instructor_id, $playlist_id, $title, $description, $rename_video, $rename_image, $status);

    if ($add_content->execute()) {
        // Move files to their respective folders
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
  <title>Add Playlist</title>
  <!-- Boxicon link  -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <!-- css link  -->
   <link rel="stylesheet" href="../css/admin.css">

</head>
<body>
  <?php include '../components/admin_header.php';?>
  <section class="video-form">
    <h1 class="heading">Upload Content</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <p>Playlist Status <span>*</span></p>
        <select name="status" class="box" required>
            <option value="" selected disabled>--Select Status--</option>
            <option value="active">Active</option>
            <option value="deactive">Deactive</option>
        </select>
        <p>Video Title <span>*</span></p>
        <input type="text" name="title" maxlength="150" required placeholder="Enter Video title" class="box">
        <p>Video Description <span>*</span></p>
        <textarea name="description" class="box" placeholder="Write description" maxlength="1000" cols="30" rows="10"></textarea>
        <p>Video Playlist <span>*</span></p>
        <select name="playlist" class="box" required>
            <option value="" selected disabled>--Select Playlist--</option>
            <?php
            $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE instructor_id = ?");
            $select_playlists->bind_param("i", $instructor_id);
            $select_playlists->execute();
            $result_playlists = $select_playlists->get_result();
            if ($result_playlists->num_rows > 0) {
                while ($fetch_playlist = $result_playlists->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($fetch_playlist['id']) . '">' . htmlspecialchars($fetch_playlist['title']) . '</option>';
                }
            } else {
                echo '<option value="" disabled>No Playlists Added Yet</option>';
            }
            ?>
        </select>
        <p>Select Thumbnail <span>*</span></p>
        <input type="file" name="image" accept="image/*" required class="box">
        <p>Select Video <span>*</span></p>
        <input type="file" name="video" accept="video/*" required class="box">
        <input type="submit" name="submit" value="Upload Video" class="btn">
    </form>
</section>

  <?php include '../components/footer.php';?>
  <script src="../js/admin.js"></script>
</body>
</html>