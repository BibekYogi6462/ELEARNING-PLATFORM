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

    // Fetch the playlist details to get the thumbnail file
    $select_playlist_query = "SELECT thumb FROM `playlist` WHERE id = ?";
    $stmt_select = $conn->prepare($select_playlist_query);
    $stmt_select->bind_param("i", $playlist_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows > 0) {
        $playlist = $result->fetch_assoc();
        $thumb_path = "../uploaded_files/" . $playlist['thumb'];

        // Delete the playlist from the database
        $delete_playlist_query = "DELETE FROM `playlist` WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_playlist_query);
        $stmt_delete->bind_param("i", $playlist_id);

        if ($stmt_delete->execute()) {
            // If thumbnail exists, delete it from the server
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
            }
            header("Location: playlists.php?success=Playlist deleted successfully!");
            exit;
        } else {
            $error_message = "Failed to delete the playlist!";
        }
    } else {
        $error_message = "Playlist not found!";
    }
} else {
    $error_message = "Invalid playlist ID!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Playlist</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<?php include '../components/admin2header.php'; ?>

<section class="delete-playlist">
    <h1 class="heading">Delete Playlist</h1>

    <?php if (isset($error_message)) { ?>
        <p class="error"><?= htmlspecialchars($error_message); ?></p>
    <?php } else { ?>
        <p>Are you sure you want to delete this playlist?</p>
        <button id="confirm-delete" class="btn-danger">Delete Playlist</button>
        <script>
            document.getElementById('confirm-delete').onclick = function () {
                if (confirm("This action cannot be undone. Are you sure you want to delete the playlist?")) {
                    window.location.href = "delete_playlist.php?id=<?= htmlspecialchars($playlist_id); ?>&confirm=true";
                }
            };
        </script>
    <?php } ?>
</section>

<?php include '../components/footer.php'; ?>

</body>
</html>
