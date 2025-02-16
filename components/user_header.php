<?php
// Display messages if available
if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message" id="message' . $msg . '">
            <span>' . $msg . '</span>
        </div>
        <script>
            // Automatically remove the message after 2 seconds
            setTimeout(function() {
                const messageElement = document.getElementById("message' . $msg . '");
                messageElement.classList.add("fade-out");

                // Wait for the fade-out transition to finish
                messageElement.addEventListener("transitionend", function() {
                    messageElement.style.display = "none";
                });
            }, 2000);
        </script>
        ';
    }
}
?>

<header class="header">
  <section class="flex">
  <a href="index.php" ><img src="image/reallogo2.png" width="80px" alt=""></a>
  <nav class="navbar">
    <a href="index.php"><span>home</span></a>
    <a href="about.php"><span>about us</span></a>
    <a href="courses.php"><span>courses</span></a>
    <a href="teachers.php"><span>teacher</span></a>
    <a href="contact.php"><span>contact us</span></a>

  </nav>

  <form action="search_course.php" method="post" class="search-form">
   <input type="text" name="search_course" id="" placeholder="search course..." required maxlength="100">
   <button type="submit" name="search_course_btn" class="bx bx-search-alt-2"></button>
  </form>
  <div class="icons">
            <div id="menu-btn" class="bx bx-list-plus"></div>
            <div id="search-btn" class="bx bx-search-alt-2"></div>
            <div id="user-btn" class="bx bxs-user"></div>
        </div>
        <div class="profile">
          <?php
            include 'connection.php';

            $stmt = mysqli_prepare($conn, "SELECT * FROM `users` WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $fetch_profile = mysqli_fetch_assoc($result);
          ?>
            <img src="uploaded_files/<?= $fetch_profile['image']; ?>">
            <h3><?= $fetch_profile['name']; ?></h3>
            <span>student</span><br><br><br>

            <div id="flex-btn">
                <a href="profile.php" class="btn">View Profile</a>
                <a href="components/userlogout.php" onclick="return confirm('Are you sure you want to logout?');" class="btn">Logout</a>
            </div>

          <?php
            } else {
          ?>
            <h3 style="margin-bottom: 1rem;">Please Login or Register</h3>
            <div id="flex-btn">
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn">Register</a>
            </div>
          <?php
            }
            mysqli_stmt_close($stmt);
          ?>
        </div>
  </section>

</header>