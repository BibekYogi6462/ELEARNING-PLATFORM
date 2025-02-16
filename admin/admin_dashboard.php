<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Count total instructors
$select_instructors_query = "SELECT * FROM `instructors`";
$select_instructors_result = mysqli_query($conn, $select_instructors_query);
$total_instructors = mysqli_num_rows($select_instructors_result);

// Count total students
$select_students_query = "SELECT * FROM `users`";
$select_students_result = mysqli_query($conn, $select_students_query);
$total_students = mysqli_num_rows($select_students_result);

// Count total playlists
$select_playlists_query = "SELECT * FROM `playlist`";
$select_playlists_result = mysqli_query($conn, $select_playlists_query);
$total_playlists = mysqli_num_rows($select_playlists_result);

// Count total content
$select_content_query = "SELECT * FROM `content`";
$select_content_result = mysqli_query($conn, $select_content_query);
$total_content = mysqli_num_rows($select_content_result);

// Count total messages
$select_messages_query = "SELECT * FROM `contact`";
$select_messages_result = mysqli_query($conn, $select_messages_query);
$total_messages = mysqli_num_rows($select_messages_result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <!-- Boxicon link -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <!-- CSS link -->
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <?php include '../components/admin2header.php'; ?>
  <section class="dashboard">
     <h1 class="heading">Admin Dashboard</h1>
     <div class="box-container">
      <div class="box">
        <h3>Welcome Admin!</h3>
        <p>Manage the platform effectively</p>
        <a href="adminprofile.php" class="btn">Update Profile</a>
      </div>
      <div class="box">
        <h3><?= $total_instructors; ?></h3>
        <p>Total Instructors</p>
        <a href="manage_instructors.php" class="btn">Manage Instructors</a>
      </div>
      <div class="box">
        <h3><?= $total_students; ?></h3>
        <p>Total Students</p>
        <a href="manage_students.php" class="btn">Manage Students</a>
      </div>
      <div class="box">
        <h3><?= $total_playlists; ?></h3>
        <p>Total Playlists</p>
        <a href="playlists.php" class="btn">View Playlists</a>
      </div>
      <div class="box">
        <h3><?= $total_content; ?></h3>
        <p>Total Content</p>
        <a href="contents.php" class="btn">View Contents</a>
      </div>
      <div class="box">
        <h3><?= $total_messages; ?></h3>
        <p>Total Messages</p>
        <a href="view_message.php" class="btn">View Messages</a>
      </div>
     </div>
  </section>
  <?php include '../components/footer.php'; ?>
  <script src="../js/admin.js"></script>
</body>
</html>
