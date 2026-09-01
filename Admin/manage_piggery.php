<?php
ob_start(); // Start output buffering

// Include the admin sidebar and database connections
include('../sidebar/admin_sidebar.php');
include('../connection/conn.php');
include('../connection/user_conn.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in.";
    exit(); // Exit if the user is not logged in
}

// Create a function to log activities
function logActivity($admin_conn, $user_id, $action_type, $activity_description, $module) {
    $action_type = $admin_conn->real_escape_string($action_type);
    $activity_description = $admin_conn->real_escape_string($activity_description);
    $module = $admin_conn->real_escape_string($module);

    // Insert log into the activity_logs table
    $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module) 
                  VALUES ('$user_id', '$action_type', '$activity_description', '$module')";

    if (!$admin_conn->query($log_query)) {
        error_log("Failed to log activity: " . $admin_conn->error);
    }
}

// Create a function to send notifications
function sendNotification($admin_conn, $title, $message, $recipient_id, $module) {
    $title = $admin_conn->real_escape_string($title);
    $message = $admin_conn->real_escape_string($message);
    $module = $admin_conn->real_escape_string($module);

    // Retrieve the user ID of the registered user with the role 'owner'
    $owner_query = "SELECT user_id FROM registered_users WHERE role = 'owner' LIMIT 1"; // Assuming there is only one owner or you want the first one
    $owner_result = $admin_conn->query($owner_query);

    if ($owner_result && $owner_result->num_rows > 0) {
        $owner_data = $owner_result->fetch_assoc();
        $owner_id = $owner_data['user_id'];

        // Insert notification with the owner_id
        $notification_query = "INSERT INTO notifications (title, message, recipient_id, module, owner_id) 
                               VALUES ('$title', '$message', '$recipient_id', '$module', '$owner_id')";

        if (!$admin_conn->query($notification_query)) {
            error_log("Failed to send notification: " . $admin_conn->error);
        }
    } else {
        error_log("Owner not found in the database.");
    }
}

// Fetch all piggeries data from the `user_piggery` database without filtering by user_id
$query = "SELECT * FROM brgy_user_piggery.piggeries ORDER BY created_at DESC";
$result = $owner_conn->query($query);

