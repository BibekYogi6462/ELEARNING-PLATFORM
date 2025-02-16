<?php
// Clear the user_id cookie by setting its expiration time in the past
include 'connection.php';
setcookie('user_id', '', time() - 3600, '/');

// Redirect to the login page or homepage
header('Location: ../index.php'); // Adjust the path as necessary
exit();
?>
