<?php
ob_start(); // Start output buffering

// Include the inspector sidebar for navigation
include('../sidebar/inspector_sidebar.php');
// Include the database connection
include('../connection/conn.php');
include('../connection/inspector_conn.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in.";
    exit(); // Exit if the user is not logged in
}

// Fetch all piggeries data from the manage_piggery table, ordered by created_at DESC
$query = "SELECT * FROM brgy_piggery.manage_piggery ORDER BY created_at DESC";
$piggeriesResult = $admin_conn->query($query);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $manage_id = $_POST['manage_id'] ?? null;
    $next_inspection_date = $_POST['next_inspection_date'] ?? null;

    // Validate that required fields are not empty
    if ($manage_id && $next_inspection_date) {
        // Get full data from manage_piggery table using manage_id
        $query = "SELECT * FROM brgy_piggery.manage_piggery WHERE manage_id = '$manage_id'";
        $result = $admin_conn->query($query);

        if ($result && $result->num_rows > 0) {
            $piggery = $result->fetch_assoc();

            // Extract all necessary data from the fetched row
            $piggery_id = $piggery['piggery_id'];
            $piggery_name = $piggery['piggery_name'];
            $owner_first_name = $piggery['owner_first_name'];
            $owner_middle_name = $piggery['owner_middle_name'];
            $owner_last_name = $piggery['owner_last_name'];
            $location = $piggery['location'];
            $num_of_pigs = $piggery['num_of_pigs'];

            // Set last_inspection_date to current date (or modify as per your logic)
            $last_inspection_date = date('Y-m-d'); // Current date

            // Save inspection record with inspector_id set to user_id from session
            $inspector_id = $_SESSION['user_id'] ?? null;
            $insert_query = "INSERT INTO inspect_piggeries 
                             (piggery_id, inspector_id, last_inspection_date, next_inspection_date, piggery_name, 
                             owner_first_name, owner_middle_name, owner_last_name, location, num_of_pigs) 
                             VALUES 
                             ('$piggery_id', '$inspector_id', '$last_inspection_date', '$next_inspection_date', 
                             '$piggery_name', '$owner_first_name', '$owner_middle_name', '$owner_last_name', 
                             '$location', '$num_of_pigs')";

            if ($inspector_conn->query($insert_query) === TRUE) {
                // Update next_inspection_date in the manage_piggery table
                $update_query = "UPDATE brgy_piggery.manage_piggery 
                                 SET next_inspection_date = '$next_inspection_date' 
                                 WHERE manage_id = '$manage_id'";

                if ($admin_conn->query($update_query) === TRUE) {
                    echo "Inspection and next inspection date successfully recorded.";
                } else {
                    echo "Error updating next inspection date: " . $admin_conn->error;
                }
            } else {
                echo "Error: " . $insert_query . "<br>" . $inspector_conn->error;
            }
        } else {
            echo "Invalid Piggery ID.";
        }
    } else {
        echo "Please fill all required fields.";
    }
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
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Inspect Piggeries - Barangay Piggery Monitoring System</title>

    <!-- Custom fonts and styles -->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <h2 class = "text-center">List of Piggeries</h2>
    
     <!-- Search -->
     <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">


    <!-- Display available piggeries for inspection -->
    <table class="table table-bordered table-striped" id="inspectionTable" style = "margin-top:-10px">
        <thead class = "text-center"  style="font-size: small; font-family: bolder; color:white; background-color:black">
            <tr>
                <th>#</th>
                <th>Piggery ID</th>
                <th>Piggery Name</th>
                <th>Owner First Name</th>
                <th>Owner Middle Name</th>
                <th>Owner Last Name</th>
                <th>Location</th>
                <th>Number of Pigs</th>
                <th>Last Inspection</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody class = "text-center" style="font-size: small">
            <?php 
                $counter = 1;
                while ($inspection = $piggeriesResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $counter; ?></td>
                    <td><?php echo $inspection['piggery_id']; ?></td>
                    <td><?php echo $inspection['piggery_name']; ?></td>
                    <td><?php echo $inspection['owner_first_name']; ?></td>
                    <td><?php echo $inspection['owner_middle_name']; ?></td>
                    <td><?php echo $inspection['owner_last_name']; ?></td>
                    <td><?php echo $inspection['location']; ?></td>
                    <td><?php echo $inspection['num_of_pigs']; ?></td>
                    <td><?php echo $inspection['last_inspection_date']; ?></td>
                    <td>
                        <!-- Button trigger modal -->
                        <button class="btn btn-primary" style="font-size: smaller" data-toggle="modal" data-target="#inspectionModal<?php echo $inspection['manage_id']; ?>">
                            Assign Inspection Date
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="inspectionModal<?php echo $inspection['manage_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="inspectionModalLabel<?php echo $inspection['manage_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="inspectionModalLabel<?php echo $inspection['manage_id']; ?>">Assign Inspection Date</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="inspect_piggery.php" method="POST">
                                        <input type="hidden" name="manage_id" value="<?php echo $inspection['manage_id']; ?>">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="next_inspection_date">Inspection Date</label>
                                                <input type="date" class="form-control" name="next_inspection_date" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Assign Inspection Date</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php 
                    $counter++;
                endwhile;
            ?>
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
