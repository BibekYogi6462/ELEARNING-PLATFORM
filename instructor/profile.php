<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    $instructor_id = '';
    header('location: login.php');
    exit;
}

// Count total contents
$select_contents = $conn->prepare("SELECT * FROM `content` WHERE instructor_id = ?");
$select_contents->bind_param("s", $instructor_id); // Bind the instructor_id
$select_contents->execute();
$select_contents_result = $select_contents->get_result();
$total_contents = $select_contents_result->num_rows;

// Count total playlists
$select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE instructor_id = ?");
$select_playlists->bind_param("s", $instructor_id);
$select_playlists->execute();
$select_playlists_result = $select_playlists->get_result();
$total_playlists = $select_playlists_result->num_rows;

// Count total likes
$select_likes = $conn->prepare("SELECT * FROM `likes` WHERE instructor_id = ?");
$select_likes->bind_param("s", $instructor_id);
$select_likes->execute();
$select_likes_result = $select_likes->get_result();
$total_likes = $select_likes_result->num_rows;

// Count total comments
$select_comments = $conn->prepare("SELECT * FROM `comments` WHERE instructor_id = ?");
$select_comments->bind_param("s", $instructor_id);
$select_comments->execute();
$select_comments_result = $select_comments->get_result();
$total_comments = $select_comments_result->num_rows;

?>

<style>
  <?php include '../css/admin.css'; ?>
</style>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tutor Profile</title>
  <!-- Boxicon link  -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <!-- css link  -->
   <link rel="stylesheet" href="../css/admin.css">

</head>
<body>
  <?php include '../components/admin_header.php';?>
  <section class="tutor-profile" style="min-height: calc(100vh-19rem);">
     <h1 class="heading">Profile Details</h1>
    <div class="details">
      <div class="tutor">
        <img src="../uploaded_files/<?= $fetch_profile['image']; ?>">
        <h3><?= $fetch_profile['name']; ?></h3>
        <span><?= $fetch_profile['specialization']; ?></span>
        <a href="update.php" class="btn" >Update Profile</a>
      </div>
      <div class="flex">
        <div class="box">
          <span><?= $total_playlists; ?></span>
          <p>Total Playlists</p>
          <a href="playlists.php" class="btn">View Playlists</a>
        </div>
        <div class="box">
          <span><?= $total_contents; ?></span>
          <p>Total Videos</p>
          <a href="contents.php" class="btn">View Contents</a>
        </div>
        <div class="box">
          <span><?= $total_likes; ?></span>
          <p>Total Likes</p>
          <a href="contents.php" class="btn">View Contents</a>
        </div>
        <div class="box">
          <span><?= $total_comments; ?></span>
          <p>Total Comments</p>
          <a href="comments.php" class="btn">View Comments</a>
        </div>
      </div>
    </div>
     
  </section>
  <?php include '../components/footer.php';?>
  <script src="../js/admin.js"></script>
</body>
</html>