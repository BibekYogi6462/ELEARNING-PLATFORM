<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Check if a playlist ID is provided
if (isset($_GET['id'])) {
    $playlist_id = $_GET['id'];

    // Fetch the playlist details
    $select_playlist_query = "SELECT * FROM `playlist` WHERE id = ?";
    $stmt = $conn->prepare($select_playlist_query);
    $stmt->bind_param("i", $playlist_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $playlist = $result->fetch_assoc();
    } else {
        echo "Playlist not found!";
        exit;
    }
} else {
    echo "Invalid playlist ID!";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    // Handle file upload for the thumbnail
    if (!empty($_FILES['thumb']['name'])) {
        $thumb_name = $_FILES['thumb']['name'];
        $thumb_tmp_name = $_FILES['thumb']['tmp_name'];
        $thumb_folder = "../uploaded_files/" . $thumb_name;

        // Move the uploaded file
        if (move_uploaded_file($thumb_tmp_name, $thumb_folder)) {
            $update_thumb_query = "UPDATE `playlist` SET thumb = ? WHERE id = ?";
            $stmt_thumb = $conn->prepare($update_thumb_query);
            $stmt_thumb->bind_param("si", $thumb_name, $playlist_id);
            $stmt_thumb->execute();
        }
    }

    // Update playlist details
    $update_playlist_query = "UPDATE `playlist` SET title = ?, description = ?, date = ? WHERE id = ?";
    $stmt_update = $conn->prepare($update_playlist_query);
    $stmt_update->bind_param("sssi", $title, $description, $date, $playlist_id);
    if ($stmt_update->execute()) {
        $success_message = "Playlist updated successfully!";
        header("Location: playlists.php");
        exit;
    } else {
        $error_message = "Failed to update the playlist!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Playlist</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/adminplaylist.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

</head>
<body>

<?php include '../components/admin2header.php'; ?>

<section class="edit-playlist">
    <h1 class="heading">Edit Playlist</h1>

    <form action="" method="POST" enctype="multipart/form-data" class="edit-form">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($playlist['title']); ?>" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="5" required><?= htmlspecialchars($playlist['description']); ?></textarea>

        <label for="date">Date:</label>
        <input type="date" name="date" id="date" value="<?= htmlspecialchars($playlist['date']); ?>" required>

        <label for="thumb">Thumbnail (optional):</label>
        <input type="file" name="thumb" id="thumb" accept="image/*">

        <button type="submit" class="btn">Update Playlist</button>
    </form>

    <?php if (isset($success_message)) { ?>
        <p class="success"><?= $success_message; ?></p>
    <?php } elseif (isset($error_message)) { ?>
        <p class="error"><?= $error_message; ?></p>
    <?php } ?>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>


</body>
</html>
