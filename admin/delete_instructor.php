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

    // Verify instructor existence
    $verify_instructor_query = "SELECT * FROM `instructors` WHERE id = ?";
    $stmt_verify = $conn->prepare($verify_instructor_query);
    $stmt_verify->bind_param("i", $instructor_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows > 0) {
        // Delete instructor image if exists
        $instructor = $result_verify->fetch_assoc();
        if (!empty($instructor['image'])) {
            $image_path = '../uploaded_files/' . $instructor['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Delete instructor from database
        $delete_instructor_query = "DELETE FROM `instructors` WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_instructor_query);
        $stmt_delete->bind_param("i", $instructor_id);
        $stmt_delete->execute();

        $message[] = 'Instructor deleted successfully';
    } else {
        $message[] = 'Instructor does not exist';
    }
    header('location: manage_instructors.php');
    exit;
} else {
    header('location: manage_instructors.php');
    exit;
}
?>
