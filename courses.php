<?php
   include 'components/connection.php';

   if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];

   }else{
    $user_id = '';
   }


?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Course Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

   <!-- banner section  -->
    <div class="banner">
      <div class="detail">
        <div class="title">
          <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>Courses</span>
        </div>
        <h1>Our Courses</h1>
        <p>Dive in and learn React.js from scratch and way more things ..

        </p>
    
      </div>
    </div>


    <!-- course section  -->
     

<!-- Courses Section -->
<div class="courses">
    <div class="heading">
        <span>Our Courses</span>
        <!-- <h1>Gurushishya Courses Students Can Join With Us</h1> -->
    </div>
    <div class="box-container">
        <?php
        // Fetch active courses
        $select_courses = $conn->prepare("SELECT * FROM playlist WHERE status = ? ORDER BY date DESC");
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
  
</div>







<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>