// Check if the form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_piggery']) && isset($_POST['id'])) {
        // Delete an existing piggery
        $piggery_id = intval($_POST['id']);
        $sql = "DELETE FROM brgy_user_piggery.piggeries WHERE id=$piggery_id";

        if ($admin_conn->query($sql) === TRUE) {
            $_SESSION['toast_message'] = "Piggery deleted successfully.";

            // Log the deletion activity
            $description = "Deleted piggery with ID $piggery_id.";
            logActivity($admin_conn, $_SESSION['user_id'], 'Delete', $description, 'Piggery Management');

            // Send a notification to the owner
            sendNotification($admin_conn, 'Piggery Deleted', $description, $_SESSION['user_id'], 'Piggery Management');
        } else {
            $_SESSION['toast_message'] = "Error deleting record: " . $admin_conn->error;
        }
    } elseif (isset($_POST['approve_piggery']) && isset($_POST['id'])) {
        // Approve pending piggery
        $id = intval($_POST['id']);
        $status = 'Approved';

        // Update status in `user_piggery` database
        $update_status_query = "UPDATE brgy_user_piggery.piggeries SET status='$status' WHERE id=$id";

        if ($admin_conn->query($update_status_query) === TRUE) {
            // Retrieve approved piggery details
            $piggery_data_query = "SELECT * FROM brgy_user_piggery.piggeries WHERE id = $id";
            $piggery_data_result = $admin_conn->query($piggery_data_query);

            if ($piggery_data_result->num_rows > 0) {
                $piggery_data = $piggery_data_result->fetch_assoc();

                // Insert approved piggery into `manage_piggery` table in the `admin` database
                $insert_query = "INSERT INTO manage_piggery 
                                (piggery_id, owner_first_name, owner_middle_name, owner_last_name, piggery_name, location, num_of_pigs, health_status, last_inspection_date, next_inspection_date, compliance_status, status, notes, created_at, updated_at)
                                VALUES (
                                    '{$piggery_data['piggery_id']}', 
                                    '{$piggery_data['owner_first_name']}', 
                                    '{$piggery_data['owner_middle_name']}', 
                                    '{$piggery_data['owner_last_name']}', 
                                    '{$piggery_data['piggery_name']}', 
                                    '{$piggery_data['location']}', 
                                    {$piggery_data['num_of_pigs']}, 
                                    '{$piggery_data['health_status']}', 
                                    '{$piggery_data['last_inspection_date']}', 
                                    '{$piggery_data['next_inspection_date']}', 
                                    '{$piggery_data['compliance_status']}', 
                                    '$status', 
                                    '{$piggery_data['notes']}', 
                                    '{$piggery_data['created_at']}', 
                                    NOW()
                                )";

                if ($admin_conn->query($insert_query) === TRUE) {
                    $_SESSION['toast_message'] = "Piggery approved and added to management.";

                    // Log the approval activity
                    $description = "Approved piggery with ID $id.";
                    logActivity($admin_conn, $_SESSION['user_id'], 'Approve', $description, 'Piggery Management');

                    // Send a notification to the owner
                    sendNotification($admin_conn, 'Piggery Approved', $description, $_SESSION['user_id'], 'Piggery Management');
                } else {
                    $_SESSION['toast_message'] = "Error inserting record into manage_piggery: " . $admin_conn->error;
                }
            }
        } else {
            $_SESSION['toast_message'] = "Error updating status: " . $admin_conn->error;
        }
    } elseif (isset($_POST['add_piggery'])) {
        // Add a new piggery
        $owner_first_name = $admin_conn->real_escape_string($_POST['owner_first_name']);
        $owner_middle_name = $admin_conn->real_escape_string($_POST['owner_middle_name']);
        $owner_last_name = $admin_conn->real_escape_string($_POST['owner_last_name']);
        $piggery_name = $admin_conn->real_escape_string($_POST['piggery_name']);
        $location = $admin_conn->real_escape_string($_POST['location']);
        $num_of_pigs = intval($_POST['num_of_pigs']);
        $health_status = $admin_conn->real_escape_string($_POST['health_status']);
        $last_inspection_date = $admin_conn->real_escape_string($_POST['last_inspection_date']);
        $next_inspection_date = $admin_conn->real_escape_string($_POST['next_inspection_date']);
        $compliance_status = $admin_conn->real_escape_string($_POST['compliance_status']);
        $status = $admin_conn->real_escape_string($_POST['status']);
        $notes = $admin_conn->real_escape_string($_POST['notes']);

        // Insert new piggery record into the `manage_piggery` table
        $insert_query = "INSERT INTO brgy_piggery.manage_piggery 
                        (owner_first_name, owner_middle_name, owner_last_name, piggery_name, location, num_of_pigs, health_status, last_inspection_date, next_inspection_date, compliance_status, status, notes, created_at, updated_at)
                        VALUES ('$owner_first_name', '$owner_middle_name', '$owner_last_name', '$piggery_name', '$location', $num_of_pigs, '$health_status', '$last_inspection_date', '$next_inspection_date', '$compliance_status', '$status', '$notes', NOW(), NOW())";

        if ($admin_conn->query($insert_query) === TRUE) {
            $_SESSION['toast_message'] = "Piggery added successfully.";

            // Log the add activity
            $description = "Added new piggery: $piggery_name at $location, with $num_of_pigs pigs.";
            logActivity($admin_conn, $_SESSION['user_id'], 'Add', $description, 'Piggery Management');

            // Send a notification to the owner
            sendNotification($admin_conn, 'New Piggery Added', $description, $_SESSION['user_id'], 'Piggery Management');
        } else {
            $_SESSION['toast_message'] = "Error adding piggery: " . $admin_conn->error;
        }
    }

    // Redirect to the same page to display the toast message
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$admin_conn->close();
ob_end_flush();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>All Piggeries Details - Barangay Piggery Monitoring System</title>

    <!-- Custom fonts and styles -->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
    <style>
        /* Custom modal width */
        .custom-modal-width {
            max-width: 90%;
            /* Set the width to 90% of the screen */
            width: 800px;
            /* Alternatively, set to a fixed width (e.g., 800px) */
        }
    </style>
</head>

