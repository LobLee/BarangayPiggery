<?php
ob_start(); // Start output buffering

// Include database connection and sidebar
include('../connection/user_conn.php');
include('../connection/conn.php');
include('../sidebar/admin_sidebar.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$search = ''; // Initialize search variable

// Handle search query submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_query'])) {
    $search = $admin_conn->real_escape_string($_POST['search_query']);
}

// Get admin's user_id for logging purposes
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Add user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    // Sanitize and store form data
    $first_name = $admin_conn->real_escape_string($_POST['first_name']);
    $middle_name = $admin_conn->real_escape_string($_POST['middle_name']);
    $last_name = $admin_conn->real_escape_string($_POST['last_name']);
    $email = $admin_conn->real_escape_string($_POST['email']);
    $username = $admin_conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $admin_conn->real_escape_string($_POST['role']);
    $status = $admin_conn->real_escape_string($_POST['status']);

    // Insert user data
    $sql = "INSERT INTO registered_users (first_name, middle_name, last_name, email, username, password, role, status) 
            VALUES ('$first_name', '$middle_name', '$last_name', '$email', '$username', '$password', '$role', '$status')";

    if ($admin_conn->query($sql) === TRUE) {
        // Log the add user activity
        $action_type = 'Add User';
        $activity_description = "Added user with username: $username";
        $module = 'User Management';

        $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module) 
                      VALUES ('$current_user_id', '$action_type', '$activity_description', '$module')";
        $admin_conn->query($log_query);

        $_SESSION['toast_message'] = "User added successfully.";
        header("Location: user_management.php");
        exit();
    } else {
        $_SESSION['toast_message'] = "Error adding user: " . $admin_conn->error;
    }
}

// Delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    // Delete user by ID

        // Delete related records in health_monitoring before deleting the user
    $delete_id = intval($_POST['delete_id']);

    // Delete from health_monitoring table
    $sql = "DELETE FROM brgy_user_piggery.health_monitoring WHERE piggery_id = $delete_id";
    $owner_conn->query($sql);

    $delete_id = intval($_POST['delete_id']);
    $sql = "DELETE FROM registered_users WHERE user_id = $delete_id";

    if ($admin_conn->query($sql) === TRUE) {
        // Log the delete user activity
        $action_type = 'Delete User';
        $activity_description = "Deleted user with ID: $delete_id";
        $module = 'User Management';

        $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module) 
                      VALUES ('$current_user_id', '$action_type', '$activity_description', '$module')";
        $admin_conn->query($log_query);

        $_SESSION['toast_message'] = "User deleted successfully.";
        header("Location: user_management.php");
        exit();
    }
}

// Edit user (expand this section to handle actual editing functionality)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    // Example logic for editing user data
    // Add actual user update logic here, then log the action
    $_SESSION['toast_message'] = "User updated successfully."; // Example success message

    // Log the edit user activity
    $edit_id = intval($_POST['edit_id']); // Assume `edit_id` represents the user being edited
    $action_type = 'Edit User';
    $activity_description = "Edited user with ID: $edit_id";
    $module = 'User Management';

    $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module) 
                  VALUES ('$current_user_id', '$action_type', '$activity_description', '$module')";
    $admin_conn->query($log_query);
}

// Modify SQL query based on search input
if (!empty($search)) {
    // If there's a search query, apply it to the SQL query
    $sql = "SELECT * FROM registered_users 
            WHERE first_name LIKE '%$search%' COLLATE utf8mb4_general_ci 
            OR middle_name LIKE '%$search%' COLLATE utf8mb4_general_ci 
            OR last_name LIKE '%$search%' COLLATE utf8mb4_general_ci 
            ORDER BY last_name ASC";
} else {
    // If search query is empty, retrieve all records
    $sql = "SELECT * FROM registered_users ORDER BY last_name ASC";
}

$result = $admin_conn->query($sql);

