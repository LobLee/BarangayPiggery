<?php
// Start session and include database connection
include('connection/conn.php');
session_start();

// Ensure user_id is set in the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Log the logout action in activity_logs
    $action_type = "Logout";
    $activity_description = "User logged out.";
    $module = "Authentication";
    $timestamp = date("Y-m-d H:i:s");

    $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module, created_at) 
                  VALUES ('$user_id', '$action_type', '$activity_description', '$module', '$timestamp')";

    if (!$admin_conn->query($log_query)) {
        echo "Error logging logout action: " . $admin_conn->error;
    }
}

// Destroy session and redirect to login page
session_destroy();
header("Location: index.php");
exit();
?>
