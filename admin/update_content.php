<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    header('location: login.php');
    exit;
}

// Get the content ID
if (isset($_GET['id'])) {
    $content_id = $_GET['id'];
} else {
    header('location: contents.php');
    exit;
}

// Fetch the content details
$select_content_query = "SELECT * FROM `content` WHERE id = ?";
$stmt = $conn->prepare($select_content_query);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$content_result = $stmt->get_result();

if ($content_result->num_rows > 0) {
    $content = $content_result->fetch_assoc();
} else {
    echo '<script>alert("Content not found!"); window.location.href="contents.php";</script>';
    exit;
}

// Update content
if (isset($_POST['update_content'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Handling file upload for thumbnail
    $thumb = $content['thumb']; // Default to existing thumbnail
    if ($_FILES['thumb']['name'] != '') {
        $thumb = $_FILES['thumb']['name'];
        $thumb_temp = $_FILES['thumb']['tmp_name'];
        move_uploaded_file($thumb_temp, '../uploaded_files/thumbnails/' . $thumb);
    }

    // Handling file upload for video
    $video = $content['video']; // Default to existing video
    if ($_FILES['video']['name'] != '') {
        $video = $_FILES['video']['name'];
        $video_temp = $_FILES['video']['tmp_name'];
        move_uploaded_file($video_temp, '../uploaded_files/videos/' . $video);
    }

    // Update query
    $update_query = "UPDATE `content` SET title = ?, description = ?, thumb = ?, video = ?, status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("sssssi", $title, $description, $thumb, $video, $status, $content_id);

    if ($stmt_update->execute()) {
        echo '<script>alert("Content updated successfully!"); window.location.href="contents.php";</script>';
    } else {
        echo '<script>alert("Error updating content!"); window.location.href="contents.php";</script>';
    }
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
    /* Updated CSS for the Update Content page */
    <style>
.update-content-page {
  padding: 40px;
  background-color: #f9f9f9;
  max-width: 700px;
  margin: 50px auto;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.update-content-page .page-heading {
  font-size: 28px;
  text-align: center;
  margin-bottom: 25px;
  color: #2c3e50;
  font-weight: bold;
}

.update-content-page form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.update-content-page label {
  font-size: 14px;
  font-weight: 600;
  color: #34495e;
  margin-bottom: 5px;
}

.update-content-page input[type="text"],
.update-content-page textarea,
.update-content-page select,
.update-content-page input[type="file"] {
  padding: 10px 12px;
  font-size: 14px;
  border: 1px solid #ced6e0;
  border-radius: 8px;
  width: 100%;
  background-color: #ffffff;
  transition: all 0.3s;
}

.update-content-page textarea {
  height: 120px;
  resize: none;
}

.update-content-page input[type="text"]:focus,
.update-content-page textarea:focus,
.update-content-page select:focus,
.update-content-page input[type="file"]:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
}

.update-content-page img {
  margin-top: 10px;
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  border: 1px solid #dcdde1;
}

.update-content-page input[type="submit"] {
  background-color: #3498db;
  color: #ffffff;
  font-weight: bold;
  padding: 5px 0;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.3s;
  margin-top: 10px;
  width: 100%;
}

.update-content-page input[type="submit"]:hover {
  background-color: #2980b9;
}

.update-content-page a {
  color: #3498db;
  font-weight: 600;
  text-decoration: none;
  width: 100%;
}

.update-content-page a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .update-content-page {
    padding: 20px;
    margin: 20px;
  }
}

</style>

    <!-- <link rel="stylesheet" href="../css/admin2.css"> -->
</head>



<body>
    <?php include '../components/admin2header.php'; ?>

    <section class="update-content-page">
        <h1 class="page-heading">Update Content</h1>
        <div class="content-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="content-box">
                    <label for="title">Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($content['title']); ?>" required>

                    <label for="description">Description</label>
                    <textarea name="description" required><?= htmlspecialchars($content['description']); ?></textarea>

                    <label for="status">Status</label>
                    <select name="status" required>
                        <option value="active" <?= $content['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?= $content['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>

                    <label for="thumb">Thumbnail</label>
                    <input type="file" name="thumb">
                    <!-- <p>Current Thumbnail: <img src="../uploaded_files/thumbnails/<?= htmlspecialchars($content['thumb']); ?>" width="100"></p> -->

                    <label for="video">Video</label>
                    <input type="file" name="video">
                    <p>Current Video: <a href="../uploaded_files/videos/<?= htmlspecialchars($content['video']); ?>" target="_blank">View Video</a></p>

                    <input type="submit" name="update_content" value="Update Content" class="btn5">
                </div>
            </form>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>
    <script src="../js/admin.js"></script>
</body>
</html>
