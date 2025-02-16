<?php
include '../components/connection.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location: login.php');
    exit;
}

// Fetch all messages
$select_messages_query = "SELECT * FROM `contact` ORDER BY id DESC";
$select_messages_result = mysqli_query($conn, $select_messages_query);

// Handle delete request
if (isset($_POST['delete'])) {
    $message_id = filter_var($_POST['message_id'], FILTER_SANITIZE_NUMBER_INT);

    $delete_message_query = $conn->prepare("DELETE FROM `contact` WHERE id = ?");
    $delete_message_query->bind_param("i", $message_id);

    if ($delete_message_query->execute()) {
        echo "<script>alert('Message deleted successfully.'); window.location.href = 'message.php';</script>";
        exit;
    } else {
        echo "<script>alert('Failed to delete message.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <!-- Boxicon link -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- CSS link -->
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin2.css"> <!-- Separate CSS for Messages -->
    <style>
      .user-messages {
  padding: 20px;
  background-color: #f9f9f9;
}

.user-messages .heading {
  font-size: 24px;
  margin-bottom: 20px;
  color: #333;
  text-align: center;
}

/* Message box styling */
.message-box-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.message-box {
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 5px;
  padding: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.message-box p {
  margin: 10px 0;
  color: #555;
  font-size: 18px;
}

/* Delete button styling */
.delete-message-form {
  margin-top: 10px;
}

.delete-message-btn {
  background-color: #ff4d4d;
  color: white;
  padding: 8px 12px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease;
}

.delete-message-btn:hover {
  background-color: #e60000;
}

/* Empty message styling */
.empty-message {
  text-align: center;
  color: #999;
  font-size: 18px;
  margin-top: 20px;
}

    </style>
</head>
<body>

    <?php include '../components/admin2header.php'; ?>
    <section class="user-messages">
        <h1 class="heading">User Messages</h1>
        <div class="message-box-container">
            <?php if (mysqli_num_rows($select_messages_result) > 0) : ?>
                <?php while ($message = mysqli_fetch_assoc($select_messages_result)) : ?>
                    <div class="message-box">
                        <p><strong>Name:</strong> <?= htmlspecialchars($message['name']); ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($message['email']); ?></p>
                        <p><strong>Number:</strong> <?= htmlspecialchars($message['number']); ?></p>
                        <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($message['message'])); ?></p>
                        <form action="" method="post" class="delete-message-form" onsubmit="return confirmDelete();">
                            <input type="hidden" name="message_id" value="<?= $message['id']; ?>">
                            <button type="submit" name="delete" class="delete-message-btn">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p class="empty-message">No messages found!</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>
    <script>
        // Confirm delete function
        function confirmDelete() {
            return confirm('Are you sure you want to delete this message?');
        }
    </script>
<script src="../js/admin.js"></script>

</body>
</html>
