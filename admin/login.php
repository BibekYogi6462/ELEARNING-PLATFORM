<?php
include '../components/connection.php';

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $pass = $_POST['pass'];
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    // Check if email and hashed password match for admin
    $check_query = "SELECT * FROM admin WHERE email = '$email' AND password = md5('$pass')";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $row = mysqli_fetch_assoc($check_result); // Fetch admin data
        setcookie('admin_id', $row['id'], time() + 60 * 60 * 24 * 30, '/'); // Set cookie for admin authentication
        $success_msg = "Login successful! Redirecting to dashboard...";
        echo "<script>
                setTimeout(() => {
                    window.location.href = 'admin_dashboard.php';
                }, 2000);
              </script>";
    } else {
        $error_msg = "Incorrect email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
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
  <form action="" method="POST" enctype="multipart/form-data" class="login">
    <h3>Admin Login</h3>
    <p>Email Address <span>*</span></p>
    <input type="email" name="email" placeholder="Enter your email" maxlength="50" required class="box">
    <p>Password <span>*</span></p>
    <input type="password" name="pass" placeholder="Enter your password" maxlength="20" required class="box">
    <input type="submit" name="submit" class="btn" value="Login Now">
  </form>
</div>

</body>
</html>
