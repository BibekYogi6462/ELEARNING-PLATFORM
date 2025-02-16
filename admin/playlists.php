<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Fetch instructors and their playlists
$instructors_with_playlists = [];
$select_instructors_query = "SELECT * FROM `instructors` ORDER BY id DESC";
$stmt_instructors = $conn->prepare($select_instructors_query);
$stmt_instructors->execute();
$instructors_result = $stmt_instructors->get_result();

while ($instructor = $instructors_result->fetch_assoc()) {
    $instructor_id = $instructor['id'];
    
    // Fetch playlists created by this instructor (order by `date` instead of `created_at`)
    $select_playlists_query = "SELECT * FROM `playlist` WHERE instructor_id = ? ORDER BY `date` DESC";
    $stmt_playlists = $conn->prepare($select_playlists_query);
    $stmt_playlists->bind_param("i", $instructor_id);
    $stmt_playlists->execute();
    $playlists_result = $stmt_playlists->get_result();

    // Store instructor and their playlists
    $instructor['playlists'] = [];
    while ($playlist = $playlists_result->fetch_assoc()) {
        $instructor['playlists'][] = $playlist;
    }

    $instructors_with_playlists[] = $instructor;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Playlists</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/admin2.css">
</head>
<body>
<?php include '../components/admin2header.php'; ?>

<section class="playlists">
    <h1 class="heading">Instructor Playlists</h1>

    <div class="box-container">
        <?php if (!empty($instructors_with_playlists)) { ?>
            <?php foreach ($instructors_with_playlists as $instructor) { ?>
                <div class="box">
                    <div class="instructor-info">
                        <div class="instructor-img">
                            <img src="../uploaded_files/<?= htmlspecialchars($instructor['image']); ?>" alt="Instructor Image">
                        </div>
                        <div class="instructor-details">
                            <h3><?= htmlspecialchars($instructor['name']); ?></h3>
                            <p>Email: <?= htmlspecialchars($instructor['email']); ?></p>
                        </div>
                    </div>

                    <div class="playlists-list">
                        <h4>Created Playlists:</h4>
                        <?php if (!empty($instructor['playlists'])) { ?>
                            <ul>
                                <?php foreach ($instructor['playlists'] as $playlist) { ?>
                                    <li>
                                        <a href="playlist_details.php?id=<?= $playlist['id']; ?>"><?= htmlspecialchars($playlist['title']); ?></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <p>No playlists created by this instructor</p>
                        <?php } ?>
                    </div>
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
