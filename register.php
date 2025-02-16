<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);
    $cpass = filter_var($_POST['cpass'], FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename = time() . '.' . $ext; // Rename file using a timestamp
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_files/' . $rename;

    // Check if email already exists
    $select_user_query = "SELECT * FROM users WHERE email = ?";
    $select_user_stmt = $conn->prepare($select_user_query);
    $select_user_stmt->bind_param('s', $email);
    $select_user_stmt->execute();
    $select_user_result = $select_user_stmt->get_result();

    if ($select_user_result->num_rows > 0) {
        $message[] = 'Email already taken';
    } else {
        if ($pass !== $cpass) {
            $message[] = 'Passwords do not match';
        } else {
            $hashed_pass = sha1($cpass); // Hashing password
            $insert_query = "INSERT INTO users (name, email, password, image) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param('ssss', $name, $email, $hashed_pass, $rename);

            if ($insert_stmt->execute()) {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'Successfully Registered, Now Login';

                // Auto-login user after successful registration
                $verify_user_query = "SELECT * FROM users WHERE email = ? AND password = ? LIMIT 1";
                $verify_user_stmt = $conn->prepare($verify_user_query);
                $verify_user_stmt->bind_param('ss', $email, $hashed_pass);
                $verify_user_stmt->execute();
                $verify_user_result = $verify_user_stmt->get_result();

                if ($verify_user_result->num_rows > 0) {
                    $row = $verify_user_result->fetch_assoc();
                    setcookie('user_id', $row['id'], time() + 60 * 60 * 24 * 30, '/');
                    header('Location: index.php');
                    exit;
                }
            } else {
                $message[] = 'Registration failed: ' . $conn->error;
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
          <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>register now</span>
        </div>
        <h1>register now</h1>
        <p>Dive in and learn from scratch and way more things ..

        </p>

      </div>
    </div>


    <!-- registration section  -->
     
<div class="form-container">
  <div class="heading">
    <span>join gurushishya</span>
    <h1>Create Account</h1>

  </div>
  <form class="register" action="" method="POST" enctype="multipart/form-data">
  <div class="flex">
    <div class="col">
    <p>Your Name <span>*</span></p>
    <input type="text" name="name" placeholder="Enter Your Name" maxlength="50" required class="box">
    <p>Your Email <span>*</span></p>
        <input type="email" name="email" placeholder="Enter your email" maxlength="50" required class="box">
 
    </div>
    <div class="col">
        <p>Your Password <span>*</span></p>
        <input type="password" name="pass" placeholder="Enter your password" maxlength="20" required class="box">
        <p>Confirm Password</p>
        <input type="password" name="cpass" placeholder="Confirm your password" maxlength="20" required class="box">
        
      </div>
  </div>    
  <p>select pic <span>*</span></p>
  <input type="file" name="image" accept="image/*" required class="box">
  <p class="link">Already have an account? <a href="login.php">Login Now</a></p>
    <input type="submit" name="submit" class="btn" value="Register Now">
</form>
</div>







<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>