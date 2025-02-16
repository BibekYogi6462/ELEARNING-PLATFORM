<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    $instructor_id = '';
    header('location: login.php');
    exit;
}

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $title = filter_var($title, FILTER_SANITIZE_STRING);

    $description = $_POST['description'];
    $description = filter_var($description, FILTER_SANITIZE_STRING);

    $status = $_POST['status'];
    $status = filter_var($status, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename = time() . '.' . $ext; // Rename file using a timestamp
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_files/' . $rename;

    // Check if a playlist with the same title already exists
    $check_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE instructor_id = ? AND title = ?");
    $check_playlist->bind_param("is", $instructor_id, $title);
    $check_playlist->execute();
    $check_playlist_result = $check_playlist->get_result();

    if ($check_playlist_result->num_rows > 0) {
        $message[] = 'Playlist title already exists. Please choose a different title.';
    } else {
        if ($image_size > 2000000) {
            $message[] = 'Image size too large';
        } else {
            // Prepare the MySQLi statement
            $add_playlist = $conn->prepare("INSERT INTO `playlist` (instructor_id, title, description, thumb, status) VALUES (?, ?, ?, ?, ?)");
            $add_playlist->bind_param("issss", $instructor_id, $title, $description, $rename, $status);

            // Execute the statement
            if ($add_playlist->execute()) {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'New playlist created';
            } else {
                $message[] = 'Failed to create playlist: ' . $conn->error;
            }

            // Close the prepared statement
            $add_playlist->close();
        }
    }

    // Close the check playlist statement
    $check_playlist->close();
}

?>


<style>
  <?php include '../css/admin.css'; ?>
</style>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add PLaylist</title>
  <!-- Boxicon link  -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <!-- css link  -->
   <link rel="stylesheet" href="../css/admin.css">

</head>
<body>
  <?php include '../components/admin_header.php';?>
  <section class="playlist-form">

     <h1 class="heading">Create Playlist</h1>
      <form action="" method="post" enctype="multipart/form-data">
        <p>Playlist Status <span>*</span></p>
        <select name="status" id="" class="box">
          <option value="" selected disabled>--Select Status--</option>
          <option value="active">active</option>
          <option value="deactive">deactive</option>
        </select>
        <p>Playlist Title <span>*</span></p>
        <input type="text" name="title" id="" maxlength="150" required placeholder="Enter playlist title" class="box">
        <p>Playlist Description <span>*</span></p>
        <textarea name="description" id="" class="box" placeholder="Write Description" maxlength="1000" cols="30" rows="10"></textarea>
        <p>Playlist Thumbnail <span>*</span></p>
        <input type="file" name="image" accept="image/*" required class="box">
        <input type="submit" name="submit" value="Create Playlist" class="btn">
      </form>
    
  </section>
  <?php include '../components/footer.php';?>
  <script src="../js/admin.js"></script>
</body>
</html>