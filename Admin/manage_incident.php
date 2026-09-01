<?php
ob_start();

// Include the admin sidebar and database connections
include('../sidebar/admin_sidebar.php');
include('../connection/conn.php'); // Connection for the admin's database
include('../connection/user_conn.php'); // Connection for the brgy_user_piggery database

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id']; // Get the user_id from the session

// Function to log activities
function logActivity($admin_conn, $user_id, $action_type, $activity_description, $module) {
    $action_type = $admin_conn->real_escape_string($action_type);
    $activity_description = $admin_conn->real_escape_string($activity_description);
    $module = $admin_conn->real_escape_string($module);

    // Insert log into the activity_logs table
    $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module) 
                  VALUES ('$user_id', '$action_type', '$activity_description', '$module')";

    if (!$admin_conn->query($log_query)) {
        // Optionally log or handle any errors here
        error_log("Failed to log activity: " . $admin_conn->error);
    }
}

// Fetch all incident reports from brgy_user_piggery database
$query = "SELECT * FROM brgy_user_piggery.incident_reports ORDER BY created_at DESC";
$result = $owner_conn->query($query);

// Function to edit an incident
function editIncident($owner_conn, $admin_conn, $user_id) {
    if (isset($_POST['edit_incident'])) {
        $incident_id = intval($_POST['incident_id']);
        $incident_type = $owner_conn->real_escape_string($_POST['incident_type']);
        $description = $owner_conn->real_escape_string($_POST['description']);
        $status = $owner_conn->real_escape_string($_POST['status']);

        // Update the incident report in the brgy_user_piggery database
        $update_query = "UPDATE brgy_user_piggery.incident_reports SET incident_type='$incident_type', description='$description', status='$status', updated_at=NOW() WHERE incident_id='$incident_id'";

        if ($owner_conn->query($update_query) === TRUE) {
            $_SESSION['toast_message'] = "Incident updated successfully.";
            
            // Log the activity
            $description = "Updated incident ID $incident_id: $incident_type, Status: $status.";
            logActivity($admin_conn, $user_id, 'Edit', $description, 'Incident Management');
            
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['toast_message'] = "Error updating incident: " . $owner_conn->error;
        }
    }
}

// Function to delete an incident
function deleteIncident($owner_conn, $admin_conn, $user_id) {
    if (isset($_POST['delete_incident'])) {
        $incident_id = intval($_POST['incident_id']);

        // Delete the incident from the brgy_user_piggery database
        $delete_query = "DELETE FROM brgy_user_piggery.incident_reports WHERE incident_id='$incident_id'";

        if ($owner_conn->query($delete_query) === TRUE) {
            $_SESSION['toast_message'] = "Incident deleted successfully.";
            
            // Log the activity
            $description = "Deleted incident ID $incident_id.";
            logActivity($admin_conn, $user_id, 'Delete', $description, 'Incident Management');
            
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['toast_message'] = "Error deleting incident: " . $owner_conn->error;
        }
    }
}

