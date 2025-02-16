<?php
include '../components/connection.php';

$message = []; // Initialize message array

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $specialization = $_POST['specialization'];
    $specialization = filter_var($specialization, FILTER_SANITIZE_STRING);

    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $pass = $_POST['pass'];
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    $cpass = $_POST['cpass'];
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename = time() . '.' . $ext; // Rename file using a timestamp
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_files/' . $rename;

    // Check if email already exists
    $check_query = "SELECT * FROM instructors WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $message[] = 'Email already exists.';
    } else {
        if ($pass !== $cpass) {
            $message[] = 'Passwords do not match.';
        } else {
            $hashed_pass = sha1($cpass); // Hashing password
            $insert_query = "INSERT INTO instructors (name, specialization, email, password, image) 
                             VALUES ('$name', '$specialization', '$email', '$hashed_pass', '$rename')";

            if (mysqli_query($conn, $insert_query)) {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'New tutor registered! You can now log in.';
            } else {
                $message[] = 'Registration failed: ' . mysqli_error($conn);
            }
        }
    }
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
  <title>Admin Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  
<?php
// Display messages if available
if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . $msg . '</span>
            <i class="bx bx-x" onclick="this.parentElement.remove()"></i>
        </div>
        ';
    }
}
?>

<div class="form-container">
  <!-- <img src="../image/fun.jpg" class="form-img" alt="" style="left: -2%;"> -->
  <form action="" method="POST" enctype="multipart/form-data" class="register">
    <h3>Register Now</h3>
    <div class="flex">
      <div class="col">
        <p>Your Name <span>*</span></p>
        <input type="text" name="name" placeholder="Enter Your Name" maxlength="50" required class="box">
        <p>Your Profession <span>*</span></p>
        <select name="specialization" required class="box">
           <option value="" disabled selected>-- Select your Specialization --</option>
           <option value="teacher">Teacher</option>
           <option value="developer">Developer</option>
           <option value="photographer">Photographer</option>
           <option value="designer">Designer</option>
           <option value="musician">Musician</option>
           <option value="engineer">Engineer</option>
           <option value="accountant">Accountant</option>
           <option value="journalist">Journalist</option>
           <option value="video editor">Video Editor</option>
        </select>
        <p>Your Email <span>*</span></p>
        <input type="email" name="email" placeholder="Enter your email" maxlength="50" required class="box">
      </div>
      <div class="col">
        <p>Your Password <span>*</span></p>
        <input type="password" name="pass" placeholder="Enter your password" maxlength="20" required class="box">
        <p>Confirm Password</p>
        <input type="password" name="cpass" placeholder="Confirm your password" maxlength="20" required class="box">
        <p>Select Profile Picture <span>*</span></p>
        <input type="file" name="image" accept="image/*" required class="box">
      </div>
    </div>
    <p class="link">Already have an account? <a href="login.php">Login Now</a></p>
    <input type="submit" name="submit" class="btn" value="Register Now">
  </form>
</div>
</body>
</html>
