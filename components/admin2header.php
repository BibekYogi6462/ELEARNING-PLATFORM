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
        <a href="dashboard.php" class="logo"><img src="../image/reallogo2.png" width="80px" alt=""></a>
        <!-- <form action="searchpage.php" method="POST" class="search-form">
            <input type="text" name="search" placeholder="Search.." required maxlength="100">
            <button type="submit" class="bx bx-search-alt-2" name="search_btn"></button>
        </form> -->
        <div class="icons">
            <div id="menu-btn" class="bx bx-list-plus"></div>
            <div id="search-btn" class="bx bx-search-alt-2"></div>
            <div id="user-btn" class="bx bxs-user"></div>
        </div>

        <div class="profile">
          <?php
            include '../components/connection.php';

            $stmt = mysqli_prepare($conn, "SELECT * FROM `admin` WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $admin_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $fetch_profile = mysqli_fetch_assoc($result);
          ?>
          
            <h3><?= $fetch_profile['username']; ?></h3>
            <!-- <span>Admin</span><br><br><br> -->

            <div id="flex-btn">
                <a href="adminprofile.php" class="btn">Update Profile</a>
                <a href="../components/admin2logout.php" onclick="return confirm('Are you sure you want to logout?');" class="btn">Logout</a>
            </div>

          <?php
            } else {
          ?>
            <h3>Please Login</h3>
            <div id="flex-btn">
                <a href="login.php" class="btn">Login</a>
            </div>
          <?php
            }
            mysqli_stmt_close($stmt);
          ?>
        </div>
    </section>
</header>

<div class="side-bar">
  <div class="profile">
    <?php
      $stmt = mysqli_prepare($conn, "SELECT * FROM `admin` WHERE id = ?");
      mysqli_stmt_bind_param($stmt, 'i', $admin_id);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      if (mysqli_num_rows($result) > 0) {
        $fetch_profile = mysqli_fetch_assoc($result);
      ?>
   <!-- img  -->
      <h3><?= $fetch_profile['username']; ?></h3>
      <!-- <p>Admin</p> -->
      <a href="adminprofile.php" class="btn">Update Profile</a>

    <?php
      } else {
    ?>
        <h3>Please Login</h3>
        <div id="flex-btn">
            <a href="login.php" class="btn">Login</a>
        </div>
    <?php
      }
      mysqli_stmt_close($stmt);
    ?>
  </div>
  <nav class="navbar">
        <a href="admin_dashboard.php"><i class="bx bxs-home-heart"></i><span>Dashboard</span></a>
        <a href="manage_instructors.php"><i class="bx bxs-user-rectangle"></i><span>Instructors</span></a>
        <a href="manage_students.php"><i class="bx bxs-group"></i><span>Students</span></a>
        <a href="playlists.php"><i class="bx bxs-receipt"></i><span>Playlists</span></a>
        <a href="view_message.php"><i class="bx bxs-message-square"></i><span>Feedback</span></a>
        <a href="../components/admin2logout.php" onclick="return confirm('Logout from this website?');">
            <i class="bx bx-log-in-circle"></i><span>Logout</span>
        </a>
    </nav>
</div>