<body>
    <div class="container-fluid">
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
        <h2 class = "text-center">All Piggeries Details</h2>

         <!-- Search -->
         <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">

        <!-- Add Button -->
        <button class="btn btn-primary mb-3" style="margin-top: -80px" data-toggle="modal" data-target="#addPiggeryModal">Add Piggery</button>

        <table class="table table-bordered table-striped" style = "margin-top:-32px">
            <thead class ="text-center"  style="font-size: small; font-family: bolder; color:white; background-color:black">
                <tr>
                    <th>#</th>
                    <th>Piggery ID</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Piggery Name</th>
                    <th>Location</th>
                    <th>Number of Pigs</th>
                    <th>Health Status</th>
                    <th>Compliance Status</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th> <!-- Added Actions column -->
                </tr>
            </thead>
            <tbody class = "text-center" style="font-size: small">
                <?php
                $counter = 1; // Initialize counter
                if ($result->num_rows > 0): ?>
                    <?php while ($piggery = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td> <!-- Display counter and increment -->
                            <td><?php echo $piggery['piggery_id']; ?></td>
                            <td><?php echo $piggery['owner_first_name']; ?></td>
                            <td><?php echo $piggery['owner_middle_name']; ?></td>
                            <td><?php echo $piggery['owner_last_name']; ?></td>
                            <td><?php echo $piggery['piggery_name']; ?></td>
                            <td><?php echo $piggery['location']; ?></td>
                            <td><?php echo $piggery['num_of_pigs']; ?></td>
                            <td><?php echo $piggery['health_status']; ?></td>
                            <td><?php echo $piggery['compliance_status']; ?></td>
                            <td><?php echo $piggery['status']; ?></td>
                            <td><?php echo $piggery['created_at']; ?></td>
                            <td>
                                <?php if ($piggery['status'] == 'Pending'): ?>
                                    <!-- Approve Button for Pending Status -->
                                    <form method="post" action="">
                                        <input type="hidden" name="id" value="<?php echo $piggery['id']; ?>">
                                        <button type="submit" name="approve_piggery" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                <?php endif; ?>
                                <!-- Action Dropdown for each row -->
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#viewModal<?php echo $piggery['piggery_id']; ?>">View</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editModal<?php echo $piggery['piggery_id']; ?>">Edit</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteModal<?php echo $piggery['id']; ?>">Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo $piggery['piggery_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel<?php echo $piggery['piggery_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog custom-modal-width" role="document"> <!-- Added custom class -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewModalLabel<?php echo $piggery['piggery_id']; ?>">View Piggery Details</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Display Piggery details -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Piggery ID:</strong> <?php echo $piggery['piggery_id']; ?></p>
                                                <p><strong>Owner First Name:</strong> <?php echo $piggery['owner_first_name']; ?></p>
                                                <p><strong>Owner Middle Name:</strong> <?php echo $piggery['owner_middle_name']; ?></p>
                                                <p><strong>Owner Last Name:</strong> <?php echo $piggery['owner_last_name']; ?></p>
                                                <p><strong>Piggery Name:</strong> <?php echo $piggery['piggery_name']; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Location:</strong> <?php echo $piggery['location']; ?></p>
                                                <p><strong>Health Status:</strong> <?php echo $piggery['health_status']; ?></p>
                                                <p><strong>Number of Pigs:</strong> <?php echo $piggery['num_of_pigs']; ?></p>
                                                <p><strong>Last Inspection Date:</strong> <?php echo $piggery['last_inspection_date']; ?></p>
                                                <p><strong>Next Inspection Date:</strong> <?php echo $piggery['next_inspection_date']; ?></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Compliance Status:</strong> <?php echo $piggery['compliance_status']; ?></p>
                                                <p><strong>Status:</strong> <?php echo $piggery['status']; ?></p>
                                                <p><strong>Created At:</strong> <?php echo $piggery['created_at']; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Notes:</strong> <?php echo $piggery['notes']; ?></p>
                                                <p><strong>Updated At:</strong> <?php echo $piggery['updated_at']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $piggery['piggery_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $piggery['piggery_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $piggery['piggery_id']; ?>">Edit Piggery Details</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                        <div class="modal-body">
                                            <!-- Hidden field to pass the piggery_id for identifying the record -->
                                            <input type="hidden" name="piggery_id" value="<?php echo $piggery['piggery_id']; ?>">

                                            <!-- Row 1 -->
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="first_name">First Name</label>
                                                    <input type="text" class="form-control" name="owner_first_name" value="<?php echo $piggery['owner_first_name']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="owner_middle_name">Middle Name</label>
                                                    <input type="text" class="form-control" name="owner_middle_name" value="<?php echo $piggery['owner_middle_name']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="owner_last_name">Last Name</label>
                                                    <input type="text" class="form-control" name="owner_last_name" value="<?php echo $piggery['owner_last_name']; ?>" required>
                                                </div>
                                            </div>

                                            <!-- Row 2 -->
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="piggery_name">Piggery Name</label>
                                                    <input type="text" class="form-control" name="piggery_name" value="<?php echo $piggery['piggery_name']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="num_of_pigs">Number of Pigs</label>
                                                    <input type="number" class="form-control" name="num_of_pigs" value="<?php echo $piggery['num_of_pigs']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="location">Location</label>
                                                    <input type="text" class="form-control" name="location" value="<?php echo $piggery['location']; ?>" required>
                                                </div>
                                            </div>

                                            <!-- Row 3 -->
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="health_status">Health Status</label>
                                                    <input type="text" class="form-control" name="health_status" value="<?php echo $piggery['health_status']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="last_inspection_date">Last Inspection Date</label>
                                                    <input type="date" class="form-control" name="last_inspection_date" value="<?php echo $piggery['last_inspection_date']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="next_inspection_date">Next Inspection Date</label>
                                                    <input type="date" class="form-control" name="next_inspection_date" value="<?php echo $piggery['next_inspection_date']; ?>" required>
                                                </div>
                                            </div>

                                            <!-- Row 4 -->
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="compliance_status">Compliance Status</label>
                                                    <input type="text" class="form-control" name="compliance_status" value="<?php echo $piggery['compliance_status']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="status">Compliance Status</label>
                                                    <input type="text" class="form-control" name="status" value="<?php echo $piggery['status']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <label for="notes">Notes</label>
                                                    <textarea class="form-control" name="notes" rows="3" required><?php echo $piggery['notes']; ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" name="edit_piggery">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $piggery['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $piggery['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $piggery['id']; ?>">Confirm Deletion</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete this piggery? This action cannot be undone.
                                    </div>
                                    <div class="modal-footer">
                                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                            <input type="hidden" name="id" value="<?php echo $piggery['id']; ?>">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" name="delete_piggery" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Display a "No results found" row if no data is returned -->
                    <tr>
                        <td colspan="13" class="text-center" style="color:red">No results found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include('../footer.php') ?>
    <!-- Add Piggery Modal -->
    <div class="modal fade" id="addPiggeryModal" tabindex="-1" role="dialog" aria-labelledby="addPiggeryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPiggeryModalLabel">Add New Piggery</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="manage_piggery.php" method="POST">
                    <div class="modal-body">
                        <!-- Row 1 with three columns for owner's name -->
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="owner_first_name">First Name</label>
                                <input type="text" class="form-control" id="owner_first_name" name="owner_first_name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="owner_middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="owner_middle_name" name="owner_middle_name">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="owner_last_name">Last Name</label>
                                <input type="text" class="form-control" id="owner_last_name" name="owner_last_name" required>
                            </div>
                        </div>

                        <!-- Row 2 with two columns for piggery name and location -->
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="piggery_name">Piggery Name</label>
                                <input type="text" class="form-control" id="piggery_name" name="piggery_name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="location">Address</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="health_status">Health Status</label>
                                <input type="text" class="form-control" name="health_status" value="Healthy" required>
                            </div>
                        </div>

                        <!-- Row 3 with two columns for number of pigs and health status -->
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="num_of_pigs">Number of Pigs</label>
                                <input type="number" class="form-control" id="num_of_pigs" name="num_of_pigs" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="last_inspection_date">Inspection Date</label>
                                <input type="date" class="form-control" id="last_inspection_date" name="last_inspection_date" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="next_inspection_date">Inspection Date</label>
                                <input type="date" class="form-control" id="next_inspection_date" name="next_inspection_date" required>
                            </div>
                        </div>

                        <!-- Row 4 with two columns for compliance status and status -->
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="compliance_status">Compliance Status</label>
                                <input type="text" class="form-control" name="compliance_status" value="Compliant" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="status">Status</label>
                                <input type="text" class="form-control" name="status" value="Pending" required>
                            </div>
                        </div>

                        <!-- Row 5 with one column for notes -->
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Piggery</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.toast').toast('show');
        });
    </script>

