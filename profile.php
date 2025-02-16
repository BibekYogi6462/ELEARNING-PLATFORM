<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:login.php');
}

 
$select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ?");
$select_likes->bind_param("s", $user_id);
$select_likes->execute();
$select_likes_result = $select_likes->get_result();
$total_likes = $select_likes_result->num_rows;

// Fetch total comments
$select_comments = $conn->prepare("SELECT * FROM `comments` WHERE user_id = ?");
$select_comments->bind_param("s", $user_id);
$select_comments->execute();
$select_comments_result = $select_comments->get_result();
$total_comments = $select_comments_result->num_rows;

// Fetch total bookmarked
$select_bookmark = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ?");
$select_bookmark->bind_param("s", $user_id);
$select_bookmark->execute();
$select_bookmark_result = $select_bookmark->get_result();
$total_bookmarked = $select_bookmark_result->num_rows;




?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Registration Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

   <!-- banner section  -->
    <div class="banner">
      <div class="detail">
        <div class="title">
          <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>Profile</span>
        </div>
        <h1>My Profile</h1>
        <p>Dive in and learn React.js from scratch and way more things ..

        </p>
        <div class="flex-btn">
          <a href="login.php" class="btn">Login To Start</a>
          <a href="contact.php" class="btn">Contact us</a>
        </div>
      </div>
      <img src="image/about.png" alt="" class="aboutimg">
    </div>


<section class="profile">
  <div class="heading">
    <h1>Profile Details</h1>
  </div>
  <div class="details">
    <div class="user">
    <img src="uploaded_files/<?= $fetch_profile['image']; ?>" alt="">
    <h3><?= $fetch_profile['name']; ?></h3>
    <p>Student</p>
    <a href="update.php" class="btn">Update Profile</a>
</div> 


  <div class="box-container">
    <div class="box">
      <div class="flex">
        <i class="bx bxs-bookmarks"></i>
        <h3><?= $total_bookmarked; ?></h3>
        <span>saved playlists</span>
      </div>
      <a href="bookmark.php" class="btn">view playlist</a>
    </div>

    <div class="box">
      <div class="flex">
        <i class="bx bxs-heart"></i>
        <h3><?= $total_likes; ?></h3>
        <span>liked videos</span>
      </div>
      <a href="likes.php" class="btn">view likes</a>
    </div>

    <div class="box">
      <div class="flex">
        <i class="bx bxs-chat"></i>
        <h3><?= $total_comments; ?></h3>
        <span>video comments</span>
      </div>
      <a href="comments.php" class="btn">view comments</a>
    </div>
  </div>
</div>
</section>





<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>