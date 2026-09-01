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

// Fetch health monitoring data for all users from the brgy_user_piggery database
$query = "SELECT * FROM brgy_user_piggery.health_monitoring ORDER BY created_at DESC";
$result = $owner_conn->query($query);

// Function to log activity in the specified format
function logActivity($current_user_id, $action_type, $activity_description, $module, $admin_conn) {
    // Sanitize input to prevent SQL injection
    $current_user_id = intval($current_user_id); // Ensure it's an integer
    $action_type = $admin_conn->real_escape_string($action_type); // Escape special characters
    $activity_description = $admin_conn->real_escape_string($activity_description); // Escape special characters
    $module = $admin_conn->real_escape_string($module); // Escape special characters

    // Insert into activity_logs table
    $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module) 
                  VALUES ('$current_user_id', '$action_type', '$activity_description', '$module')";
    $admin_conn->query($log_query);
}

// Check if the form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_monitoring']) && isset($_POST['monitoring_id'])) {
        // Delete health monitoring record
        $monitoring_id = intval($_POST['monitoring_id']);
        $sql = "DELETE FROM brgy_user_piggery.health_monitoring WHERE monitoring_id = $monitoring_id";

        if ($admin_conn->query($sql) === TRUE) {
            $_SESSION['toast_message'] = "Health monitoring record deleted successfully.";
            logActivity($_SESSION['user_id'], 'Delete', "Deleted health monitoring record with ID: $monitoring_id", 'Health Monitoring', $admin_conn);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['toast_message'] = "Error deleting record: " . $admin_conn->error;
        }
    } elseif (isset($_POST['approve_monitoring']) && isset($_POST['monitoring_id'])) {
        // Approve health monitoring record and insert into `manage_piggery`
        $monitoring_id = intval($_POST['monitoring_id']);
        $status = 'Approved';

        // Fetch the health monitoring record
        $select_query = "SELECT * FROM brgy_user_piggery.health_monitoring WHERE monitoring_id = $monitoring_id";
        $select_result = $admin_conn->query($select_query);

        if ($select_result->num_rows > 0) {
            $monitoring_data = $select_result->fetch_assoc();

            // Insert into `manage_piggery` table
            $insert_query = "INSERT INTO brgy_piggery.manage_health 
                             (monitoring_id, piggery_id, user_id, check_date, number_of_sick, number_of_healthy, treatments_given, vaccinations, notes, status, created_at, updated_at)
                             VALUES (
                                '{$monitoring_data['monitoring_id']}',
                                '{$monitoring_data['piggery_id']}',
                                '{$monitoring_data['user_id']}',
                                '{$monitoring_data['check_date']}',
                                '{$monitoring_data['number_of_sick']}',
                                '{$monitoring_data['number_of_healthy']}',
                                '{$monitoring_data['treatments_given']}',
                                '{$monitoring_data['vaccinations']}',
                                '{$monitoring_data['notes']}',
                                '$status',
                                '{$monitoring_data['created_at']}',
                                NOW()
                             )";

            if ($admin_conn->query($insert_query) === TRUE) {
                // Update the status in the health_monitoring table
                $update_status_query = "UPDATE brgy_user_piggery.health_monitoring SET status='$status' WHERE monitoring_id=$monitoring_id";

                if ($admin_conn->query($update_status_query) === TRUE) {
                    $_SESSION['toast_message'] = "Health monitoring record approved successfully.";
                    logActivity($_SESSION['user_id'], 'Approve', "Approved health monitoring record with ID: $monitoring_id", 'Health Monitoring', $admin_conn);
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $_SESSION['toast_message'] = "Error updating status: " . $admin_conn->error;
                }
            } else {
                $_SESSION['toast_message'] = "Error inserting record into manage_table: " . $admin_conn->error;
            }
        }
    } elseif (isset($_POST['edit_monitoring']) && isset($_POST['monitoring_id'])) {
        // Edit health monitoring record
        $monitoring_id = intval($_POST['monitoring_id']);
        $check_date = $admin_conn->real_escape_string($_POST['check_date']);
        $number_of_sick = intval($_POST['number_of_sick']);
        $number_of_healthy = intval($_POST['number_of_healthy']);
        $treatments_given = $admin_conn->real_escape_string($_POST['treatments_given']);
        $vaccinations = $admin_conn->real_escape_string($_POST['vaccinations']);
        $notes = $admin_conn->real_escape_string($_POST['notes']);

        // Update the health monitoring record in the database
        $update_query = "UPDATE brgy_user_piggery.health_monitoring 
                         SET check_date='$check_date', number_of_sick=$number_of_sick, number_of_healthy=$number_of_healthy, 
                             treatments_given='$treatments_given', vaccinations='$vaccinations', notes='$notes', updated_at=NOW()
                         WHERE monitoring_id=$monitoring_id";

        if ($admin_conn->query($update_query) === TRUE) {
            $_SESSION['toast_message'] = "Health monitoring record updated successfully.";
            logActivity($_SESSION['user_id'], 'Edit', "Edited health monitoring record with ID: $monitoring_id", 'Health Monitoring', $admin_conn);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['toast_message'] = "Error updating record: " . $admin_conn->error;
        }
    }
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
    <title>Barangay Piggery Monitoring System</title>
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
        <h2 class="text-center" style="font-family: bolder;">Health Monitoring Records</h2>

        <!-- Search -->
        <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">

        <table class="table table-bordered table-striped" style="margin-top:-10px">
            <thead class="text-center" style="font-size: small; font-family: bolder; color:white; background-color:black">
                <tr>
                    <th>#</th>
                    <th>Monitoring ID</th>
                    <th>Piggery ID</th>
                    <th>Check Date</th>
                    <th>Sick Pigs</th>
                    <th>Healthy Pigs</th>
                    <th>Treatments Given</th>
                    <th>Vaccinations</th>
                    <th>Notes</th>
                    <th>Inspected By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="text-center" style="font-size: small">
                <?php
                $counter = 1; // Initialize counter
                if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $row['monitoring_id']; ?></td>
                            <td><?php echo $row['piggery_id']; ?></td>
                            <td><?php echo $row['check_date']; ?></td>
                            <td><?php echo $row['number_of_sick']; ?></td>
                            <td><?php echo $row['number_of_healthy']; ?></td>
                            <td><?php echo $row['treatments_given']; ?></td>
                            <td><?php echo $row['vaccinations']; ?></td>
                            <td><?php echo $row['notes']; ?></td>
                            <td><?php echo $row['inspected_by']; ?></td>
                            <td>
                                <!-- Action Dropdown for each row -->
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#viewModal<?php echo $row['monitoring_id']; ?>">View</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editModal<?php echo $row['monitoring_id']; ?>">Edit</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteModal<?php echo $row['monitoring_id']; ?>">Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo $row['monitoring_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel<?php echo $row['monitoring_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewModalLabel<?php echo $row['monitoring_id']; ?>">View Monitoring Details</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Monitoring ID:</strong> <?php echo $row['monitoring_id']; ?></p>
                                        <p><strong>Piggery ID:</strong> <?php echo $row['piggery_id']; ?></p>
                                        <p><strong>Check Date:</strong> <?php echo $row['check_date']; ?></p>
                                        <p><strong>Sick Pigs:</strong> <?php echo $row['number_of_sick']; ?></p>
                                        <p><strong>Healthy Pigs:</strong> <?php echo $row['number_of_healthy']; ?></p>
                                        <p><strong>Treatments Given:</strong> <?php echo $row['treatments_given']; ?></p>
                                        <p><strong>Vaccinations:</strong> <?php echo $row['vaccinations']; ?></p>
                                        <p><strong>Notes:</strong> <?php echo $row['notes']; ?></p>
                                        <p><strong>Inspected By:</strong> <?php echo $row['inspected_by']; ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $row['monitoring_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $row['monitoring_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $row['monitoring_id']; ?>">Edit Monitoring Details</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="monitoring_id" value="<?php echo $row['monitoring_id']; ?>">

                                            <!-- First Row (Check Date, Number of Sick, Number of Healthy) -->
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="check_date">Check Date</label>
                                                    <input type="date" class="form-control" name="check_date" value="<?php echo $row['check_date']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="number_of_sick">Number of Sick Pigs</label>
                                                    <input type="number" class="form-control" name="number_of_sick" value="<?php echo $row['number_of_sick']; ?>" required>
                                                </div>
                                            </div>

                                            <!-- Second Row (Treatments Given, Vaccinations, Notes, Inspected By) -->
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="number_of_healthy">Number of Healthy Pigs</label>
                                                    <input type="number" class="form-control" name="number_of_healthy" value="<?php echo $row['number_of_healthy']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="treatments_given">Treatments Given</label>
                                                    <input type="text" class="form-control" name="treatments_given" value="<?php echo $row['treatments_given']; ?>" required>
                                                </div>
                                            </div>

                                            <!-- Third Row (Notes, Inspected By) -->
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="vaccinations">Vaccinations</label>
                                                    <input type="text" class="form-control" name="vaccinations" value="<?php echo $row['vaccinations']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="inspected_by">Inspected By</label>
                                                    <input type="text" class="form-control" name="inspected_by" value="<?php echo $row['inspected_by']; ?>" required>
                                                </div>

                                            </div>
                                            <!-- Fourth Row (Notes, Inspected By) -->
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="notes">Notes</label>
                                                    <textarea class="form-control" name="notes" rows="3" required><?php echo $row['notes']; ?></textarea>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" name="edit_monitoring">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $row['monitoring_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $row['monitoring_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $row['monitoring_id']; ?>">Delete Monitoring Record</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="monitoring_id" value="<?php echo $row['monitoring_id']; ?>">
                                            <p>Are you sure you want to delete this record?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger" name="delete_monitoring">Delete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center" style="color:red">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

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