<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Check if a content ID is provided
if (isset($_GET['id'])) {
    $content_id = $_GET['id'];
} else {
    header('location: contents.php');
    exit;
}

// Fetch content details with instructor and playlist information
$select_content_query = "
    SELECT 
        content.*, 
        instructors.name AS instructor_name, 
        playlist.title AS playlist_title 
    FROM 
        content
    INNER JOIN 
        instructors ON content.instructor_id = instructors.id
    INNER JOIN 
        playlist ON content.playlist_id = playlist.id
    WHERE 
        content.id = ? 
    LIMIT 1";

$stmt = $conn->prepare($select_content_query);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$result_content = $stmt->get_result();

if ($result_content->num_rows > 0) {
    $content_data = $result_content->fetch_assoc();
} else {
    echo '<script>alert("Content not found!"); window.location.href="contents.php";</script>';
    exit;
}

// Handle content deletion
if (isset($_POST['delete_content'])) {
    $delete_query = "DELETE FROM `content` WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param("i", $content_id);
    $stmt_delete->execute();

    echo '<script>alert("Content deleted successfully!"); window.location.href="contents.php";</script>';
    exit;
}

// Fetch comments
$comments = [];
$select_comments_query = "
    SELECT 
        comments.*, 
        users.name AS user_name, 
        users.image AS user_image 
    FROM 
        comments
    LEFT JOIN 
        users ON comments.user_id = users.id
    WHERE 
        comments.content_id = ?";

$stmt_comments = $conn->prepare($select_comments_query);
$stmt_comments->bind_param("i", $content_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

if ($result_comments->num_rows > 0) {
    while ($comment = $result_comments->fetch_assoc()) {
        $comments[] = $comment;
    }
}

// Handle comment deletion
if (isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];

    $delete_comment_query = "DELETE FROM `comments` WHERE id = ?";
    $stmt_delete_comment = $conn->prepare($delete_comment_query);
    $stmt_delete_comment->bind_param("i", $comment_id);
    $stmt_delete_comment->execute();

    echo '<script>alert("Comment deleted successfully!"); window.location.reload();</script>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Content</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="view-content">
    <h1 class="heading">Content Details</h1>
    <div class="container">
        <video src="../uploaded_files/videos/<?= htmlspecialchars($content_data['video']); ?>" controls poster="../uploaded_files/thumbnails/<?= htmlspecialchars($content_data['thumb']); ?>" class="video"></video>
        <h3 class="title"><?= htmlspecialchars($content_data['title']); ?></h3>
        <p class="description"><?= nl2br(htmlspecialchars($content_data['description'])); ?></p>
        <p><strong>Playlist:</strong> <?= htmlspecialchars($content_data['playlist_title']); ?></p>
        <p><strong>Instructor:</strong> <?= htmlspecialchars($content_data['instructor_name']); ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($content_data['status']); ?></p>
        <form action="" method="POST">
            <input type="submit" name="delete_content" value="Delete Content" class="btn" onclick="return confirm('Are you sure you want to delete this content?');">
        </form>
    </div>
</section>

<section class="comments">
    <h1 class="heading">User Comments</h1>
    <div class="show-comments">
        <?php if (!empty($comments)) { ?>
            <?php foreach ($comments as $comment) { ?>
                <div class="box">
                    <div class="user">
                        <img src="../uploaded_files/<?= htmlspecialchars($comment['user_image'] ?: 'default.png'); ?>" alt="User Image">
                        <div>
                            <h3><?= htmlspecialchars($comment['user_name'] ?: 'Anonymous'); ?></h3>
                            <span><?= htmlspecialchars($comment['date']); ?></span>
                        </div>
                    </div>
                    <p class="text"><?= nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    <form action="" method="POST">
                        <input type="hidden" name="comment_id" value="<?= htmlspecialchars($comment['id']); ?>">
                        <button type="submit" name="delete_comment" class="btn" onclick="return confirm('Delete this comment?');">Delete Comment</button>
                    </form>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="empty">No comments available.</p>
        <?php } ?>
    </div>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