// Check if form data is submitted for approving, editing, or deleting an incident
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_incident'])) {
        // Existing approve logic
        $incident_id = intval($_POST['incident_id']);
        $inspector_id = intval($_POST['inspector_id']);
        $incident_query = "SELECT * FROM brgy_user_piggery.incident_reports WHERE incident_id = '$incident_id'";
        $incident_result = $owner_conn->query($incident_query);

        if ($incident_result->num_rows > 0) {
            $incident_data = $incident_result->fetch_assoc();
            $piggery_id = $admin_conn->real_escape_string($incident_data['piggery_id']);
            $report_date = $admin_conn->real_escape_string($incident_data['report_date']);
            $reported_by = $admin_conn->real_escape_string($incident_data['reported_by']);
            $incident_type = $admin_conn->real_escape_string($incident_data['incident_type']);
            $description = $admin_conn->real_escape_string($incident_data['description']);
            $status = 'Approved'; // Set status as 'Approved'
            $action_taken = $admin_conn->real_escape_string($incident_data['action_taken']);
            $resolved_by = $admin_conn->real_escape_string($incident_data['resolved_by']);
            $resolved_date = $admin_conn->real_escape_string($incident_data['resolved_date']);

            // Insert the approved incident into manage_incidents
            $insert_query = "INSERT INTO brgy_piggery.manage_incidents 
                            (piggery_id, report_date, reported_by, incident_type, description, status, action_taken, resolved_by, resolved_date, created_at, updated_at, inspector_id)
                            VALUES ('$piggery_id', '$report_date', '$reported_by', '$incident_type', '$description', '$status', '$action_taken', '$resolved_by', '$resolved_date', NOW(), NOW(), '$inspector_id')";

            if ($admin_conn->query($insert_query) === TRUE) {
                // Update the incident report in brgy_user_piggery to reflect 'Approved' status
                $update_query = "UPDATE brgy_user_piggery.incident_reports SET status = 'Approved' WHERE incident_id = '$incident_id'";

                if ($owner_conn->query($update_query) === TRUE) {
                    // Update the incident_reports table with action_taken, resolved_by, and resolved_date
                    $update_reports_query = "UPDATE brgy_user_piggery.incident_reports
                                              SET action_taken = '$action_taken', resolved_by = '$resolved_by', resolved_date = NOW()
                                              WHERE incident_id = '$incident_id' AND status = 'Approved'";

                    if ($owner_conn->query($update_reports_query) === TRUE) {
                        $_SESSION['toast_message'] = "Incident approved, status updated, and action details saved successfully.";
                        
                        // Log the activity
                        $description = "Approved incident ID $incident_id, Status: Approved.";
                        logActivity($admin_conn, $user_id, 'Approve', $description, 'Incident Management');
                        
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $_SESSION['toast_message'] = "Error updating action details in incident_reports: " . $owner_conn->error;
                    }
                } else {
                    $_SESSION['toast_message'] = "Error updating incident status in incident_reports: " . $owner_conn->error;
                }
            } else {
                $_SESSION['toast_message'] = "Error inserting approved incident into manage_incidents: " . $admin_conn->error;
            }
        } else {
            $_SESSION['toast_message'] = "Error: Incident data not found.";
        }
    }

    // Call edit and delete functions
    editIncident($owner_conn, $admin_conn, $user_id);
    deleteIncident($owner_conn, $admin_conn, $user_id);
}

