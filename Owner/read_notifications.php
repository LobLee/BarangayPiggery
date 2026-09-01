<?php
// Start session and include database connection
include('../connection/conn.php');
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the notification ID from the query parameter
if (isset($_GET['id'])) {
    $notification_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Update the notification's is_read status to 1 (read)
    $update_query = "UPDATE brgy_piggery.notifications SET is_read = 1 WHERE id = ? AND owner_id = ?";
    $stmt = $admin_conn->prepare($update_query);
    $stmt->bind_param("ii", $notification_id, $user_id);
    if ($stmt->execute()) {
        // Redirect back to the page where notifications are listed
        header("Location: owner_dashboard.php");
        exit();
    } else {
        // Handle error
        echo "Error updating notification: " . $admin_conn->error;
    }
}
?>
