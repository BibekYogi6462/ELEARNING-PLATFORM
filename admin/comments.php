<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Fetch all comments
$comments = [];
$select_comments_query = "SELECT 
    comments.id AS comment_id, 
    comments.comment, 
    comments.date, 
    content.title AS content_title, 
    users.name AS user_name, 
    instructors.name AS instructor_name 
    FROM comments 
    INNER JOIN content ON comments.content_id = content.id 
    INNER JOIN users ON comments.user_id = users.id 
    INNER JOIN instructors ON comments.instructor_id = instructors.id 
    ORDER BY comments.date DESC";

$result_comments = $conn->query($select_comments_query);
if ($result_comments->num_rows > 0) {
    while ($comment = $result_comments->fetch_assoc()) {
        $comments[] = $comment;
    }
}

// Delete comment
if (isset($_GET['delete'])) {
    $comment_id = $_GET['delete'];
    $delete_query = "DELETE FROM `comments` WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param("i", $comment_id);
    if ($stmt_delete->execute()) {
        header('location: comments.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Comments</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="comments-section">
    <h1 class="heading">Manage Comments</h1>

    <div class="box-container">
        <?php if (!empty($comments)) { ?>
            <?php foreach ($comments as $comment) { ?>
                <div class="box">
                    <p><strong>Content:</strong> <?= htmlspecialchars($comment['content_title']); ?></p>
                    <p><strong>User:</strong> <?= htmlspecialchars($comment['user_name']); ?></p>
                    <p><strong>Instructor:</strong> <?= htmlspecialchars($comment['instructor_name']); ?></p>
                    <p><strong>Comment:</strong> <?= htmlspecialchars($comment['comment']); ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($comment['date']); ?></p>
                    <a href="comments.php?delete=<?= $comment['comment_id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this comment?');" 
                       class="delete-btn">Delete</a>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="empty">No comments found</p>
        <?php } ?>
    </div>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
