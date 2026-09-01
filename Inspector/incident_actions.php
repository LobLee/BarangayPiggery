<?php
ob_start();
include('../sidebar/inspector_sidebar.php');
include('../connection/conn.php'); // Main database connection
include('../connection/inspector_conn.php'); // brgy_user_piggery database connection

$loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch approved incidents with inspector details
$query = "SELECT mi.*, u.first_name AS inspector_first_name, u.last_name AS inspector_last_name 
          FROM manage_incidents mi
          LEFT JOIN registered_users u ON mi.inspector_id = u.user_id 
          WHERE mi.status = 'Approved' AND mi.inspector_id = '$user_id'
          ORDER BY mi.created_at DESC";
$result = $admin_conn->query($query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_incident'])) {
        $incident_id = intval($_POST['incident_id']);
        $action_taken = $admin_conn->real_escape_string($_POST['action_taken']);
        $resolved_by = intval($_POST['resolved_by']); // Ensure resolved_by is an integer

        // Fetch piggery_id from manage_incidents or registered_users for foreign key in incident_reports
        $piggery_query = "SELECT piggery_id FROM manage_incidents WHERE incident_id = $incident_id";
        $piggery_result = $admin_conn->query($piggery_query);

        if ($piggery_result->num_rows > 0) {
            $piggery_data = $piggery_result->fetch_assoc();
            $piggery_id = $piggery_data['piggery_id']; // Use this piggery_id for foreign key reference
        } else {
            $_SESSION['toast_message'] = "Error: No matching piggery_id found.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        // Check if the action already exists in incident_actions
        $check_sql = "SELECT * FROM incident_actions 
                      WHERE incident_id = $incident_id AND action_taken = '$action_taken' AND resolved_by = '$resolved_by'";
        $check_result = $inspector_conn->query($check_sql);

        if ($check_result->num_rows == 0) {
            // Insert into incident_actions table
            $insert_sql = "INSERT INTO incident_actions (incident_id, action_taken, resolved_by, resolved_date) 
                           VALUES ($incident_id, '$action_taken', '$resolved_by', NOW())";
            if ($inspector_conn->query($insert_sql) === TRUE) {
                $_SESSION['toast_message'] = "New action added successfully.";
            } else {
                $_SESSION['toast_message'] = "Error adding action: " . $inspector_conn->error;
            }
        } else {
            $_SESSION['toast_message'] = "Action already exists.";
        }

        // Update the manage_incidents table
        $update_incident_sql = "UPDATE manage_incidents 
                                SET action_taken='$action_taken', 
                                    resolved_by='$resolved_by', 
                                    resolved_date=NOW() 
                                WHERE incident_id=$incident_id";
        if ($admin_conn->query($update_incident_sql) === TRUE) {
            $_SESSION['toast_message'] .= " Incident report updated successfully.";
        } else {
            $_SESSION['toast_message'] .= " Error updating record: " . $admin_conn->error;
        }

        // Insert or update action details in incident_reports
        $check_incident_report_sql = "SELECT ir.incident_id 
                                      FROM brgy_user_piggery.incident_reports ir 
                                      WHERE ir.incident_id = $incident_id";
        $check_report_result = $inspector_conn->query($check_incident_report_sql);

        if ($check_report_result->num_rows > 0) {
            // If record exists, update it
            $update_incident_reports_sql = "UPDATE brgy_user_piggery.incident_reports 
                                            SET action_taken = '$action_taken', 
                                                resolved_by = '$resolved_by', 
                                                resolved_date = NOW(), 
                                                piggery_id = $piggery_id
                                            WHERE incident_id = $incident_id";
            if ($inspector_conn->query($update_incident_reports_sql) === TRUE) {
                $_SESSION['toast_message'] .= " Incident report in incident_reports updated successfully.";
            } else {
                $_SESSION['toast_message'] .= " Error updating incident report: " . $inspector_conn->error;
            }
        } else {
            // If record doesn't exist, insert a new record
            $insert_incident_reports_sql = "INSERT INTO brgy_user_piggery.incident_reports (incident_id, action_taken, resolved_by, resolved_date, piggery_id) 
                                            VALUES ($incident_id, '$action_taken', '$resolved_by', NOW(), $piggery_id)";
            if ($inspector_conn->query($insert_incident_reports_sql) === TRUE) {
                $_SESSION['toast_message'] .= " New incident report added to incident_reports successfully.";
            } else {
                $_SESSION['toast_message'] .= " Error adding new incident report: " . $inspector_conn->error;
            }
        }
    } elseif (isset($_POST['delete_incident'])) {
        $incident_id = intval($_POST['incident_id']);
        $sql = "DELETE FROM incident_actions WHERE action_id=$incident_id";

        if ($inspector_conn->query($sql) === TRUE) {
            $_SESSION['toast_message'] = "Incident action deleted successfully.";
        } else {
            $_SESSION['toast_message'] = "Error deleting record: " . $inspector_conn->error;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$inspector_conn->close();
ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>All Piggeries Details - Barangay Piggery Monitoring System</title>
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
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
        <h2 class="text-center">Approved Incident Reports</h2>

           <!-- Search -->
           <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">

        <table class="table table-bordered table-striped" style="margin-top:-10px">
            <thead class = "text-center"  style="font-size: small; font-family: bolder; color:white; background-color:black">
                <tr>
                    <th>#</th>
                    <th>Piggery ID</th>
                    <th>Reported By</th>
                    <th>Incident Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action Taken</th>
                    <th>Resolved By</th>
                    <th>Resolved Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class = "text-center" style="font-size: small">
                <?php
                $counter = 1;
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $resolved_by_id = intval($row['resolved_by']);
                        $inspector_query = "SELECT first_name, last_name FROM registered_users WHERE user_id = $resolved_by_id";
                        $inspector_result = $admin_conn->query($inspector_query);

                        if ($inspector_result && $inspector_result->num_rows > 0) {
                            $inspector = $inspector_result->fetch_assoc();
                            $resolved_by_name = $inspector['first_name'] . ' ' . $inspector['last_name'];
                        } else {
                            $resolved_by_name = "";
                        }
                ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $row['piggery_id']; ?></td>
                            <td><?php echo $row['reported_by']; ?></td>
                            <td><?php echo $row['incident_type']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td><?php echo $row['action_taken']; ?></td>
                            <td><?php echo $resolved_by_name; ?></td>
                            <td><?php echo $row['resolved_date']; ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editModal<?php echo $row['incident_id']; ?>">Edit</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteModal<?php echo $row['incident_id']; ?>">Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Modals -->
                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo $row['incident_id']; ?>" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">View Incident</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Reported by:</strong> <?php echo $row['reported_by']; ?></p>
                                        <p><strong>Incident Type:</strong> <?php echo $row['incident_type']; ?></p>
                                        <p><strong>Description:</strong> <?php echo $row['description']; ?></p>
                                        <p><strong>Status:</strong> <?php echo $row['status']; ?></p>
                                        <p><strong>Action Taken:</strong> <?php echo $row['action_taken']; ?></p>
                                        <p><strong>Resolved By:</strong> <?php echo $resolved_by_name; ?></p>
                                        <p><strong>Resolved Date:</strong> <?php echo $row['resolved_date']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $row['incident_id']; ?>" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Incident</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="incident_id" value="<?php echo $row['incident_id']; ?>">
                                            <div class="form-group">
                                                <label for="action_taken">Action Taken:</label>
                                                <textarea name="action_taken" class="form-control" required><?php echo $row['action_taken']; ?></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="resolved_by">Resolved By:</label>
                                                <input type="text" name="resolved_by" class="form-control" value="<?php echo $loggedInUserId; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="edit_incident" class="btn btn-primary">Save Changes</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $row['incident_id']; ?>" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Incident</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="incident_id" value="<?php echo $row['incident_id']; ?>">
                                            <p>Are you sure you want to delete this incident?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="delete_incident" class="btn btn-danger">Delete</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Modals -->
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="10" class="text-center" style="color:red">No results found.</td>
                    </tr>
                <?php } ?>
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