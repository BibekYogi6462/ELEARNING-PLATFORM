<?php
include '../components/connection.php';

// Check instructor login
if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    header('location: login.php');
    exit;
}

// Handle comment deletion
if (isset($_POST['delete_comment'])) {
    $delete_id = filter_var($_POST['comment_id'], FILTER_SANITIZE_STRING);

    // Verify if the comment exists
    $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ?");
    $verify_comment->bind_param("i", $delete_id);
    $verify_comment->execute();
    $verify_comment_result = $verify_comment->get_result();

    if ($verify_comment_result->num_rows > 0) {
        // If the comment exists, delete it
        $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
        $delete_comment->bind_param("i", $delete_id);
        $delete_comment->execute();
        $message[] = 'Comment deleted successfully!';
    } else {
        $message[] = 'Comment already deleted or not found!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Comments</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="comments">
    <h1 class="heading">User Comments</h1>
    <div class="show-comments">
        <?php
        $select_comments = $conn->prepare('SELECT * FROM `comments` WHERE instructor_id = ?');
        $select_comments->bind_param("i", $instructor_id);
        $select_comments->execute();
        $comments_result = $select_comments->get_result();

        if ($comments_result->num_rows > 0) {
            while ($fetch_comment = $comments_result->fetch_assoc()) {
                $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ?");
                $select_content->bind_param("i", $fetch_comment['content_id']);
                $select_content->execute();
                $content_result = $select_content->get_result();

                if ($content_result->num_rows > 0) {
                    $fetch_content = $content_result->fetch_assoc();
                    ?>
                    <div class="box" style="<?php if ($fetch_comment['instructor_id'] == $instructor_id) { echo 'order:-1'; } ?>">
                        <div class="content">
                            <span><?= htmlspecialchars($fetch_comment['date']); ?></span>
                            <p>- <?= htmlspecialchars($fetch_content['title']); ?> -</p>
                            <a href="view_content.php?get_id=<?= htmlspecialchars($fetch_content['id']); ?>">View Content</a>
                        </div>
                        <p class="text"><?= htmlspecialchars($fetch_comment['comment']); ?></p>
                        <form action="" method="POST">
                            <input type="hidden" name="comment_id" value="<?= htmlspecialchars($fetch_comment['id']); ?>">
                            <button type="submit" name="delete_comment" class="btn" onclick="return confirm('Delete this comment?');">
                                Delete Comment
                            </button>
                        </form>
                    </div>
                    <?php
                }
            }
        } else {
            echo '<p class="empty">No comments added yet!</p>';
        }
        ?>
    </div>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
