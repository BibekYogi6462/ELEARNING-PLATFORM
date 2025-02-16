<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
} else {
    $get_id = '';
    header('location:index.php');
    exit; // Ensure no further code runs
}

if (!empty($get_id)) {
  // Check if the user ID is empty for guests, or use it for logged-in users
  if (!empty($user_id)) {
    // Insert the view for logged-in users
    $insert_view = $conn->prepare("INSERT INTO `views` (user_id, content_id) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM `views` WHERE user_id = ? AND content_id = ?)");
    $insert_view->bind_param("iiii", $user_id, $get_id, $user_id, $get_id);
    $insert_view->execute();
  } else {
    // Insert the view for guests (no user ID)
    $insert_view = $conn->prepare("INSERT INTO `views` (user_id, content_id) VALUES (NULL, ?)");
    $insert_view->bind_param("i", $get_id);
    $insert_view->execute();
  }
}


//like content

if (isset($_POST['like_content'])) {
    if (!empty($user_id)) { // Ensure user is logged in
        $content_id = $_POST['content_id'];
        $content_id = filter_var($content_id, FILTER_SANITIZE_NUMBER_INT);

        // Fetch content details
        $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
        $select_content->bind_param("i", $content_id);
        $select_content->execute();
        $result_content = $select_content->get_result();

        if ($result_content->num_rows > 0) {
            $fetch_content = $result_content->fetch_assoc();
            $instructor_id = $fetch_content['instructor_id'];

        


            // Check if user already liked the content
            $select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
            $select_likes->bind_param("ii", $user_id, $content_id);
            $select_likes->execute();
            $result_likes = $select_likes->get_result();

            if ($result_likes->num_rows > 0) {
                // User already liked the content - remove like
                $remove_like = $conn->prepare("DELETE FROM `likes` WHERE user_id = ? AND content_id = ?");
                $remove_like->bind_param("ii", $user_id, $content_id);
                $remove_like->execute();

                $message[] = 'Removed from likes.';
            } else {
                // User hasn't liked the content - add like
                $add_like = $conn->prepare("INSERT INTO `likes` (user_id, instructor_id, content_id) VALUES (?, ?, ?)");
                $add_like->bind_param("iii", $user_id, $instructor_id, $content_id);
                $add_like->execute();

                $message[] = 'Added to likes!';
            }
        } else {
            $message[] = 'Content not found.';
        }
    } else {
        $message[] = 'Please log in to like content.';
    }
}

