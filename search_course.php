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
  <title>Gurushishya - Search Course Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<!-- banner section -->
<div class="banner">
    <div class="detail">
        <div class="title">
            <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>Search Course</span>
        </div>
        <h1>Search Course</h1>
        <p>Dive in and learn React.js from scratch and way more things ..</p>
        <div class="flex-btn">
            <a href="login.php" class="btn">Login To Start</a>
            <a href="contact.php" class="btn">Contact us</a>
        </div>
    </div>
    <img src="image/about.png" alt="" class="aboutimg">
</div>

<!-- courses section -->
<section class="courses">
    <div class="heading">
        <h1>search result</h1>
    </div>

    <div class="box-container">
        <?php
        if (isset($_POST['search_course']) || isset($_POST['search_course_btn'])) {
            $search_course = filter_var($_POST['search_course'], FILTER_SANITIZE_STRING);
            $search_term = "%{$search_course}%";

            $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE `title` LIKE ? AND `status` = ?");
            $status = 'active';
            $select_courses->bind_param("ss", $search_term, $status);
            $select_courses->execute();
            $result_courses = $select_courses->get_result();

            if ($result_courses->num_rows > 0) {
                while ($fetch_courses = $result_courses->fetch_assoc()) {
                    $course_id = $fetch_courses['id'];
                    $instructor_id = $fetch_courses['instructor_id'];

                    // Fetch instructor data
                    $select_instructor = $conn->prepare("SELECT * FROM `instructors` WHERE `id` = ?");
                    $select_instructor->bind_param("i", $instructor_id);
                    $select_instructor->execute();
                    $result_instructor = $select_instructor->get_result();
                    $fetch_instructor = $result_instructor->fetch_assoc();

                    // Set instructor image
                    $instructor_image = !empty($fetch_instructor['image']) ? $fetch_instructor['image'] : 'default_instructor.png';
        ?>
        <div class="box">
            <div class="tutor">
                <img src="uploaded_files/<?= htmlspecialchars($instructor_image); ?>" alt="Instructor Image">
                <div>
                    <h3><?= htmlspecialchars($fetch_instructor['name']); ?></h3>
                    <span><?= htmlspecialchars($fetch_courses['date']); ?></span>
                </div>
            </div>
            <img src="uploaded_files/<?= htmlspecialchars($fetch_courses['thumb']); ?>" alt="Course Thumbnail" class="thumb">
            <h3 class="title"><?= htmlspecialchars($fetch_courses['title']); ?></h3>
            <a href="playlist.php?get_id=<?= $course_id; ?>" class="btn">View Playlist</a>
        </div>
        <?php
                }
            } else {
                echo '<p class="empty">No courses found.</p>';
            }
        } else {
            echo '<p class="empty">Please search for something!</p>';
        }
        ?>
    </div>
</section>

<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>
