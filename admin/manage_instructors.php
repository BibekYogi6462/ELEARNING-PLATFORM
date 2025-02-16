<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Delete Instructor
if (isset($_POST['delete'])) {
    $instructor_id = $_POST['instructor_id'];
    $instructor_id = filter_var($instructor_id, FILTER_SANITIZE_STRING);

    // Verify instructor existence
    $verify_instructor_query = "SELECT * FROM `instructors` WHERE id = ?";
    $stmt_verify = $conn->prepare($verify_instructor_query);
    $stmt_verify->bind_param("i", $instructor_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows > 0) {
        // Delete instructor
        $delete_instructor_query = "DELETE FROM `instructors` WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_instructor_query);
        $stmt_delete->bind_param("i", $instructor_id);
        $stmt_delete->execute();

        $message[] = 'Instructor deleted successfully';
    } else {
        $message[] = 'Instructor does not exist or is already deleted';
    }
}

// Fetch instructors
$instructors = [];
$select_instructors_query = "SELECT * FROM `instructors` ORDER BY id DESC";
$stmt = $conn->prepare($select_instructors_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Instructors</title>
  <!-- Boxicons and Admin CSS -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="instructors">
    <h1 class="heading">Manage Instructors</h1>

    <div class="box-container">
        <?php if (!empty($instructors)) { ?>
            <?php foreach ($instructors as $instructor) { ?>
                <div class="box">
                    <div class="tutor">
                        <img src="../uploaded_files/<?= htmlspecialchars($instructor['image']); ?>" alt="<?= htmlspecialchars($instructor['name']); ?>">
                        <div>
                            <h3><?= htmlspecialchars($instructor['name']); ?></h3>
                            <span><?= htmlspecialchars($instructor['specialization']); ?></span>
                        </div>
                    </div>
                    <p>Email: <?= htmlspecialchars($instructor['email']); ?></p>
                    <form action="" method="POST" class="flex-btn">
                        <input type="hidden" name="instructor_id" value="<?= $instructor['id']; ?>">
                        <a href="update_instructor.php?id=<?= $instructor['id']; ?>" class="btn">Update</a>
                        <input type="submit" name="delete" value="Delete" class="btn" onclick="return confirm('Are you sure you want to delete this instructor?');">
                    </form>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="empty">No instructors found</p>
        <?php } ?>
    </div>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
