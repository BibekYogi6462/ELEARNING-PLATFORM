<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:login.php');
    exit;
}

if (isset($_POST['submit'])) {
    // Fetch the current user data
    $fetch_query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($fetch_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fetch_user = $result->fetch_assoc();

    $prev_pass = $fetch_user['password'];
    $prev_image = $fetch_user['image'];

    // Get and sanitize inputs
    $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : $fetch_user['name'];
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : $fetch_user['email'];
    $old_pass = isset($_POST['old_pass']) ? sha1(filter_var($_POST['old_pass'], FILTER_SANITIZE_STRING)) : '';

    // Verify current password
    if ($old_pass != $prev_pass) {
        $message[] = 'Current password is incorrect. No updates have been made.';
    } else {
        // Update name
        if (!empty($name) && $name != $fetch_user['name']) {
            $update_name = "UPDATE users SET name = ? WHERE id = ?";
            $stmt = $conn->prepare($update_name);
            $stmt->bind_param("si", $name, $user_id);
            $stmt->execute();
            $message[] = 'Name updated successfully';
        }

        // Update email
        if (!empty($email) && $email != $fetch_user['email']) {
            $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($email_check);
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message[] = 'Email already taken';
            } else {
                $update_email = "UPDATE users SET email = ? WHERE id = ?";
                $stmt = $conn->prepare($update_email);
                $stmt->bind_param("si", $email, $user_id);
                $stmt->execute();
                $message[] = 'Email updated successfully';
            }
        }

        // Update profile picture if a new one is uploaded
        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $image = filter_var($image, FILTER_SANITIZE_STRING);
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            $rename = time() . '.' . $ext; // Rename file using a timestamp
            $image_size = $_FILES['image']['size'];
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_folder = 'uploaded_files/' . $rename;

            if ($image_size > 20000000) {
                $message[] = 'Image size too large';
            } else {
                $update_image = "UPDATE users SET image = ? WHERE id = ?";
                $stmt = $conn->prepare($update_image);
                $stmt->bind_param("si", $rename, $user_id);
                $stmt->execute();
                move_uploaded_file($image_tmp_name, $image_folder);

                if (!empty($prev_image) && $prev_image != $rename) {
                    unlink('uploaded_files/' . $prev_image); // Remove old image
                }
                $message[] = 'Profile picture updated successfully';
            }
        }

        // Update password if new password is provided
        if (!empty($_POST['new_pass']) && !empty($_POST['cpass'])) {
            $new_pass = sha1(filter_var($_POST['new_pass'], FILTER_SANITIZE_STRING));
            $cpass = sha1(filter_var($_POST['cpass'], FILTER_SANITIZE_STRING));

            if ($new_pass != $cpass) {
                $message[] = 'Confirm password does not match';
            } else {
                $update_pass = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_pass);
                $stmt->bind_param("si", $cpass, $user_id);
                $stmt->execute();
                $message[] = 'Password updated successfully';
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - Update Profile Page</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<div class="banner">
  <div class="detail">
    <div class="title">
      <a href="index.php">home</a><span><i class="bx bx-chevron-right"></i>Update Profile</span>
    </div>
    <h1>Update Profile</h1>
    <p>Dive in and learn React.js from scratch and way more things...</p>
    <div class="flex-btn">
      <a href="login.php" class="btn">Login To Start</a>
      <a href="contact.php" class="btn">Contact us</a>
    </div>
  </div>
  <img src="image/about.png" alt="" class="aboutimg">
</div>

<div class="form-container">
  <div class="heading">
    <span>Join Gurushishya</span>
    <h1>Update Profile</h1>
  </div>
  <form class="register" action="" method="POST" enctype="multipart/form-data">
    <div class="flex">
      <div class="col">
        <p>Your Name <span>*</span></p>
        <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" maxlength="50" class="box">
        <p>Your Email <span>*</span></p>
        <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" maxlength="50" class="box">
        <p>Update Profile Picture</p>
        <input type="file" name="image" accept="image/*" class="box">
      </div>
      <div class="col">
        <p>Old Password <span>*</span></p>
        <input type="password" name="old_pass" placeholder="Enter your old password" maxlength="20" class="box" required>
        <p>New Password</p>
        <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="20" class="box">
        <p>Confirm New Password</p>
        <input type="password" name="cpass" placeholder="Confirm your new password" maxlength="20" class="box">
      </div>
    </div>
    <input type="submit" name="submit" class="btn" value="Update Profile">
  </form>
</div>

<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>
