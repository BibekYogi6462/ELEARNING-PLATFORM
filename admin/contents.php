<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Fetch content details with instructor and playlist information
$contents = [];
$select_contents_query = "
    SELECT 
        content.id AS content_id,
        content.title AS content_title,
        content.description AS content_description,
        content.video AS content_video,
        content.thumb AS content_thumb,
        content.date AS content_date,
        content.status AS content_status,
        instructors.name AS instructor_name,
        playlist.title AS playlist_title
    FROM 
        content
    INNER JOIN 
        instructors ON content.instructor_id = instructors.id
    INNER JOIN 
        playlist ON content.playlist_id = playlist.id
    ORDER BY 
        content.date DESC";

$result_contents = $conn->query($select_contents_query);

if ($result_contents->num_rows > 0) {
    while ($content = $result_contents->fetch_assoc()) {
        $contents[] = $content;
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $content_id = $_GET['delete'];

    // Fetch content details to delete files
    $fetch_content_query = "SELECT * FROM `content` WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($fetch_content_query);
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $content_result = $stmt->get_result();

    if ($content_result->num_rows > 0) {
        $content_data = $content_result->fetch_assoc();

        // Delete thumbnail
        $thumb_path = '../uploaded_files/thumbnails/' . $content_data['thumb'];
        if (file_exists($thumb_path)) {
            unlink($thumb_path);
        }

        // Delete video
        $video_path = '../uploaded_files/videos/' . $content_data['video'];
        if (file_exists($video_path)) {
            unlink($video_path);
        }

        // Delete content from database
        $delete_query = "DELETE FROM `content` WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_query);
        $stmt_delete->bind_param("i", $content_id);
        $stmt_delete->execute();

        header('location: contents.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Content</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="contents-section">
    <h1 class="heading">Manage Contents</h1>

    <div class="box-container">
        <?php if (!empty($contents)) { ?>
            <?php foreach ($contents as $content) { ?>
                <div class="box">
                    <div class="flex">
                        <div>
                            <i class="bx bx-dots-vertical-rounded" style="<?= $content['content_status'] === 'active' ? 'color:limegreen' : 'color:red'; ?>"></i>
                            <span style="<?= $content['content_status'] === 'active' ? 'color:limegreen' : 'color:red'; ?>">
                                <?= htmlspecialchars($content['content_status']); ?>
                            </span>
                        </div>
                        <div>
                            <i class="bx bxs-calendar-alt"></i>
                            <span><?= htmlspecialchars($content['content_date']); ?></span>
                        </div>
                    </div>
                    <img src="../uploaded_files/thumbnails/<?= htmlspecialchars($content['content_thumb']); ?>" class="thumb">
                    <h3 class="title"><?= htmlspecialchars($content['content_title']); ?></h3>
                    <p class="description"><?= htmlspecialchars($content['content_description']); ?></p>
                    <p><strong>Playlist:</strong> <?= htmlspecialchars($content['playlist_title']); ?></p>
                    <p><strong>Instructor:</strong> <?= htmlspecialchars($content['instructor_name']); ?></p>
                    <form action="" method="POST" class="flex-btn">
                        <a href="view_content.php?id=<?= $content['content_id']; ?>" class="btn">View</a>
                        <a href="update_content.php?id=<?= $content['content_id']; ?>" class="btn">Update</a>
                        <a href="contents.php?delete=<?= $content['content_id']; ?>" 
                           class="btn" 
                           onclick="return confirm('Are you sure you want to delete this content?');">Delete</a>
                    </form>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="empty">No content available</p>
        <?php } ?>
    </div>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
