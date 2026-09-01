<?php
ob_start();

// Include the owner sidebar and database connection
include('../sidebar/owner_sidebar.php');
include('../connection/conn.php'); // Connection for the users account
include('../connection/user_conn.php'); // Connection for the user_piggery db

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

// Fetch all columns of incident reports data from the database using JOIN
$query = "SELECT * FROM incident_reports WHERE piggery_id = $user_id ORDER BY created_at DESC";
$result = $owner_conn->query($query);

// Check if the form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_incident'])) {
        // Handle the delete form submission
        $incident_id = intval($_POST['incident_id']);
        $sql = "DELETE FROM incident_reports WHERE incident_id=$incident_id";

        if ($owner_conn->query($sql) === TRUE) {
            $_SESSION['toast_message'] = "Incident report deleted successfully.";
        } else {
            $_SESSION['toast_message'] = "Error deleting record: " . $owner_conn->error;
        }
    } elseif (isset($_POST['edit_incident'])) {
        // Handle the edit form submission
        $incident_id = intval($_POST['incident_id']);
        $report_date = $owner_conn->real_escape_string($_POST['report_date']);
        $reported_by = $owner_conn->real_escape_string($_POST['reported_by']);
        $incident_type = $owner_conn->real_escape_string($_POST['incident_type']);
        $description = $owner_conn->real_escape_string($_POST['description']);
        $status = $owner_conn->real_escape_string($_POST['status']);

        $sql = "UPDATE incident_reports 
                SET report_date='$report_date', reported_by='$reported_by', incident_type='$incident_type', 
                    description='$description', status='$status',
                    updated_at=NOW() 
                WHERE incident_id=$incident_id";

        if ($owner_conn->query($sql) === TRUE) {
            $_SESSION['toast_message'] = "Incident report updated successfully.";
        } else {
            $_SESSION['toast_message'] = "Error updating record: " . $owner_conn->error;
        }
    } else {
        // Handle the add form submission
        $piggery_id = intval($_POST['piggery_id']);
        $report_date = $owner_conn->real_escape_string($_POST['report_date']);
        $reported_by = $owner_conn->real_escape_string($_POST['reported_by']);
        $incident_type = $owner_conn->real_escape_string($_POST['incident_type']);
        $description = $owner_conn->real_escape_string($_POST['description']);
        $status = $owner_conn->real_escape_string($_POST['status']);

        $sql = "INSERT INTO incident_reports (piggery_id, report_date, reported_by, incident_type, description, status, created_at, updated_at)
                VALUES ('$piggery_id','$report_date', '$reported_by', '$incident_type', '$description', '$status', NOW(), NOW())";
        if ($owner_conn->query($sql) === TRUE) {
            $_SESSION['toast_message'] = "Incident report added successfully.";
        } else {
            $_SESSION['toast_message'] = "Error adding record: " . $owner_conn->error;
        }
    }

    // Redirect to the same page to display the toast message
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

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

        <!-- Add Button -->
        <button class="btn btn-primary mb-3" style="margin-top: -80px" data-toggle="modal" data-target="#addIncidentModal">Add Incident</button>
        <!-- Incident Reports Table -->
        <table class="table table-bordered table-striped" style = "margin-top:-30px">
            <thead class = "text-center" style="font-size: small; font-family: bolder; color:white; background-color:black">
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
                            <td><?php echo $counter++; ?></td> <!-- Display counter and increment -->
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
                                    <button class="btn btn-secondary dropdown-toggle" style="font-size: smaller;" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#viewModal<?php echo $incident['incident_id']; ?>">View</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editModal<?php echo $incident['incident_id']; ?>">Edit</a>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteModal<?php echo $incident['incident_id']; ?>">Delete</a>
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
                                        <p><strong>Resolved By:</strong> <?php echo $incident['resolved_by']; ?></p>
                                        <p><strong>Resolved Date:</strong> <?php echo $incident['resolved_date']; ?></p>
                                        <p><strong>Created At:</strong> <?php echo $incident['created_at']; ?></p>
                                        <p><strong>Updated At:</strong> <?php echo $incident['updated_at']; ?></p>
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
                                        <h5 class="modal-title" id="editModalLabel<?php echo $incident['incident_id']; ?>">Edit Incident Details</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="incident_id" value="<?php echo $incident['incident_id']; ?>">

                                            <!-- Row 1 -->
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="report_date">Report Date</label>
                                                    <input type="date" class="form-control" name="report_date" value="<?php echo $incident['report_date']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="reported_by">Reported By</label>
                                                    <input type="text" class="form-control" name="reported_by" value="<?php echo $incident['reported_by']; ?>" required>
                                                </div>
                                            </div>

                                            <!-- Row 2 -->
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="incident_type">Incident Type</label>
                                                    <input type="text" class="form-control" name="incident_type" value="<?php echo $incident['incident_type']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="status">Status</label>
                                                    <input type="text" class="form-control" name="status" value="<?php echo $incident['status']; ?>" required>
                                                </div>    
                                            </div>

                                            <!-- Row 3 -->
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="description">Description</label>
                                                    <textarea class="form-control" name="description" rows="2" required><?php echo $incident['description']; ?></textarea>
                                                </div>                                                                                       
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" name="edit_incident">Save Changes</button>
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
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="incident_id" value="<?php echo $incident['incident_id']; ?>">
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
                        <td colspan="11" class="text-center" style="color:red">No incidents found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Incident Modal -->
    <div class="modal fade" id="addIncidentModal" tabindex="-1" role="dialog" aria-labelledby="addIncidentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addIncidentModalLabel">Add New Incident</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="incident_report.php" method="POST">
                    <div class="modal-body">
                        <!-- Row 1 with two columns -->
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="report_date">Report Date</label>
                                <input type="date" class="form-control" name="report_date" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="reported_by">Reported By</label>
                                <input type="text" class="form-control" name="reported_by" required>
                            </div>
                        </div>

                        <!-- Row 2 with two columns -->
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="incident_type">Incident Type</label>
                                <input type="text" class="form-control" name="incident_type" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="description">Description</label>
                                <textarea class="form-control" name="description" required></textarea>
                            </div>
                        </div>

                        <!-- Row 3 with two columns -->
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="status">Status</label>
                                <input type="text" class="form-control" name="status" value="Pending" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="piggery_id">Piggery Name</label>
                                <select class="form-control" name="piggery_id" required>
                                    <option value="">Select Piggery Name</option>
                                    <?php
                                    // Fetch available piggeries from the database
                                    include('../connection/user_conn.php'); // Ensure connection file is included
                                    $piggery_query = "SELECT piggery_id, piggery_name FROM piggeries WHERE piggery_id=$user_id";
                                    $piggery_result = $owner_conn->query($piggery_query);

                                    if ($piggery_result->num_rows > 0) {
                                        while ($row = $piggery_result->fetch_assoc()) {
                                            echo "<option value='" . $row['piggery_id'] . "'>" . htmlspecialchars($row['piggery_name']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No piggeries available</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_incident">Add Incident</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include('../footer.php') ?>
    <script>
        $(document).ready(function() {
            // Show toast if it exists
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