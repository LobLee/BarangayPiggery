<?php 
include ('../sidebar/owner_sidebar.php');
include('../connection/conn.php'); // Include database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Barangay Piggery Monitoring System</title>

    <!-- Custom fonts for this template-->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../template/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body>

    <!-- Page Content -->
    <div id="content-wrapper" style="flex: 1; padding: 20px;">

        <h2>All Notifications</h2>

        <?php
        // Assuming user_id is stored in the session
        session_start();
        $user_id = $_SESSION['user_id']; 

        // Fetch all notifications for the user
        $query_notifications = "SELECT id, title, message, module, created_at, is_read 
                                FROM brgy_piggery.notifications 
                                WHERE owner_id = ? 
                                ORDER BY created_at DESC";
        $stmt_notifications = $admin_conn->prepare($query_notifications);
        $stmt_notifications->bind_param("i", $user_id);
        $stmt_notifications->execute();
        $result_notifications = $stmt_notifications->get_result();

        // Display notifications
        if ($result_notifications->num_rows > 0) {
            while ($row = $result_notifications->fetch_assoc()) {
                ?>
                <div class="notification-item" style="border: 1px solid #ddd; margin-bottom: 15px; padding: 15px; border-radius: 5px;">
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </div>
                    <div style="color: #777; font-size: 14px;">
                        <i><?php echo date('F d, Y', strtotime($row['created_at'])); ?></i> | 
                        <span style="color: <?php echo ($row['is_read'] == 0) ? 'red' : 'green'; ?>;">
                            <?php echo ($row['is_read'] == 0) ? 'Unread' : 'Read'; ?>
                        </span>
                    </div>
                    <div style="margin-top: 10px;">
                        <?php echo htmlspecialchars($row['message']); ?>
                    </div>
                    <div style="margin-top: 10px; font-size: 12px; color: #555;">
                        <i>Module: <?php echo htmlspecialchars($row['module']); ?></i>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No notifications found.</p>";
        }
        ?>
    </div>
    <!-- End of Page Content -->

</body>

</html>
