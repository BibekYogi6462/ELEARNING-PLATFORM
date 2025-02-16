<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Teacher Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

   <!-- banner section  -->
    <div class="banner">
      <div class="detail">
        <div class="title">
          <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>Search Teachers</span>
        </div>
        <h1>Search teachers</h1>
        <p>Dive in and learn React.js from scratch and way more things ..

        </p>
        <div class="flex-btn">
          <a href="login.php" class="btn">Login To Start</a>
          <a href="contact.php" class="btn">Contact us</a>
        </div>
      </div>
      <img src="image/about.png" alt="" class="aboutimg">
    </div>


    <!-- registration section  -->
     
<section class="teachers">
  <div class="heading">
    <h1>expert tutors</h1>
  </div>

  <form action="" method="POST" class="search-tutor">
    <input type="text" name="search_tutor" id="" maxlength="100" required placeholder="search tutor">
    <button type="submit" name="search_tutor_btn" class="bx bx-search-alt-2"></button>
  </form>
  <div class="box-container">
    <?php  
    if (isset($_POST['search_tutor']) || isset($_POST['search_tutor_btn'])) {
        $search_tutor = filter_var($_POST['search_tutor'], FILTER_SANITIZE_STRING);

        // Prepare the query to search for instructors
        $select_instructor = $conn->prepare("SELECT * FROM `instructors` WHERE `name` LIKE ?");
        $search_term = "%{$search_tutor}%";
        $select_instructor->bind_param("s", $search_term);
        $select_instructor->execute();
        $result_instructor = $select_instructor->get_result();

        if ($result_instructor->num_rows > 0) {
            while ($fetch_instructor = $result_instructor->fetch_assoc()) {
    $instructor_id = $fetch_instructor['id'];
                // Fetch instructor statistics
                $total_playlists = $conn->prepare("SELECT COUNT(*) AS count FROM playlist WHERE instructor_id = ?");
                $total_playlists->bind_param("i", $instructor_id);
                $total_playlists->execute();
                $playlists_result = $total_playlists->get_result()->fetch_assoc()['count'];
    
                $total_contents = $conn->prepare("SELECT COUNT(*) AS count FROM content WHERE instructor_id = ?");
                $total_contents->bind_param("i", $instructor_id);
                $total_contents->execute();
                $contents_result = $total_contents->get_result()->fetch_assoc()['count'];
    
                $total_likes = $conn->prepare("SELECT COUNT(*) AS count FROM likes WHERE instructor_id = ?");
                $total_likes->bind_param("i", $instructor_id);
                $total_likes->execute();
                $likes_result = $total_likes->get_result()->fetch_assoc()['count'];
    
                $total_comments = $conn->prepare("SELECT COUNT(*) AS count FROM comments WHERE instructor_id = ?");
                $total_comments->bind_param("i", $instructor_id);
                $total_comments->execute();
                $comments_result = $total_comments->get_result()->fetch_assoc()['count'];

                ?>
  <div class="box">
    <div class="tutor">
        <?php 
        // Use the instructor's image if it exists; otherwise, use a default image
        $instructor_image = !empty($fetch_instructor['image']) ? $fetch_instructor['image'] : 'default_instructor.png'; 
        ?>
        <img src="uploaded_files/<?= htmlspecialchars($instructor_image); ?>" alt="Instructor Image">
        <div>
            <h3><?= htmlspecialchars($fetch_instructor['name']); ?></h3>
            <span><?= htmlspecialchars($fetch_instructor['specialization']); ?></span>
        </div>
    </div>
    <p>Playlists: <span><?= $playlists_result; ?></span></p>
    <p>Total Videos: <span><?= $contents_result; ?></span></p>
    <p>Total Likes: <span><?= $likes_result; ?></span></p>
    <p>Total Comments: <span><?= $comments_result; ?></span></p>
    <form action="tutor_profile.php" method="POST">
        <input type="hidden" name="tutor_email" value="<?= htmlspecialchars($fetch_instructor['email']); ?>">
        <input type="submit" name="tutor_fetch" value="View Profile" class="btn">
    </form>
</div>

<?php
           }
          }else {
            echo '<p class="empty">No result found.</p>';
        }
         }else {
          echo '<p class="empty">search something else!.</p>';
      }

    ?> 
  </div>
</section>







<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>