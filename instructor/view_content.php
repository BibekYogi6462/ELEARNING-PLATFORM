<?php
include '../components/connection.php';

// Check if the user is logged in (Instructor or Learner)
if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
    $user_id = $instructor_id; // Set the user_id as the instructor_id
    $user_type = 'instructor';
} elseif (isset($_COOKIE['learner_id'])) {
    $user_id = $_COOKIE['learner_id'];
    $user_type = 'learner';
} else {
    $user_id = null; // Guest users
    $user_type = 'guest';
}


// Get content ID
if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
} else {
    header('location: contents.php');
    exit;
}

// Fetch content details
$select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ?");
$select_content->bind_param("i", $get_id);
$select_content->execute();
$result_content = $select_content->get_result();

if ($result_content->num_rows > 0) {
    $fetch_content = $result_content->fetch_assoc();

    // Track View in the views table if the user has not already viewed this content
// Track View in the views table
if ($user_id) {
    if ($user_type == 'instructor') {
        // For instructors, track by instructor_id
        $check_view = $conn->prepare("SELECT * FROM `views` WHERE `instructor_id` = ? AND `content_id` = ?");
        $check_view->bind_param("ii", $instructor_id, $get_id);
        $check_view->execute();
    } else {
        // For learners, track by user_id
        $check_view = $conn->prepare("SELECT * FROM `views` WHERE `user_id` = ? AND `content_id` = ?");
        $check_view->bind_param("ii", $user_id, $get_id);
        $check_view->execute();
    }
    $view_result = $check_view->get_result();
    if ($view_result->num_rows === 0) {
        // Insert view record for the user (either instructor or learner)
        if ($user_type == 'instructor') {
            $insert_view = $conn->prepare("INSERT INTO `views` (`instructor_id`, `content_id`) VALUES (?, ?)");
            $insert_view->bind_param("ii", $instructor_id, $get_id);
        } else {
            $insert_view = $conn->prepare("INSERT INTO `views` (`user_id`, `content_id`) VALUES (?, ?)");
            $insert_view->bind_param("ii", $user_id, $get_id);
        }
        $insert_view->execute();
    }
} else {
    // For guest users
    $check_view = $conn->prepare("SELECT * FROM `views` WHERE `user_id` IS NULL AND `content_id` = ?");
    $check_view->bind_param("i", $get_id);
    $check_view->execute();
    $view_result = $check_view->get_result();
    if ($view_result->num_rows === 0) {
        // Insert view record for the guest user
        $insert_view = $conn->prepare("INSERT INTO `views` (`user_id`, `content_id`) VALUES (NULL, ?)");
        $insert_view->bind_param("i", $get_id);
        $insert_view->execute();
    }
}

// Handle video deletion
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
      $delete_likes->bind_param("i", $delete_id);
      $delete_likes->execute();

      $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
      $delete_comments->bind_param("i", $delete_id);
      $delete_comments->execute();

      $delete_views = $conn->prepare("DELETE FROM `views` WHERE content_id = ?");
      $delete_views->bind_param("i", $delete_id);
      $delete_views->execute();

      $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ?");
      $delete_content->bind_param("i", $delete_id);
      $delete_content->execute();

      $message[] = 'Video deleted successfully!';
  } else {
      $message[] = 'Video not found or already deleted!';
  }
  header("Location: view_playlist.php?get_id=" . $get_id);
  exit;
}
}


// Handle comment deletion
if (isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];
    $comment_id = filter_var($comment_id, FILTER_SANITIZE_STRING);

    $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
    $delete_comment->bind_param("i", $comment_id);
    $delete_comment->execute();

    $message[] = 'Comment deleted successfully!';
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
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="view-content">
    <h1 class="heading">Content Details</h1>
    <div class="container">
        <video src="../uploaded_files/videos/<?= htmlspecialchars($fetch_content['video']); ?>" 
               autoplay controls 
               poster="../uploaded_files/thumbnails/<?= htmlspecialchars($fetch_content['thumb']); ?>" class="video">
        </video>
        <div class="date"><i class="bx bxs-calendar-alt"></i> <span><?= htmlspecialchars($fetch_content['date']); ?></span></div>
        <h3 class="title"><?= htmlspecialchars($fetch_content['title']); ?></h3>
        <div class="flex">
            <div>
                <i class="bx bxs-heart"></i>
                <span>
                    <?php
                    $likes_count = $conn->prepare("SELECT COUNT(*) AS total FROM `likes` WHERE content_id = ?");
                    $likes_count->bind_param("i", $get_id);
                    $likes_count->execute();
                    $likes = $likes_count->get_result()->fetch_assoc();
                    echo $likes['total']. ' Likes';
                    ?>
                </span>
            </div>
            <div>
                <i class="bx bxs-chat"></i>
                <span>
                    <?php
                    $comments_count = $conn->prepare("SELECT COUNT(*) AS total FROM `comments` WHERE content_id = ?");
                    $comments_count->bind_param("i", $get_id);
                    $comments_count->execute();
                    $comments = $comments_count->get_result()->fetch_assoc();
                    echo $comments['total'] . ' Comments';
                    ?>
                </span>
            </div>
            <div>
                <i class="bx bxs-binoculars"></i>
                <span>
                    <?php
                    // Display view count
                    $views_count = $conn->prepare("SELECT COUNT(*) AS total FROM `views` WHERE content_id = ?");
                    $views_count->bind_param("i", $get_id);
                    $views_count->execute();
                    $views = $views_count->get_result()->fetch_assoc();


                    echo $views['total'] . ' Views';

                    ?>
                </span>
            </div>
        </div>
        <div class="description">
            <?= nl2br(htmlspecialchars($fetch_content['description'])); ?>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="video_id" value="<?= $get_id; ?>">
            <a href="update_content.php?get_id=<?= $get_id; ?>" class="btn">Update</a>
            <input type="submit" name="delete_video" value="Delete Video" class="btn" onclick="return confirm('Delete this video?');">
        </form>
    </div>
</section>
<section class="comments">
    <h1 class="heading">User Comments</h1>
    <div class="show-comments">
        <?php
        $select_comments = $conn->prepare("SELECT comments.*, users.name AS user_name, users.image AS user_image
                                          FROM comments
                                          LEFT JOIN users ON comments.user_id = users.id
                                          WHERE comments.content_id = ?");
        $select_comments->bind_param("i", $get_id);
        $select_comments->execute();
        $comments_result = $select_comments->get_result();

        if ($comments_result->num_rows > 0) {
            while ($fetch_comment = $comments_result->fetch_assoc()) {
                ?>
                <div class="box">
                    <div class="user">
                        <img src="../uploaded_files/<?= htmlspecialchars($fetch_comment['user_image'] ?: 'default.png'); ?>" alt="User Image">
                        <div>
                            <h3><?= htmlspecialchars($fetch_comment['user_name'] ?: 'Anonymous'); ?></h3>
                            <span><?= htmlspecialchars($fetch_comment['date']); ?></span>
                        </div>
                    </div>
                    <p class="text"><?= nl2br(htmlspecialchars($fetch_comment['comment'])); ?></p>
                    <form action="" method="POST">
                        <input type="hidden" name="comment_id" value="<?= htmlspecialchars($fetch_comment['id']); ?>">
                        <button type="submit" name="delete_comment" class="btn" onclick="return confirm('Delete this comment?');">Delete Comment</button>
                    </form>
                </div>
                <?php
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