// Check if results are empty
$noResultsFound = $result->num_rows === 0;
ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Barangay Piggery Monitoring System</title>
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
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

        <h2 class="text-center">User Management</h2>


        <!-- Search Bar -->
        <input type="text" id="searchInput" class="form-control mb-3" style="width: 30%; margin-left: 70%;" placeholder="Search by name...">

        <!-- Add User Button -->
        <button class="btn btn-primary mb-3" style="margin-top: -80px" data-toggle="modal" data-target="#addUserModal">Add New User</button>

        <?php if ($noResultsFound): ?>
            <p>No results found for "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
        <?php else: ?>
            <!-- Table to display users -->
            <table class="table table-bordered table-striped" style = "margin-top:-30px">
                <thead class="text-center" style="font-size: small; font-family: bolder; color:white; background-color:black">
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center" style="font-size: small" id="userTableBody">
                    <?php
                    $counter = 1;
                    $result = $admin_conn->query("SELECT * FROM registered_users ORDER BY last_name ASC");

                    while ($user = $result->fetch_assoc()) {
                        echo "<tr>
                        <td>{$counter}</td>
                        <td>{$user['user_id']}</td>
                        <td>{$user['first_name']}</td>
                        <td>{$user['middle_name']}</td>
                        <td>{$user['last_name']}</td>
                        <td>{$user['email']}</td>
                        <td>{$user['username']}</td>
                        <td>{$user['role']}</td>
                        <td>{$user['status']}</td>
                        <td>
                            <div class='dropdown'>
                                <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                    Actions
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
                                    <a class='dropdown-item editBtn' href='#' data-id='{$user['user_id']}' data-first_name='{$user['first_name']}' data-middle_name='{$user['middle_name']}' data-last_name='{$user['last_name']}' data-email='{$user['email']}' data-username='{$user['username']}' data-role='{$user['role']}' data-status='{$user['status']}'>Edit</a>
                                    <a class='dropdown-item deleteBtn' href='#' data-id='{$user['user_id']}'>Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>";
                        $counter++;
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Add User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="user_management.php" method="POST">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- User Form Fields -->
                            <input type="text" name="first_name" class="form-control" placeholder="First Name" required><br>
                            <input type="text" name="middle_name" class="form-control" placeholder="Middle Name"><br>
                            <input type="text" name="last_name" class="form-control" placeholder="Last Name" required><br>
                            <input type="email" name="email" class="form-control" placeholder="Email"><br>
                            <input type="text" name="username" class="form-control" placeholder="Username" required><br>
                            <input type="password" name="password" class="form-control" placeholder="Password" required><br>
                            <select name="role" class="form-control" required><br>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                                <option value="inspector">Inspector</option>
                            </select><br>
                            <select name="status" class="form-control"><br>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="add_user">Add User</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="user_management.php" method="POST">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- User Form Fields -->
                            <input type="hidden" id="edit_user_id" name="user_id">
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" placeholder="First Name" required><br>
                            <input type="text" id="edit_middle_name" name="middle_name" class="form-control" placeholder="Middle Name"><br>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" placeholder="Last Name" required><br>
                            <input type="email" id="edit_email" name="email" class="form-control" placeholder="Email"><br>
                            <input type="text" id="edit_username" name="username" class="form-control" placeholder="Username" required><br>
                            <select name="role" id="edit_role" class="form-control" required><br>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                                <option value="inspector">Inspector</option>
                            </select><br>
                            <select name="status" id="edit_status" class="form-control"><br>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="edit_user">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete User Modal -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this user?
                    </div>
                    <div class="modal-footer">
                        <form action="user_management.php" method="POST">
                            <input type="hidden" id="delete_user_id" name="delete_id">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" name="delete_user">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../footer.php') ?>
    <script>
        // Edit user
        $('.editBtn').click(function() {
            var userId = $(this).data('id');
            var firstName = $(this).data('first_name');
            var middleName = $(this).data('middle_name');
            var lastName = $(this).data('last_name');
            var email = $(this).data('email');
            var username = $(this).data('username');
            var role = $(this).data('role');
            var status = $(this).data('status');

            $('#edit_user_id').val(userId);
            $('#edit_first_name').val(firstName);
            $('#edit_middle_name').val(middleName);
            $('#edit_last_name').val(lastName);
            $('#edit_email').val(email);
            $('#edit_username').val(username);
            $('#edit_role').val(role);
            $('#edit_status').val(status);

            $('#editUserModal').modal('show');
        });

        // Delete user
        $('.deleteBtn').click(function() {
            var userId = $(this).data('id');
            $('#delete_user_id').val(userId);
            $('#deleteUserModal').modal('show');
        });
    </script>

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