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
    $instructor_id = $_GET['id'];
    $instructor_id = filter_var($instructor_id, FILTER_SANITIZE_NUMBER_INT);

    // Fetch instructor details
    $select_instructor_query = "SELECT * FROM `instructors` WHERE id = ?";
    $stmt = $conn->prepare($select_instructor_query);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();

    if (!$instructor) {
        header('location: manage_instructors.php');
        exit;
    }
}

// Update instructor
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $email = $_POST['email'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];

    // Handle image upload
    if (!empty($image)) {
        $image = time() . '_' . $image;
        move_uploaded_file($image_tmp, '../uploaded_files/' . $image);
        // SQL query to update instructor with image
        $update_instructor_query = "UPDATE `instructors` SET name = ?, specialization = ?, email = ?, image = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_instructor_query);
        $stmt_update->bind_param("ssssi", $name, $specialization, $email, $image, $instructor_id);
    } else {
        // SQL query to update instructor without image
        $update_instructor_query = "UPDATE `instructors` SET name = ?, specialization = ?, email = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_instructor_query);
        $stmt_update->bind_param("sssi", $name, $specialization, $email, $instructor_id);
    }

    $stmt_update->execute();

    $message[] = 'Instructor updated successfully';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Instructor</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="update-instructor">
    <h1 class="heading">Update Instructor</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="name">Instructor Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($instructor['name']); ?>" required>

        <label for="specialization">Specialization</label>
        <input type="text" name="specialization" value="<?= htmlspecialchars($instructor['specialization']); ?>" required>

        <label for="email">Instructor Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($instructor['email']); ?>" required>

        <label for="image">Instructor Image (optional)</label>
        <input type="file" name="image">

        <?php if (!empty($instructor['image'])) { ?>
            <img src="../uploaded_files/<?= htmlspecialchars($instructor['image']); ?>" alt="<?= htmlspecialchars($instructor['name']); ?>" style="width: 100px; height: 100px; margin-top: 10px;">
        <?php } ?>

        <input type="submit" name="update" value="Update Instructor" class="btn">
    </form>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin.js"></script>
</body>
</html>
