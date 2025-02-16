<?php
include '../components/connection.php';

if (isset($_COOKIE['instructor_id'])) {
    $instructor_id = $_COOKIE['instructor_id'];
} else {
    header('location: login.php');
    exit;
}

$message = []; // Initialize messages array

if (isset($_POST['submit'])) {
    // Fetch the current instructor data
    $fetch_query = "SELECT * FROM instructors WHERE id = ?";
    $stmt = $conn->prepare($fetch_query);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fetch_instructor = $result->fetch_assoc();

    $prev_pass = $fetch_instructor['password'];
    $prev_image = $fetch_instructor['image'];

    // Sanitize inputs
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $specialization = filter_var($_POST['specialization'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $current_pass = sha1(filter_var($_POST['current_pass'], FILTER_SANITIZE_STRING));
    $new_pass = sha1(filter_var($_POST['new_pass'], FILTER_SANITIZE_STRING));
    $cpass = sha1(filter_var($_POST['cpass'], FILTER_SANITIZE_STRING));

    // Verify current password
    if ($current_pass !== $prev_pass) {
        $message[] = 'Current password is incorrect. No changes were made.';
    } else {
        // Update name if changed
        if (!empty($name) && $name !== $fetch_instructor['name']) {
            $update_name = "UPDATE instructors SET name = ? WHERE id = ?";
            $stmt = $conn->prepare($update_name);
            $stmt->bind_param("si", $name, $instructor_id);
            $stmt->execute();
            $message[] = 'Name updated successfully.';
        }

        // Update specialization if changed
        if (!empty($specialization) && $specialization !== $fetch_instructor['specialization']) {
            $update_specialization = "UPDATE instructors SET specialization = ? WHERE id = ?";
            $stmt = $conn->prepare($update_specialization);
            $stmt->bind_param("si", $specialization, $instructor_id);
            $stmt->execute();
            $message[] = 'Specialization updated successfully.';
        }

        // Update email if changed and not taken
        if (!empty($email) && $email !== $fetch_instructor['email']) {
            $email_check = "SELECT id FROM instructors WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($email_check);
            $stmt->bind_param("si", $email, $instructor_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message[] = 'Email is already taken.';
            } else {
                $update_email = "UPDATE instructors SET email = ? WHERE id = ?";
                $stmt = $conn->prepare($update_email);
                $stmt->bind_param("si", $email, $instructor_id);
                $stmt->execute();
                $message[] = 'Email updated successfully.';
            }
        }

        // Update profile picture if uploaded
        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $image = filter_var($image, FILTER_SANITIZE_STRING);
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            $rename = time() . '.' . $ext; // Rename file using a timestamp
            $image_size = $_FILES['image']['size'];
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_folder = '../uploaded_files/' . $rename;

            if ($image_size > 20000000) {
                $message[] = 'Image size is too large.';
            } else {
                $update_image = "UPDATE instructors SET image = ? WHERE id = ?";
                $stmt = $conn->prepare($update_image);
                $stmt->bind_param("si", $rename, $instructor_id);
                $stmt->execute();
                move_uploaded_file($image_tmp_name, $image_folder);

                if (!empty($prev_image) && $prev_image !== $rename) {
                    unlink('../uploaded_files/' . $prev_image); // Remove old image
                }
                $message[] = 'Profile picture updated successfully.';
            }
        }

        // Update password if provided
        if (!empty($_POST['new_pass']) && !empty($_POST['cpass'])) {
            if ($new_pass !== $cpass) {
                $message[] = 'New password and confirm password do not match.';
            } else {
                $update_pass = "UPDATE instructors SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_pass);
                $stmt->bind_param("si", $new_pass, $instructor_id);
                $stmt->execute();
                $message[] = 'Password updated successfully.';
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
  <title>Update Profile</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<?php include '../components/admin_header.php';?>
<div class="form-container" style="min-height:calc(100vh -19rem); padding: 5rem 0;">
<form action="" method="POST" enctype="multipart/form-data" class="register">
    <h3>Update profile</h3>
    <div class="flex">
        <div class="col">
            <p>Your Name <span>*</span></p>
            <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name'], ENT_QUOTES); ?>" maxlength="50" required class="box">
            <p>Your Profession <span>*</span></p>
            <select name="specialization" required class="box">
                <option value="" disabled>Select your specialization</option>
                <option value="teacher" <?= $fetch_profile['specialization'] == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                <option value="developer" <?= $fetch_profile['specialization'] == 'developer' ? 'selected' : ''; ?>>Developer</option>
                <option value="photographer" <?= $fetch_profile['specialization'] == 'photographer' ? 'selected' : ''; ?>>Photographer</option>
                <option value="designer" <?= $fetch_profile['specialization'] == 'designer' ? 'selected' : ''; ?>>Designer</option>
                <option value="musician" <?= $fetch_profile['specialization'] == 'musician' ? 'selected' : ''; ?>>Musician</option>
                <option value="engineer" <?= $fetch_profile['specialization'] == 'engineer' ? 'selected' : ''; ?>>Engineer</option>
                <option value="accountant" <?= $fetch_profile['specialization'] == 'accountant' ? 'selected' : ''; ?>>Accountant</option>
                <option value="journalist" <?= $fetch_profile['specialization'] == 'journalist' ? 'selected' : ''; ?>>Journalist</option>
                <option value="video editor" <?= $fetch_profile['specialization'] == 'video editor' ? 'selected' : ''; ?>>Video Editor</option>
            </select>
            <p>Your Email <span>*</span></p>
            <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email'], ENT_QUOTES); ?>" maxlength="50" required class="box">
        </div>
        <div class="col">
        <p>Current Password <span>*</span></p>
<input type="password" name="current_pass" placeholder="Enter your current password" maxlength="20" required class="box">

            <p>New Password</p>
            <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="20" class="box">
            <p>Confirm Password</p>
            <input type="password" name="cpass" placeholder="Confirm your password" maxlength="20" class="box">
        </div>
    </div>
    <!-- <p>Update Profile Picture <span>*</span></p> -->
    <input type="file" name="image" accept="image/*"  class="box">
    <input type="submit" name="submit" class="btn" value="Update Profile">
</form>

</div>
<?php include '../components/footer.php';?>
  <script src="../js/admin.js"></script>

</body>
</html>
