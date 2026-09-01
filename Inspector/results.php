<?php
ob_start();
// Include the inspector sidebar and database connection
include('../sidebar/inspector_sidebar.php');
include('../connection/conn.php');
include('../connection/inspector_conn.php');

// Fetch inspection results where next_inspection_date is in a valid date format, ordered by next_inspection_date
$query = "SELECT * FROM brgy_piggery.manage_piggery 
          WHERE next_inspection_date IS NOT NULL 
          AND DATE(next_inspection_date) = next_inspection_date
          ORDER BY next_inspection_date";
$result = $admin_conn->query($query);

// Check if form data was submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $managed_id = $_POST['manage_id'];
    $compliance_status = $_POST['compliance_status'];
    $health_status = $_POST['health_status'];
    $observations = $_POST['observations'];
    $recommendations = $_POST['recommendations'];

    // Retrieve the next_inspection_date for the given managed_id
    $dateQuery = "SELECT next_inspection_date FROM brgy_piggery.manage_piggery WHERE manage_id = $managed_id";
    $dateResult = $admin_conn->query($dateQuery);

    if ($dateResult->num_rows > 0) {
        $row = $dateResult->fetch_assoc();
        $next_inspection_date = $row['next_inspection_date'];

        // Retrieve a valid inspection_id from inspect_piggeries
        $inspectionIdQuery = "SELECT inspection_id FROM inspect_piggeries WHERE inspection_id = $managed_id LIMIT 1";
        $inspectionIdResult = $inspector_conn->query($inspectionIdQuery);
        $inspection_id = $inspectionIdResult->num_rows > 0 ? $inspectionIdResult->fetch_assoc()['inspection_id'] : null;

        if ($inspection_id) {
            // Update the manage_piggery table with the new inspection data
            $updateQuery = "UPDATE brgy_piggery.manage_piggery 
                            SET compliance_status = '$compliance_status', 
                                health_status = '$health_status', 
                                observations = '$observations', 
                                recommendations = '$recommendations'
                            WHERE manage_id = $managed_id";

            if ($admin_conn->query($updateQuery) === TRUE) {
                echo "<script>alert('Inspection data updated successfully!');</script>";

                // Insert the data into the inspection_results table, using next_inspection_date as the inspection_date
                $insertQuery = "INSERT INTO brgy_inspector_piggery.inspection_results 
                                (inspection_id, compliance_status, health_status, observations, recommendations, inspection_date) 
                                VALUES ($inspection_id, '$compliance_status', '$health_status', '$observations', '$recommendations', '$next_inspection_date')";

                if ($inspector_conn->query($insertQuery) === TRUE) {
                    echo "<script>alert('Inspection data recorded successfully!');</script>";
                } else {
                    echo "Error inserting data into inspection_results: " . $inspector_conn->error;
                }
            } else {
                echo "Error updating data: " . $admin_conn->error;
            }
        } else {
            echo "Error: No valid inspection_id found in inspect_piggeries.";
        }
    } else {
        echo "Error fetching next inspection date: " . $admin_conn->error;
    }

    // Redirect back to the same page
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
    <title>Barangay Piggery Monitoring System - Inspection Results</title>

    <!-- Custom fonts and styles -->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <h2 class="text-center">Inspection Results</h2>

             <!-- Search -->
        <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">


        <table class="table table-bordered table-striped" style="margin-top:-10px">
            <thead class="text-center" style="font-size: small; font-family: bolder; color:white; background-color:black">
                <tr>
                    <th>#</th>
                    <th>Piggery ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Piggery Name</th>
                    <th>Location</th>
                    <th>Compliance Status</th>
                    <th>Health Status</th>
                    <th>Next Inspection Date</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="text-center" style="font-size: small">
                <?php
                $counter = 1;
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $counter; ?></td>
                        <td><?php echo $row['piggery_id']; ?></td>
                        <td><?php echo $row['owner_first_name']; ?></td>
                        <td><?php echo $row['owner_last_name']; ?></td>
                        <td><?php echo $row['piggery_name']; ?></td>
                        <td><?php echo $row['location']; ?></td>
                        <td><?php echo $row['compliance_status']; ?></td>
                        <td><?php echo $row['health_status']; ?></td>
                        <td><?php echo $row['next_inspection_date']; ?></td>
                        <td><?php echo $row['updated_at']; ?></td>
                        <td>
                            <!-- Dropdown Actions -->
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $counter; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $counter; ?>">
                                    <!-- Trigger View Modal -->
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#viewInspectionModal<?php echo $counter; ?>">View</a>
                                    <!-- Trigger Edit Modal -->
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editInspectionModal<?php echo $counter; ?>">Edit</a>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- View Inspection Modal for each record -->
                    <div class="modal fade" id="viewInspectionModal<?php echo $counter; ?>" tabindex="-1" role="dialog" aria-labelledby="viewInspectionModalLabel<?php echo $counter; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewInspectionModalLabel<?php echo $counter; ?>">View Inspection Details</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Piggery ID:</strong> <?php echo $row['piggery_id']; ?></p>
                                    <p><strong>Owner:</strong> <?php echo $row['owner_first_name'] . ' ' . $row['owner_last_name']; ?></p>
                                    <p><strong>Piggery Name:</strong> <?php echo $row['piggery_name']; ?></p>
                                    <p><strong>Location:</strong> <?php echo $row['location']; ?></p>
                                    <p><strong>Compliance Status:</strong> <?php echo $row['compliance_status']; ?></p>
                                    <p><strong>Health Status:</strong> <?php echo $row['health_status']; ?></p>
                                    <p><strong>Next Inspection Date:</strong> <?php echo $row['next_inspection_date']; ?></p>
                                    <p><strong>Last Updated:</strong> <?php echo $row['updated_at']; ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Inspection Modal for each record -->
                    <div class="modal fade" id="editInspectionModal<?php echo $counter; ?>" tabindex="-1" role="dialog" aria-labelledby="editInspectionModalLabel<?php echo $counter; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editInspectionModalLabel<?php echo $counter; ?>">Edit Inspection Result</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="manage_id" value="<?php echo $row['manage_id']; ?>">
                                        <div class="form-group">
                                            <label for="compliance_status">Compliance Status</label>
                                            <input type="text" class="form-control" id="compliance_status" name="compliance_status" value="<?php echo $row['compliance_status']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="health_status">Health Status</label>
                                            <input type="text" class="form-control" id="health_status" name="health_status" value="<?php echo $row['health_status']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="observations">Observations</label>
                                            <textarea class="form-control" id="observations" name="observations" required><?php echo $row['observations']; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="recommendations">Recommendations</label>
                                            <textarea class="form-control" id="recommendations" name="recommendations" required><?php echo $row['recommendations']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php
                    $counter++;
                endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include('../footer.php') ?>
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