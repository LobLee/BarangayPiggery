<?php
ob_start(); // Start output buffering

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
    exit(); // Exit if the user is not logged in
}

$user_id = $_SESSION['user_id']; // Get the user_id from the session

// Fetch all columns of piggeries data from the database using JOIN
$query = "SELECT * FROM health_monitoring WHERE piggery_id = $user_id ORDER BY created_at DESC";
$result = $owner_conn->query($query);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_monitoring'])) {
        // Delete a record
        $monitoring_id = intval($_POST['monitoring_id']);
        $sql = "DELETE FROM health_monitoring WHERE monitoring_id=$monitoring_id";
        $owner_conn->query($sql);
        $_SESSION['toast_message'] = "Record deleted successfully.";
    } elseif (isset($_POST['edit_monitoring'])) {
        // Edit a record
        $monitoring_id = intval($_POST['monitoring_id']);

        $check_date = $_POST['check_date'];
        $number_of_sick = intval($_POST['number_of_sick']);
        $number_of_healthy = intval($_POST['number_of_healthy']);
        $treatments_given = $_POST['treatments_given'];
        $vaccinations = $_POST['vaccinations'];
        $notes = $_POST['notes'];
        $inspected_by = $_POST['inspected_by'];

        $sql = "UPDATE health_monitoring 
                SET check_date='$check_date', number_of_sick=$number_of_sick, 
                    number_of_healthy=$number_of_healthy, treatments_given='$treatments_given', 
                    vaccinations='$vaccinations', notes='$notes', inspected_by='$inspected_by', 
                    updated_at=NOW() 
                WHERE monitoring_id=$monitoring_id";
        $owner_conn->query($sql);
        $_SESSION['toast_message'] = "Record updated successfully.";
    } else {

        $piggery_id = intval($_POST['piggery_id']);
        $check_date = $_POST[('check_date')];
        $number_of_sick = intval($_POST['number_of_sick']);
        $number_of_healthy = intval($_POST['number_of_healthy']);
        $treatments_given = $_POST['treatments_given'];
        $vaccinations = $_POST['vaccinations'];
        $notes = $_POST['notes'];
        $inspector_user_id = $_POST['inspected_by'];

        $inspector_query = "SELECT first_name, last_name FROM registered_users WHERE user_id = $inspector_user_id";
        $inspector_result = $admin_conn->query($inspector_query);

        if ($inspector_result->num_rows > 0) {
            $inspector_row = $inspector_result->fetch_assoc();
            $inspected_by = $inspector_row['first_name'] . '' . $inspector_row['last_name'];
        } else {
            $inspected_by = 'Unknown';
        }
            $sql = "INSERT INTO health_monitoring (piggery_id, check_date, number_of_sick, number_of_healthy,
            treatments_given, vaccinations, notes, inspected_by, created_at, updated_at) VALUES ('$piggery_id',
            '$check_date', '$number_of_sick', '$number_of_healthy', '$treatments_given', '$vaccinations',
            '$notes', '$inspected_by', NOW(), NOW())";
            $owner_conn ->query($sql);
            $_SESSION['toast_message'] = "Record added successfully";
        }
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
        <h2 class="text-center">Health Monitoring Records</h2>

        <!-- Search -->
        <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">

        <!-- Button to trigger Add Record Modal -->
        <button type="button" class="btn btn-primary mb-3" style="margin-top: -80px" data-toggle="modal" data-target="#addRecordModal">
            Add Record
        </button>

        <table class="table table-bordered table-striped" style="margin-top:-30px">
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
                                    <button class="btn btn-secondary dropdown-toggle" style="font-size: smaller;" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                                                <div class="form-group col-md-6">
                                                    <label for="vaccinations">Vaccinations</label>
                                                    <input type="text" class="form-control" name="vaccinations" value="<?php echo $row['vaccinations']; ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="inspected_by">Inspected By</label>
                                                    <input type="text" class="form-control" name="inspected_by" value="<?php echo $row['inspected_by']; ?>" required>
                                                </div>
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


        <!-- Add Record Modal -->
        <div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-labelledby="addRecordModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRecordModalLabel">Add Health Monitoring Record</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="piggery_id">Piggery Name</label>
                                    <select class="form-control" name="piggery_id" required>
                                        <option value="">Select Piggery Name</option>
                                        <?php
                                        // Fetch available piggeries from the database
                                        include('../connection/user_conn.php'); // Ensure connection file is included
                                        $piggery_query = "SELECT piggery_id, piggery_name FROM piggeries";
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

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_date">Check Date</label>
                                        <input type="date" class="form-control" name="check_date" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="number_of_sick">Number of Sick Pigs</label>
                                        <input type="number" class="form-control" name="number_of_sick" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="number_of_healthy">Number of Healthy Pigs</label>
                                        <input type="number" class="form-control" name="number_of_healthy" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="treatments_given">Treatments Given</label>
                                        <input type="text" class="form-control" name="treatments_given">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vaccinations">Vaccinations</label>
                                        <input type="text" class="form-control" name="vaccinations">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="inspected_by">Inspected By</label>
                                <select class="form-control" name="inspected_by" required>
                                    <option value="">Select Inpector</option>
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
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="add_monitoring">Add Record</button>
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