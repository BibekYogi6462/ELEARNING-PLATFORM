<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

    // Fetch user details
    $select_user_query = "SELECT * FROM `users` WHERE id = ?";
    $stmt = $conn->prepare($select_user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header('location: manage_users.php');
        exit;
    }
}

// Update user
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];

    // Handle image upload
    if (!empty($image)) {
        $image = time() . '_' . $image;
        move_uploaded_file($image_tmp, '../uploaded_files/' . $image);
        // SQL query to update user with image
        $update_user_query = "UPDATE `users` SET name = ?, email = ?, image = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_user_query);
        $stmt_update->bind_param("sssi", $name, $email, $image, $user_id);
    } else {
        // SQL query to update user without image
        $update_user_query = "UPDATE `users` SET name = ?, email = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_user_query);
        $stmt_update->bind_param("ssi", $name, $email, $user_id);
    }

    $stmt_update->execute();

    $message[] = 'User updated successfully';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update User</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="update-user">
    <h1 class="heading">Update User</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="name">User Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>

        <label for="email">User Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

        <label for="image">User Image (optional)</label>
        <input type="file" name="image">

        <?php if (!empty($user['image'])) { ?>
            <img src="../uploaded_files/<?= htmlspecialchars($user['image']); ?>" alt="<?= htmlspecialchars($user['name']); ?>" style="width: 100px; height: 100px; margin-top: 10px;">
        <?php } ?>

        <input type="submit" name="update" value="Update User" class="btn">
    </form>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
