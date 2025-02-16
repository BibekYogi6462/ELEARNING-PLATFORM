<?php
include 'components/connection.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {
    try {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
        $msg = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);

        // Check if message already exists
        $select_contact = $conn->prepare("SELECT * FROM `contact` WHERE name = ? AND email = ? AND number = ? AND message = ?");
        $select_contact->bind_param("ssss", $name, $email, $number, $msg);
        $select_contact->execute();
        $result = $select_contact->get_result();

        if ($result->num_rows > 0) {
            $message[] = 'Message already sent!';
        } else {
            // Insert the new message
            $insert_message = $conn->prepare("INSERT INTO `contact` (name, email, number, message) VALUES (?, ?, ?, ?)");
            $insert_message->bind_param("ssss", $name, $email, $number, $msg);
            $insert_message->execute();
            $message[] = 'Message successfully sent!';
        }
    } catch (Exception $e) {
        $message[] = 'Something went wrong. Please try again later.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gurushishya - Contact Us Page</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<!-- Banner Section -->
<div class="banner">
    <div class="detail">
        <div class="title">
            <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>contact us now</span>
        </div>
        <h1>Contact Us Now</h1>
        <p>Dive in and learn React.js from scratch and way more things...</p>
  
    </div>
</div>

<!-- Contact Section -->
<section class="contact">
    <div class="address-detail">
        <div class="box">
            <i class="bx bxs-phone"></i>
            <h3>Phone Number</h3>
            <a href="tel:01-4596223">9860096462</a>
            <a href="tel:01-4596223">01-45932223</a>
        </div>
        <div class="box">
            <i class="bx bxs-envelope"></i>
            <h3>Email Address</h3>
            <a href="mailto:gurushishya@gmail.com">gurushishya@gmail.com</a>
            <a href="mailto:bibekyogi6462@gmail.com">bibekyogi6462@gmail.com</a>
        </div>
        <div class="box">
            <i class="bx bxs-map"></i>
            <h3>Office Address</h3>
            <a href="#">Durbarmarg, Kathmandu</a>
            <a href="#">Building 245, Flat 14</a>
        </div>
    </div>
    <div class="box-container">
        <div class="box">
            <img src="image/contact.jpg" alt="Contact">
        </div>

        <form action="" method="POST">
            <div class="heading">
                <span>Education for Everyone</span>
                <h1>Contact With Us</h1>
            </div>
            <?php
            if (isset($message)) {
                foreach ($message as $msg) {
                    echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
                }
            }
            ?>
            <div class="input-field">
                <p>Name <span>*</span></p>
                <input type="text" name="name" maxlength="100" required class="box">
            </div>
            <div class="input-field">
                <p>Email <span>*</span></p>
                <input type="email" name="email" maxlength="100" required class="box">
            </div>
            <div class="input-field">
                <p>Number <span>*</span></p>
                <input type="text" name="number" maxlength="10" required class="box" pattern="\d{7,10}">
            </div>
            <div class="input-field">
                <p>Message <span>*</span></p>
                <textarea name="msg" class="box" cols="30" rows="10" maxlength="1000" required></textarea>
            </div>
            <input type="submit" name="submit" value="Send Message" class="btn">
        </form>
    </div>
</section>


<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>
