<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Delete User
if (isset($_POST['delete'])) {
    $user_id = $_POST['user_id'];
    $user_id = filter_var($user_id, FILTER_SANITIZE_STRING);

    // Verify user existence
    $verify_user_query = "SELECT * FROM `users` WHERE id = ?";
    $stmt_verify = $conn->prepare($verify_user_query);
    $stmt_verify->bind_param("i", $user_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows > 0) {
        // Delete user
        $delete_user_query = "DELETE FROM `users` WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_user_query);
        $stmt_delete->bind_param("i", $user_id);
        $stmt_delete->execute();

        $message[] = 'User deleted successfully';
    } else {
        $message[] = 'User does not exist or is already deleted';
    }
}

// Fetch users
$users = [];
$select_users_query = "SELECT * FROM `users` ORDER BY id DESC";
$stmt = $conn->prepare($select_users_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="users">
    <h1 class="heading">Manage Users</h1>

    <div class="box-container">
        <?php if (!empty($users)) { ?>
            <?php foreach ($users as $user) { ?>
                <div class="box">
                    <div class="user">
                        <img src="../uploaded_files/<?= htmlspecialchars($user['image']); ?>" alt="<?= htmlspecialchars($user['name']); ?>">
                        <div>
                            <h3><?= htmlspecialchars($user['name']); ?></h3>
                            <span><?= htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>
                    <p>Email: <?= htmlspecialchars($user['email']); ?></p>
                    <form action="" method="POST" class="flex-btn">
                        <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                        <a href="update_user.php?id=<?= $user['id']; ?>" class="btn">Update</a>
                        <input type="submit" name="delete" value="Delete" class="btn" onclick="return confirm('Are you sure you want to delete this user?');">
                    </form>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="empty">No users found</p>
        <?php } ?>
    </div>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
