<?php
ob_start();
include('../connection/conn.php'); // Main database connection
include('../sidebar/admin_sidebar.php');

// Handle delete all activity logs
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete_all_logs'])) {
    // Delete all records from activity_logs
    $delete_query = "DELETE FROM activity_logs";
    if ($admin_conn->query($delete_query) === TRUE) {
        $_SESSION['toast_message'] = "All activity logs deleted successfully.";
    } else {
        $_SESSION['toast_message'] = "Error deleting logs: " . $admin_conn->error;
    }
    // Redirect to refresh the page and display the toast message
    header("Location: activity_log.php");
    exit();
}

// Fetch all activity logs from the database
$query = "SELECT * FROM activity_logs ORDER BY created_at DESC";
$result = $admin_conn->query($query);

// Close database connection
$admin_conn->close();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Barangay Piggery Monitoring System - Admin Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">

</head>

<body>

    <div class="container-fluid">
        <!-- Page Heading -->
        <h1 class="text-center">Activity Logs</h1><br>

        <!-- Toast Notification -->
        <?php if (isset($_SESSION['toast_message'])): ?>
            <div class="toast-container position-fixed mt-5 w-100 d-flex justify-content-center" style="top: 56px; z-index: 1050;">
                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000" style="min-width: 300px;">
                    <div class="toast-header">
                        <strong class="mr-auto">Notification</strong>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        <?php echo $_SESSION['toast_message']; ?>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['toast_message']); ?>
        <?php endif; ?>

        <!-- Delete All Logs Button -->
        <button class="btn btn-danger" style="margin-top: -50px;" data-toggle="modal" data-target="#deleteModal">Delete All Logs</button>

        <!-- Table for activity logs -->
        <table class="table table-bordered table-striped" style="margin-top: -10px;">
            <thead class="text-center" style="font-size: small; font-family: bolder; color:white; background-color:black">
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Action Type</th>
                    <th>Activity Description</th>
                    <th>Module</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . $counter++ . "</td>
                            <td>" . htmlspecialchars($row['user_id']) . "</td>
                            <td>" . htmlspecialchars($row['action_type']) . "</td>
                            <td>" . htmlspecialchars($row['activity_description']) . "</td>
                            <td>" . htmlspecialchars($row['module']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                          </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center' style = 'color:red'>No activity logs found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete all activity logs? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" action="">
                        <button type="submit" name="confirm_delete_all_logs" class="btn btn-danger">Delete All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('../footer.php') ?>
    <script>
        $(document).ready(function() {
            $('.toast').toast('show');
        });
    </script>

</body>

</html>