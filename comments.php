<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}



// Delete Comment
if (isset($_POST['delete_comment'])) {
  $delete_id = $_POST['comment_id'];
  $delete_id = filter_var($delete_id, FILTER_SANITIZE_NUMBER_INT);

  // Verify comment exists
  $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? LIMIT 1");
  $verify_comment->bind_param("i", $delete_id);
  $verify_comment->execute();
  $result_verify = $verify_comment->get_result();

  if ($result_verify->num_rows > 0) {
      // Delete comment
      $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
      $delete_comment->bind_param("i", $delete_id);
      $delete_comment->execute();
      $message[] = 'Comment deleted successfully.';
  } else {
      $message[] = 'Comment already deleted.';
  }
}

// Update Comment
if (isset($_POST['update_now'])) {
  $update_id = $_POST['update_id'];
  $update_id = filter_var($update_id, FILTER_SANITIZE_NUMBER_INT);

  $update_box = $_POST['update_box'];
  $update_box = filter_var($update_box, FILTER_SANITIZE_STRING);

  // Verify if the comment already exists with the same content
  $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? AND comment = ?");
  $verify_comment->bind_param("is", $update_id, $update_box);
  $verify_comment->execute();
  $result_verify = $verify_comment->get_result();

  if ($result_verify->num_rows > 0) {
      $message[] = 'Comment already exists with the same content.';
  } else {
      // Update comment
      $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ?");
      $update_comment->bind_param("si", $update_box, $update_id);
      $update_comment->execute();
      $message[] = 'Comment updated successfully.';
  }
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - User Comments Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<!-- banner section -->
<div class="banner">
    <div class="detail">
        <div class="title">
            <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>your comments</span>
        </div>
        <h1>Your Comments</h1>
        <p>Dive in and learn React.js from scratch and way more things ..</p>
        <div class="flex-btn">
            <a href="login.php" class="btn">Login To Start</a>
            <a href="contact.php" class="btn">Contact us</a>
        </div>
    </div>
    <img src="image/about.png" alt="" class="aboutimg">
</div>
<!-- edit comment  -->
<<?php
if (isset($_POST['edit_comment'])) {
    $edit_id = $_POST['comment_id'];
    $edit_id = filter_var($edit_id, FILTER_SANITIZE_NUMBER_INT);

    // Fetch comment to edit
    $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? LIMIT 1");
    $verify_comment->bind_param("i", $edit_id);
    $verify_comment->execute();
    $result_verify = $verify_comment->get_result();

    if ($result_verify->num_rows > 0) {
        $fetch_edit_comment = $result_verify->fetch_assoc();
?>
<section class="edit_comment">
    <div class="heading">
        <h1>Edit Comment</h1>
    </div>
    <form action="" method="POST">
        <input type="hidden" name="update_id" value="<?= $fetch_edit_comment['id']; ?>">
        <textarea name="update_box" class="box" maxlength="1000" required cols="30" rows="10"><?= htmlspecialchars($fetch_edit_comment['comment']); ?></textarea>
        <div class="flex-btn">
            <a href="watch_video.php?get_id=<?= $get_id; ?>" class="btn">Cancel Edit</a>
            <input type="submit" name="update_now" class="btn" value="Update Now">
        </div>
    </form>
</section>

<?php
    } else {
        $message[] = 'Comment not found.';
    }
}
?>
<!-- comment section -->
<section class="comments">
    <div class="heading">
        <h1>Your Comments</h1>
    </div>

    <div class="show-comments">
        <?php
        // Fetch comments for the current user
        $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE user_id = ?");
        $select_comments->bind_param("i", $user_id);
        $select_comments->execute();
        $comments_result = $select_comments->get_result();

        if ($comments_result->num_rows > 0) {
            while ($fetch_comment = $comments_result->fetch_assoc()) {
                // Fetch the related content details
                $content_id = $fetch_comment['content_id'];
                $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ?");
                $select_content->bind_param("i", $content_id);
                $select_content->execute();
                $content_result = $select_content->get_result();

                if ($content_result->num_rows > 0) {
                    $fetch_content = $content_result->fetch_assoc();
                } else {
                    $fetch_content = ['title' => 'Unknown Content', 'id' => 0];
                }
        ?>
        <div class="box" style="<?= $fetch_comment['user_id'] == $user_id ? 'order: -1' : ''; ?>">
            <div class="content">
                <span><?= htmlspecialchars($fetch_comment['date']); ?></span>
                <p><?= htmlspecialchars($fetch_content['title']); ?></p>
                <?php if ($fetch_content['id'] != 0) { ?>
                    <a href="watch_video.php?get_id=<?= $fetch_content['id']; ?>">View Content</a>
                <?php } ?>
            </div>
            <p class="text"><?= nl2br(htmlspecialchars($fetch_comment['comment'])); ?></p>

            <?php if ($fetch_comment['user_id'] == $user_id) { ?>
                <form action="" method="POST" class="flex-btn">
                    <input type="hidden" name="comment_id" value="<?= $fetch_comment['id']; ?>">
                    <button type="submit" name="edit_comment" class="btn">Edit Comment</button>
                    <button type="submit" name="delete_comment" class="btn" onclick="return confirm('Delete this comment?');">Delete Comment</button>
                </form>
            <?php } ?>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No comments added yet.</p>';
        }
        ?>
    </div>
</section>

<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>