$admin_conn->close();
$owner_conn->close();
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
    <title>Incident Reports - Barangay Piggery Monitoring System</title>

    <!-- Custom fonts and styles -->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
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
        <h2 class = "text-center">Incident Reports</h2>

             <!-- Search -->
     <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">

        <!-- Incident Reports Table -->
        <table class="table table-bordered table-striped" style = "margin-top:-10px">
            <thead class = "text-center"  style="font-size: small; font-family: bolder; color:white; background-color:black">
                <tr>
                    <th>#</th>
                    <th>Incident ID</th>
                    <th>Piggery ID</th>
                    <th>Report Date</th>
                    <th>Reported By</th>
                    <th>Incident Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class = "text-center" style="font-size: small">
                <?php
                $counter = 1; // Initialize counter
                if ($result->num_rows > 0): ?>
                    <?php while ($incident = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $incident['incident_id']; ?></td>
                            <td><?php echo $incident['piggery_id']; ?></td>
                            <td><?php echo $incident['report_date']; ?></td>
                            <td><?php echo $incident['reported_by']; ?></td>
                            <td><?php echo $incident['incident_type']; ?></td>
                            <td><?php echo $incident['description']; ?></td>
                            <td><?php echo $incident['status']; ?></td>
                            <td><?php echo $incident['created_at']; ?></td>
                            <td>
                                <!-- Action Dropdown for each row -->
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#viewModal<?php echo $incident['incident_id']; ?>">View</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editModal<?php echo $incident['incident_id']; ?>">Edit</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteModal<?php echo $incident['incident_id']; ?>">Delete</a>

                                        <!-- Approve Action -->
                                        <?php if ($incident['status'] == 'Pending'): ?>
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#approveModal<?php echo $incident['incident_id']; ?>">Approve</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo $incident['incident_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel<?php echo $incident['incident_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewModalLabel<?php echo $incident['incident_id']; ?>">View Incident Details</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Incident ID:</strong> <?php echo $incident['incident_id']; ?></p>
                                        <p><strong>Piggery ID:</strong> <?php echo $incident['piggery_id']; ?></p>
                                        <p><strong>Report Date:</strong> <?php echo $incident['report_date']; ?></p>
                                        <p><strong>Reported By:</strong> <?php echo $incident['reported_by']; ?></p>
                                        <p><strong>Incident Type:</strong> <?php echo $incident['incident_type']; ?></p>
                                        <p><strong>Description:</strong> <?php echo $incident['description']; ?></p>
                                        <p><strong>Status:</strong> <?php echo $incident['status']; ?></p>
                                        <p><strong>Action Taken:</strong> <?php echo $incident['action_taken']; ?></p>
                                        <p><strong>Created At:</strong> <?php echo $incident['created_at']; ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $incident['incident_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $incident['incident_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $incident['incident_id']; ?>">Edit Incident</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="manage_incident.php" method="POST">
                                        <input type="hidden" name="incident_id" value="<?php echo $incident['incident_id']; ?>">
                                        
                                        <div class="modal-body">
                                            <!-- First Row -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="piggery_id">Piggery ID</label>
                                                        <input type="text" class="form-control" name="piggery_id" value="<?php echo $incident['piggery_id']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="report_date">Report Date</label>
                                                        <input type="date" class="form-control" name="report_date" value="<?php echo $incident['report_date']; ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Second Row -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reported_by">Reported By</label>
                                                        <input type="text" class="form-control" name="reported_by" value="<?php echo $incident['reported_by']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="incident_type">Incident Type</label>
                                                        <input type="text" class="form-control" name="incident_type" value="<?php echo $incident['incident_type']; ?>" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Third Row (Full-width Description) -->
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="description">Description</label>
                                                        <textarea class="form-control" name="description" required><?php echo $incident['description']; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Fourth Row (Status Selection) -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="status">Status</label>
                                                        <select class="form-control" name="status" required>
                                                            <option value="Pending" <?php if ($incident['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                                            <option value="Approved" <?php if ($incident['status'] == 'Approved') echo 'selected'; ?>>Approved</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary" name="edit_incident">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <!-- Approve Modal -->
                        <div class="modal fade" id="approveModal<?php echo $incident['incident_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel<?php echo $incident['incident_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="approveModalLabel<?php echo $incident['incident_id']; ?>">Approve Incident</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="manage_incident.php" method="POST">
                                        <input type="hidden" name="incident_id" value="<?php echo $incident['incident_id']; ?>">

                                        <div class="modal-body">
                                            <label for="inspector_id">Assign Inspector</label>
                                            <select class="form-control" name="inspector_id" required>
                                                <option value="">Select Inspector</option>
                                                <?php
                                                include('../connection/conn.php'); // Connection for the admin's database
                                                // Fetch available inspectors from the database
                                                $inspectors_query = "SELECT user_id, first_name, last_name FROM registered_users WHERE role = 'inspector'";
                                                $inspectors_result = $admin_conn->query($inspectors_query);

                                                if ($inspectors_result->num_rows > 0) {
                                                    while ($row = $inspectors_result->fetch_assoc()) {
                                                        echo "<option value='" . $row['user_id'] . "'>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No inspectors available</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success" name="approve_incident">Approve and Assign Inspector</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>



                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $incident['incident_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $incident['incident_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $incident['incident_id']; ?>">Delete Incident</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="manage_incident.php" method="POST">
                                        <input type="hidden" name="incident_id" value="<?php echo $incident['incident_id']; ?>">
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this incident?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger" name="delete_incident">Delete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center" style="color:red">No incidents found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php include('../footer.php') ?>

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