// Add Comment
if (isset($_POST['add_comment'])) {
    if (!empty($user_id)) {
        $comment_box = $_POST['comment_box'];
        $comment_box = filter_var($comment_box, FILTER_SANITIZE_STRING);
        $content_id = $_POST['content_id'];
        $content_id = filter_var($content_id, FILTER_SANITIZE_NUMBER_INT);

        // Fetch content details
        $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
        $select_content->bind_param("i", $content_id);
        $select_content->execute();
        $result_content = $select_content->get_result();

        if ($result_content->num_rows > 0) {
            $fetch_content = $result_content->fetch_assoc();
            $instructor_id = $fetch_content['instructor_id'];

            // Check if comment already exists
            $select_comment = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ? AND user_id = ? AND comment = ?");
            $select_comment->bind_param("iis", $content_id, $user_id, $comment_box);
            $select_comment->execute();
            $result_comment = $select_comment->get_result();

            if ($result_comment->num_rows > 0) {
                $message[] = 'Comment already added.';
            } else {
                // Insert new comment
                $insert_comment = $conn->prepare("INSERT INTO `comments` (content_id, user_id, instructor_id, comment) VALUES (?, ?, ?, ?)");
                $insert_comment->bind_param("iiis", $content_id, $user_id, $instructor_id, $comment_box);
                $insert_comment->execute();
                $message[] = 'New comment added.';
            }
        } else {
            $message[] = 'Content not found.';
        }
    } else {
        $message[] = 'Please login first.';
    }
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
  <title>Gurushishya - Watch Video</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<!-- banner section -->
<div class="banner">
  <div class="detail">
    <div class="title">
      <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>Watch Video</span>
    </div>
    <h1>Watch Video</h1>
    <p>Dive in and learn React.js from scratch and way more things ..</p>

  </div>
</div>


<!-- edit section  -->
 <?php
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


<!-- video section -->
<section class="watch-video">
  <?php
  // Fetch video content
  $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND status = ?");
  $status = 'active';
  $select_content->bind_param("is", $get_id, $status);
  $select_content->execute();
  $result_content = $select_content->get_result();

  if ($result_content->num_rows > 0) {
      $fetch_content = $result_content->fetch_assoc();

      $content_id = $fetch_content['id'];


      $total_views = 0; // Initialize the variable

// Fetch total views
$select_views = $conn->prepare("SELECT COUNT(*) as total_views FROM `views` WHERE content_id = ?");
$select_views->bind_param("i", $content_id);
$select_views->execute();
$result_views = $select_views->get_result();
if ($result_views->num_rows > 0) {
    $total_views = $result_views->fetch_assoc()['total_views'];
}


      // Fetch total likes
      $select_likes = $conn->prepare("SELECT COUNT(*) as total_likes FROM `likes` WHERE content_id = ?");
      $select_likes->bind_param("i", $content_id);
      $select_likes->execute();
      $result_likes = $select_likes->get_result();
      $total_likes = $result_likes->fetch_assoc()['total_likes'];

      // Check if the user liked this content
      $verify_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
      $verify_likes->bind_param("ii", $user_id, $content_id);
      $verify_likes->execute();
      $result_verify_likes = $verify_likes->get_result();

      // Fetch instructor details through the playlist
      $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? LIMIT 1");
      $select_playlist->bind_param("i", $fetch_content['playlist_id']);
      $select_playlist->execute();
      $playlist_result = $select_playlist->get_result();
      $fetch_playlist = $playlist_result->fetch_assoc();

      $instructor_id = $fetch_playlist['instructor_id'];

      $select_instructor = $conn->prepare("SELECT * FROM `instructors` WHERE id = ? LIMIT 1");
      $select_instructor->bind_param("i", $instructor_id);
      $select_instructor->execute();
      $instructor_result = $select_instructor->get_result();
      $fetch_instructor = $instructor_result->fetch_assoc();
  ?>

  <div class="video-details">
      <video src="uploaded_files/videos/<?= $fetch_content['video']; ?>" class="video" poster="uploaded_files/thumbnails/<?= $fetch_content['thumb']; ?>" controls autoplay></video>

      <h3 class="title"><?= htmlspecialchars($fetch_content['title']); ?></h3>
      
      <div class="info">
          <p><i class="bx bxs-calendar-alt"></i><span><?= htmlspecialchars($fetch_content['date']); ?></span></p>
          <p><i class="bx bxs-heart"></i><span><?= $total_likes; ?></span></p>
          <p><i class="bx bxs-binoculars"></i><span><?= $total_views; ?> Views</span></p> <!-- Display total views -->
      </div>
      <div class="tutor">
        <img src="uploaded_files/<?= htmlspecialchars($fetch_instructor['image']); ?>" alt="">
        <div>
          <h3><?= htmlspecialchars($fetch_instructor['name']); ?></h3>
          <span><?= htmlspecialchars($fetch_instructor['specialization']); ?></span>
        </div>
      </div>
      <form action="" method="post" class="flex">
          <input type="hidden" name="content_id" value="<?= $content_id; ?>">
          <a href="playlist.php?get_id=<?= $fetch_content['playlist_id']; ?>" class="btn">View Playlist</a>
          <?php if ($result_verify_likes->num_rows > 0): ?>
              <button type="submit" name="like_content"><i class="bx bxs-heart"></i><span>Liked</span></button>
          <?php else: ?>
              <button type="submit" name="like_content"><i class="bx bxs-heart"></i><span>Like</span></button>
          <?php endif; ?>
      </form>
      <div class="description">
          <p><?= nl2br(htmlspecialchars($fetch_content['description'])); ?></p>
      </div>
  </div>
  <?php
  } else {
      echo '<p class="empty">No videos added yet.</p>';
  }
  ?>
</section>

<!-- comment section  -->
<section class="comments">
  <div class="heading">
    <h1>Add a Comment</h1>
  </div>

  <form action="" method="post" class="add-comment">
    <input type="hidden" name="content_id" value="<?= $get_id; ?>">
    <textarea name="comment_box" required placeholder="Write your comment.." maxlength="1000" cols="30" rows="10"></textarea>
    <input type="submit" value="Add Comment" name="add_comment" class="btn">
  </form>

  <div class="heading">
    <h1>User Comments</h1>
  </div>
  <div class="show-comments">
    <?php
    // Fetch comments for the current content ID
    $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ?");
    $select_comments->bind_param("i", $get_id);
    $select_comments->execute();
    $comments_result = $select_comments->get_result();

    if ($comments_result->num_rows > 0) {
      while ($fetch_comment = $comments_result->fetch_assoc()) {
        // Fetch the commentor's details
        $select_commentor = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
        $select_commentor->bind_param("i", $fetch_comment['user_id']);
        $select_commentor->execute();
        $commentor_result = $select_commentor->get_result();
        $fetch_commentor = $commentor_result->fetch_assoc();
    ?>
        <div class="box" style="<?php if ($fetch_comment['user_id'] == $user_id) echo 'order: -1'; ?>">
          <div class="user">
            <img src="uploaded_files/<?= htmlspecialchars($fetch_commentor['image']); ?>" alt="User Image">
            <div>
              <h3><?= htmlspecialchars($fetch_commentor['name']); ?></h3>
              <span><?= htmlspecialchars($fetch_comment['date']); ?></span>
            </div>
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
