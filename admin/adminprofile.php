<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

if (isset($_POST['submit'])) {
    // Fetch the current admin data
    $stmt = mysqli_prepare($conn, "SELECT * FROM `admin` WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $admin_id);
    mysqli_stmt_execute($stmt);
    $result = $stmt->get_result();
    $fetch_admin = $result->fetch_assoc();

    $prev_pass = $fetch_admin['password'];

    // Sanitize inputs
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Handle password inputs
    $old_pass = md5(filter_var($_POST['old_pass'], FILTER_SANITIZE_STRING));
    $new_pass = !empty($_POST['new_pass']) ? md5(filter_var($_POST['new_pass'], FILTER_SANITIZE_STRING)) : '';
    $cpass = !empty($_POST['cpass']) ? md5(filter_var($_POST['cpass'], FILTER_SANITIZE_STRING)) : '';

    $updates_made = false; // Flag to track if any updates were made

    // Verify old password before any update
    if ($old_pass !== $prev_pass) {
        $message[] = 'Old password does not match. No changes were made.';
    } else {
        // Update name if changed
        if ($name !== $fetch_admin['username']) {
            $update_name = "UPDATE admin SET username = ? WHERE id = ?";
            $stmt = $conn->prepare($update_name);
            $stmt->bind_param("si", $name, $admin_id);
            $stmt->execute();
            $message[] = 'Name updated successfully.';
            $updates_made = true;
        }

        // Update email if changed
        if ($email !== $fetch_admin['email']) {
            $email_check = "SELECT id FROM admin WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($email_check);
            $stmt->bind_param("si", $email, $admin_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message[] = 'Email already taken.';
            } else {
                $update_email = "UPDATE admin SET email = ? WHERE id = ?";
                $stmt = $conn->prepare($update_email);
                $stmt->bind_param("si", $email, $admin_id);
                $stmt->execute();
                $message[] = 'Email updated successfully.';
                $updates_made = true;
            }
        }

        // Update password only if new and confirm passwords are provided
        if (!empty($new_pass) && !empty($cpass)) {
            if ($new_pass !== $cpass) {
                $message[] = 'Confirm password does not match.';
            } else {
                $update_pass = "UPDATE admin SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_pass);
                $stmt->bind_param("si", $new_pass, $admin_id);
                $stmt->execute();
                $message[] = 'Password updated successfully.';
                $updates_made = true;
            }
        }
    }

    // If no updates were made, inform the user
    if (!$updates_made) {
        $message[] = 'No changes were made.';
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
  <title>Update Profile</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<?php include '../components/admin2header.php';?>
<div class="form-container" style="min-height:calc(100vh -19rem); padding: 5rem 0;">
<form action="" method="POST" enctype="multipart/form-data" class="register">
    <h3>Update profile</h3>
    <div class="flex">
        <div class="col">
            <p>Your Name <span>*</span></p>
            <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['username'], ENT_QUOTES); ?>" maxlength="50" required class="box">
           
            
            <p>Your Email <span>*</span></p>
            <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email'], ENT_QUOTES); ?>" maxlength="50" required class="box">
        </div>

        <div class="col">
            <p>Old Password <span>*</span></p>
            <input type="password" name="old_pass"  placeholder="Enter your old password" maxlength="20" required class="box">
            <p>New Password</p>
            <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="20" class="box">
            <p>Confirm Password</p>
            <input type="password" name="cpass" placeholder="Confirm your password" maxlength="20" class="box">
        </div>
    </div>
 
    <input type="submit" name="submit" class="btn" value="Update Profile">
</form>

</div>
<?php include '../components/footer.php';?>
  <script src="../js/admin.js"></script>

</body>
</html>
