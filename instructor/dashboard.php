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
  <title>Dashboard</title>
  <!-- Boxicon link  -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <!-- css link  -->
   <link rel="stylesheet" href="../css/admin.css">

</head>
<body>
  <?php include '../components/admin_header.php';?>
  <section class="dashboard">
     <h1 class="heading">Dashboard</h1>
     <div class="box-container">
      <div class="box">
        <h3>Welcome!</h3>
        <p><?= $fetch_profile['name']; ?></p>
        <a href="profile.php" class="btn">View</a>
      </div>
      <div class="box">
        <h3><?= $total_contents; ?></h3>
        <p>Total Contents</p>
        <a href="add_content.php" class="btn">Add New Content</a>
      </div>
      <div class="box">
        <h3><?= $total_playlists; ?></h3>

        <p>Total Playlists</p>
        <a href="add_playlist.php" class="btn">Add New Playlist</a>
      </div>
      <div class="box">
        <h3><?= $total_likes; ?></h3>

        <p>Total likes</p>
        <a href="contents.php" class="btn">View Content</a>
      </div>
      <div class="box">
        <h3><?= $total_comments; ?></h3>

        <p>Total Comments</p>
        <a href="comments.php" class="btn">View Comments</a>
      </div>
      <!-- <div class="box">
        <h3>Quick Start</h3>
        <div class="flex-btn">
          <a href="login.php" class="btn" style="width:200px;">Login Now</a>
          <a href="register.php" class="btn" style="width:200px;">Register Now</a>
</div> -->
      </div>
     
     </div>
  </section>
  <?php include '../components/footer.php';?>
  <script src="../js/admin.js"></script>
</body>
</html>