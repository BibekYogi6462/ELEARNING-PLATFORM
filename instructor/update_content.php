<?php
include '../components/connection.php';

// Check if the instructor is logged in
if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    header('location: login.php');
    exit;
}

// Get content ID from the query string
if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
} else {
    header('location: dashboard.php');
    exit;
}

// Fetch content details
$select_video = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND instructor_id = ?");
$select_video->bind_param("ii", $get_id, $instructor_id);
$select_video->execute();
$result_video = $select_video->get_result();

if ($result_video->num_rows > 0) {
    $fetch_video = $result_video->fetch_assoc();
} else {
    echo '<script>alert("Content not found!"); window.location.href="dashboard.php";</script>';
    exit;
}

if (isset($_POST['update'])) {
    $video_id = filter_var($_POST['video_id'], FILTER_SANITIZE_STRING);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $playlist_id = filter_var($_POST['playlist'], FILTER_SANITIZE_STRING);

    // Update main content fields
    $update_content = $conn->prepare("UPDATE `content` SET title = ?, description = ?, status = ?, playlist_id = ? WHERE id = ?");
    $update_content->bind_param("sssii", $title, $description, $status, $playlist_id, $video_id);

    if (!$update_content->execute()) {
        echo '<script>alert("Failed to update content.");</script>';
        exit;
    }

    // Handle thumbnail update
    $old_thumb = filter_var($_POST['old_thumb'], FILTER_SANITIZE_STRING);

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $rename_image = time() . '_thumb.' . $image_ext;
        $image_folder = '../uploaded_files/thumbnails/' . $rename_image;

        if ($_FILES['image']['size'] > 2000000) {
            echo '<script>alert("Thumbnail size too large!");</script>';
        } else if (move_uploaded_file($image_tmp_name, $image_folder)) {
            $update_thumb = $conn->prepare("UPDATE `content` SET thumb = ? WHERE id = ?");
            $update_thumb->bind_param("si", $rename_image, $video_id);
            $update_thumb->execute();

            if (!empty($old_thumb) && $old_thumb != $rename_image && file_exists('../uploaded_files/thumbnails/' . $old_thumb)) {
                unlink('../uploaded_files/thumbnails/' . $old_thumb);
            }
        }
    }

    // Handle video update
    $old_video = filter_var($_POST['old_video'], FILTER_SANITIZE_STRING);

    if (!empty($_FILES['video']['name'])) {
        $video = $_FILES['video']['name'];
        $video_tmp_name = $_FILES['video']['tmp_name'];
        $video_ext = pathinfo($video, PATHINFO_EXTENSION);
        $rename_video = time() . '_video.' . $video_ext;
        $video_folder = '../uploaded_files/videos/' . $rename_video;

        if (move_uploaded_file($video_tmp_name, $video_folder)) {
            $update_video = $conn->prepare("UPDATE `content` SET video = ? WHERE id = ?");
            $update_video->bind_param("si", $rename_video, $video_id);
            $update_video->execute();

            if (!empty($old_video) && $old_video != $rename_video && file_exists('../uploaded_files/videos/' . $old_video)) {
                unlink('../uploaded_files/videos/' . $old_video);
            }
        }
    }

    echo '<script>alert("Content updated successfully!"); window.location.href = "contents.php?get_id=' . $video_id . '";</script>';
}



if (isset($_POST['delete_video'])) {
    $delete_id = $_POST['video_id'];
    $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
  
    $fetch_video = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
    if (!$fetch_video) {
        die("Error preparing fetch_video: " . $conn->error);
    }
    $fetch_video->bind_param("i", $delete_id);
    $fetch_video->execute();
    $result = $fetch_video->get_result();
  
    if ($result->num_rows > 0) {
        $video_data = $result->fetch_assoc();
  
        // Delete thumbnail and video files
        $thumb_path = '../uploaded_files/thumbnails/' . $video_data['thumb'];
        if (file_exists($thumb_path)) unlink($thumb_path);
  
        $video_path = '../uploaded_files/videos/' . $video_data['video'];
        if (file_exists($video_path)) unlink($video_path);
  
        // Delete related likes, comments, and content
        $delete_likes = $conn->prepare("DELETE FROM `likes` WHERE content_id = ?");
        if (!$delete_likes) {
            die("Error preparing delete_likes: " . $conn->error);
        }
        $delete_likes->bind_param("i", $delete_id);
        $delete_likes->execute();
  
        $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
        if (!$delete_comments) {
            die("Error preparing delete_comments: " . $conn->error);
        }
        $delete_comments->bind_param("i", $delete_id);
        $delete_comments->execute();
  
        $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ?");
        if (!$delete_content) {
            die("Error preparing delete_content: " . $conn->error);
        }
        $delete_content->bind_param("i", $delete_id);
        $delete_content->execute();
  
        $message[] = 'Video deleted successfully!';
    } else {
        $message[] = 'Video not found or already deleted!';
    }
    header("Location: view_playlist.php?get_id=" . $get_id);
      exit;
  }

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Content</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="video-form">
    <h1 class="heading">Update Content</h1>
    <form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="video_id" value="<?= $get_id; ?>">
    <input type="hidden" name="old_thumb" value="<?= htmlspecialchars($fetch_video['thumb']); ?>">
    <input type="hidden" name="old_video" value="<?= htmlspecialchars($fetch_video['video']); ?>">

    <p>Update Status <span>*</span></p>
    <select name="status" class="box" required>
        <option value="<?= htmlspecialchars($fetch_video['status']); ?>" selected><?= htmlspecialchars($fetch_video['status']); ?></option>
        <option value="active">Active</option>
        <option value="deactive">Deactive</option>
    </select>

    <p>Video Title <span>*</span></p>
    <input type="text" name="title" maxlength="150" required value="<?= htmlspecialchars($fetch_video['title']); ?>" class="box">

    <p>Video Description <span>*</span></p>
    <textarea name="description" class="box" required cols="30" rows="10"><?= htmlspecialchars($fetch_video['description']); ?></textarea>

    <p>Video Playlist <span>*</span></p>
    <select name="playlist" class="box" required>
        <?php
        $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE instructor_id = ?");
        $select_playlists->bind_param("i", $instructor_id);
        $select_playlists->execute();
        $result_playlists = $select_playlists->get_result();

        while ($playlist = $result_playlists->fetch_assoc()) {
            $selected = $playlist['id'] == $fetch_video['playlist_id'] ? 'selected' : '';
            echo "<option value='{$playlist['id']}' $selected>" . htmlspecialchars($playlist['title']) . "</option>";
        }
        ?>
    </select>

    <img src="../uploaded_files/thumbnails/<?= htmlspecialchars($fetch_video['thumb']); ?>" alt="Thumbnail">
    <p>Update Thumbnail <span>*</span></p>
    <input type="file" name="image" accept="image/*" class="box">

    <video src="../uploaded_files/videos/<?= htmlspecialchars($fetch_video['video']); ?>" controls></video>
    <p>Update Video <span>*</span></p>
    <input type="file" name="video" accept="video/*" class="box">

    <div class="flex-btn">
        <input type="submit" name="update" value="Update Video" class="btn">
        <a href="view_content.php?get_id=<?= $get_id; ?>" class="btn">View Content</a>
        <input type="submit" name="delete_video" value="Delete Video" class="btn">
    </div>
</form>

</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
