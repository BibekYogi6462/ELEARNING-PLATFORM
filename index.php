<?php
   include 'components/connection.php';

   if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];

   }else{
    $user_id = '';
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
  <title>Gurushishya - Home Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
  <style>

/* Responsive Styles */
@media (max-width: 1200px) {
  .hero .box h1 {
    font-size: 2rem;
  }


}

@media (max-width: 768px) {
  .hero {
    flex-direction: column;
  }

  .hero .box {
    flex: 1 1 100%;
  }

}

@media (max-width: 480px) {

  .benefits .box {
    flex: 1 1 100%;
  }

}

  </style>
</head>

<body>
<?php include 'components/user_header.php'; ?>

<!-- home section  -->
 <div class="hero">
  <div class="box-container">
    <div class="box">
      <img src="image/bannerop.png" alt="">
    </div>
    <div class="box">
      <h1>
      Gurushishya: The Bond of Learning</h1>
      <p>
In the sacred journey of education, the bond between a guru and shishya transcends mere instruction. It's a relationship founded on trust, respect, and mutual growth. Together, they illuminate the path of knowledge, nurturing wisdom through every shared lesson and experience. The true essence of learning lies in this timeless connection.
</p>
<a href="courses.php" class="btn">View Courses</a>
    </div>
  </div>
 </div>


 <!-- categories section  -->
  <div class="categories">
    <div class="heading">
      <span>categories</span>
      <h1>Find top courses <br> that you really need</h1>
    </div>

    <div class="box-container">
      <div class="box">
        <img src="image/web-design.png" alt="">
        <h3>Web Design</h3>
        <!-- <a href="courses.php">2 Courses <i class="bx bx-right-arrow-alt"></i></a> -->
      </div>
      <div class="box">
        <img src="image/design.png" alt="">
        <h3>Graphic Design</h3>
        <!-- <a href="courses.php">2 Courses <i class="bx bx-right-arrow-alt"></i></a> -->
      </div>
      <div class="box">
        <img src="image/personal.png" alt="">
        <h3>Music Tutorial</h3>
        <!-- <a href="courses.php">2 Courses <i class="bx bx-right-arrow-alt"></i></a> -->
      </div>
      <div class="box">
        <img src="image/server.png" alt="">
        <h3>IT and Software</h3>
        <!-- <a href="courses.php">2 Courses <i class="bx bx-right-arrow-alt"></i></a> -->
      </div>



    </div>
  </div>




<!-- Courses Section -->
<div class="courses">
    <div class="heading">
        <span>Top Popular Courses</span>
        <h1>Gurushishya Courses Students Can Join With Us</h1>
    </div>
    <div class="box-container">
        <?php
        // Fetch active courses
        $select_courses = $conn->prepare("SELECT * FROM playlist WHERE status = ? ORDER BY date DESC LIMIT 6");
        $status = 'active';
        $select_courses->bind_param("s", $status);
        $select_courses->execute();
        $result_courses = $select_courses->get_result();

        if ($result_courses->num_rows > 0) {
            while ($fetch_courses = $result_courses->fetch_assoc()) {
                $course_id = $fetch_courses['id'];

                // Fetch instructor data
                $select_instructor = $conn->prepare("SELECT * FROM instructors WHERE id = ?");
                $select_instructor->bind_param("i", $fetch_courses['instructor_id']);
                $select_instructor->execute();
                $result_instructor = $select_instructor->get_result();
                $fetch_instructor = $result_instructor->fetch_assoc();

                $instructor_image = !empty($fetch_instructor['image']) ? $fetch_instructor['image'] : 'default_instructor.png';
                ?>
                <div class="box">
                    <div class="tutor">
                        <img src="uploaded_files/<?= htmlspecialchars($instructor_image); ?>" alt="Instructor">
                        <div>
                        <h3><?= htmlspecialchars($fetch_instructor['name']); ?></h3>
                        <span><?= htmlspecialchars($fetch_courses['date']); ?></span>
                    </div>
                    </div>
               
                    <img src="uploaded_files/<?= $fetch_courses['thumb']; ?>" alt="Course Thumbnail" class="thumb">
                    <h3 class="title"><?= htmlspecialchars($fetch_courses['title']); ?></h3>
                    <a href="playlist.php?get_id=<?= $course_id; ?>" class="btn">View Playlist</a>
                </div>
                <?php
            }
        } else {
            echo '<p class="empty">No courses added yet.</p>';
        }
        ?>
    </div>
    <div class="more-btn">
      <a href="courses.php" class="btn">View More</a>

    </div>
</div>


<!-- benefits section  -->
<div class="benefits">
  <img src="image/map.png" alt="" class="map">
  <div class="detail">
    <h1>Globally Trusted By hundreds of instructors <br> thousands of learners</h1>
    <p>Work Smarter ☕ Create Better + Build Faster</p>
    <a href="courses.php" class="btn">Explore Courses Now</a>
    <p>How will GuruShihsya BENEFIT YOU?</p>
    <div class="box-container">
      <div class="box">
        <img src="image/benefit-01.png" alt="">
        <p>Free <br> Courses </p>
      </div>
      <div class="box">
        <img src="image/benefit-02.png" alt="">
        <p>  Fast Feedback<br> Always</p>
      </div>
      <div class="box">
        <img src="image/benefit-03.png" alt="">
        <p>High Speed <br> Performance </p>
      </div>
      <div class="box">
        <img src="image/benefit-04.png" alt="">
        <p>We provide premium <br>Courses </p>
      </div>
      <div class="box">
        <img src="image/benefit-05.png" alt="">
        <p>User Friendly and<br> Easy Access</p>
      </div>
    </div>
  </div>
</div>








<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>