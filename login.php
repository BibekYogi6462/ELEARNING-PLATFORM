<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

$message = []; // Initialize the message array

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);

    $check_query = "SELECT * FROM users WHERE email = ? AND password = SHA1(?)";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('ss', $email, $pass);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc(); // Fetch user data
        setcookie('user_id', $row['id'], time() + 60 * 60 * 24 * 30, '/'); // Set cookie for authentication
        header('Location: index.php');
        $message[] = 'Successfully Logged in.';

        exit; // Ensure no further code executes after redirection
    } else {
        $message[] = 'Incorrect email or password.';
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
          <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>login now</span>
        </div>
        <h1>Login now</h1>
        <p>Dive in and learn React.js from scratch and way more things ..

        </p>
   
      </div>
    </div>


    <!-- registration section  -->
     
<div class="form-container">
  <div class="heading">
    <span>Already joined gurushishya?</span>
    <h1>Welcome Back</h1>

  </div>
  <form class="login" action="" method="POST" enctype="multipart/form-data">
  <p>Your Email <span>*</span></p>
  <input type="email" name="email" placeholder="Enter your email" maxlength="50" required class="box">

  <p>Your Password <span>*</span></p>
        <input type="password" name="pass" placeholder="Enter your password" maxlength="20" required class="box">
  
  <p class="link">Do not have an account? <a href="register.php">Register Now</a></p>
    <input type="submit" name="submit" class="btn" value="Login Now">
</form>
</div>







<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>