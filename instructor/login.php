<?php
include '../components/connection.php';

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $pass = $_POST['pass'];
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    // Check if email and hashed password match
    $check_query = "SELECT * FROM instructors WHERE email = '$email' AND password = SHA1('$pass')";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $row = mysqli_fetch_assoc($check_result); // Fetch user data
        setcookie('instructor_id', $row['id'], time() + 60 * 60 * 24 * 30, '/'); // Set cookie for authentication
        $success_msg = "Login successful! Redirecting to dashboard...";
        echo "<script>
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000);
              </script>";
    } else {
        $error_msg = "Incorrect email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en"df>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    <?php include '../css/admin.css'; ?>
  </style>
</head>
<body>
  
<?php
// SweetAlert notifications for feedback
if (isset($error_msg)) {
    echo "
    <script>
        Swal.fire({
            title: 'Login Failed',
            text: '$error_msg',
            icon: 'error',
            width: '400px',
            padding: '20px',
            position: 'center'
        });
    </script>";
}

if (isset($success_msg)) {
    echo "
    <script>
        Swal.fire({
            title: 'Success!',
            text: '$success_msg',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500,
            position: 'center'
        });
    </script>";
}
?>
<div class="form-container">
  <!-- <img src="../image/fun.jpg" class="form-img" alt="" style="left: 6%;"> -->
  <form action="" method="POST" enctype="multipart/form-data" class="login">
    <h3>Login Now</h3>
    <p>Your Email <span>*</span></p>
    <input type="email" name="email" placeholder="Enter your email" maxlength="50" required class="box">
    <p>Your Password <span>*</span></p>
    <input type="password" name="pass" placeholder="Enter your password" maxlength="20" required class="box">
    <p class="link">Don't have an account? <a href="register.php">Register Now</a></p>
    <input type="submit" name="submit" class="btn" value="Login Now">
  </form>
</div>

</body>
</html>