<script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let table = document.querySelector('table');
            let rows = table.getElementsByTagName('tr'); // Get all rows
            let noResults = true; // Flag to check if any results are found

            // Loop through all rows (excluding the first one which is the header)
            for (let i = 1; i < rows.length; i++) {
                let columns = rows[i].getElementsByTagName('td'); // Get all columns in the row
                let found = false;

                // Loop through all columns (you can modify this to target specific columns, e.g., first name, last name)
                for (let j = 0; j < columns.length; j++) {
                    let columnText = columns[j].textContent || columns[j].innerText;
                    if (columnText.toLowerCase().includes(input)) {
                        found = true;
                        break; // No need to continue if a match is found
                    }
                }

                // Show or hide the row based on whether the search term was found in any column
                if (found) {
                    rows[i].style.display = '';
                    noResults = false; // If we find a match, set the flag to false
                } else {
                    rows[i].style.display = 'none';
                }
            }

            // If no results are found, display the "No results found" message
            let noResultsRow = document.querySelector('.no-results');
            if (noResults && !noResultsRow) {
                // If there is no existing "No results found" row, create one
                let tr = document.createElement('tr');
                tr.classList.add('no-results');
                let td = document.createElement('td');
                td.setAttribute('colspan', '13'); // Adjust the colspan based on your table
                td.style.color = 'red';
                td.textContent = 'No results found';
                tr.appendChild(td);
                table.querySelector('tbody').appendChild(tr);
            } else if (!noResults && noResultsRow) {
                // If there are results, remove the "No results found" row
                noResultsRow.remove();
            }
        });
    </script>

</body>

</html>