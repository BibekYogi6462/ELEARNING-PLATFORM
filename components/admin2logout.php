<?php
// Clear the user_id cookie by setting its expiration time in the past
setcookie('admin_id', '', time() - 3600, '/');

// Redirect to the login page or homepage
header('Location: ../admin/login.php'); // Adjust the path as necessary
exit();
?>
