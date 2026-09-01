<?php
ob_start();
// Start session
session_start();
include('../sidebar/Inspector_sidebar.php');
// Database connection setup
include('../connection/conn.php');

// Fetch the current user's data
$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session after login
$query = "SELECT * FROM registered_users WHERE user_id = $user_id";
$result = $admin_conn->query($query);
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture and sanitize form data
    $first_name = $admin_conn->real_escape_string($_POST['first_name']);
    $middle_name = $admin_conn->real_escape_string($_POST['middle_name']);
    $last_name = $admin_conn->real_escape_string($_POST['last_name']);
    $email = $admin_conn->real_escape_string($_POST['email']);
    $username = $admin_conn->real_escape_string($_POST['username']);
    $role = $admin_conn->real_escape_string($_POST['role']);
    $status = $admin_conn->real_escape_string($_POST['status']);

    // Initialize profile_image variable
    $profile_image = $user['profile_image'];

    // Handle file upload
    if ($_FILES['profile_image']['name']) {
        $target_dir = "../Inspector/image/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allow only specific file types
        $allowed_types = ['jpeg', 'jpg', 'png', 'gif'];
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                // Update the profile_image variable with the new file path
                $profile_image = $admin_conn->real_escape_string($target_file);
            } else {
                $_SESSION['toast_message'] = "There was an error uploading your file.";
            }
        } else {
            $_SESSION['toast_message'] = "Only JPEG, PNG, JPG, and GIF files are allowed.";
        }
    }

    // Update user details including profile image
    $update_query = "UPDATE registered_users SET
                   first_name = '$first_name',
                   middle_name = '$middle_name',
                   last_name = '$last_name',
                   email = '$email',
                   username = '$username',
                   role = '$role',
                   status = '$status',
                   profile_image = '$profile_image'
                   WHERE user_id = $user_id";

    if ($admin_conn->query($update_query) === TRUE) {
        $_SESSION['toast_message'] = "Profile updated successfully.";
    } else {
        $_SESSION['toast_message'] = "Error updating profile: " . $admin_conn->error;
    }

    // Refresh the page to show updated data
    header("Location: inspector_profile.php");
    exit;
}

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

    <!-- Custom fonts and styles -->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
</head>
<body>

<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
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
                <h3 class="text-center"> Inspector's Profile Information</h3>

                <form action="inspector_profile.php" method="post" enctype="multipart/form-data" class="mt-4">
                    <div class="text-center mb-4">
                        <?php if ($user['profile_image']): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="rounded-circle" width="125" height="125">
                        <?php else: ?>
                            <img src="../Owner/image/default.png" alt="Profile Image" class="rounded-circle" width="12%" height="15%"> <!-- Default image path -->
                        <?php endif; ?>
                        <div class="mt-2">
                            <input type="file" class="form-control-file" id="profile_image" name="profile_image" accept=".jpeg, .jpg, .png, .gif">

                        </div>
                    </div>

                    <div class="row">
                        <!-- First Row: First Name, Middle Name, Last Name, Email -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Second Row: Username, Role, Status -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                    <option value="owner" <?php if ($user['role'] == 'owner') echo 'selected'; ?>>Owner</option>
                                    <option value="inspector" <?php if ($user['role'] == 'inspector') echo 'selected'; ?>>Inspector</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active" <?php if ($user['status'] == 'active') echo 'selected'; ?>>Active</option>
                                    <option value="inactive" <?php if ($user['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>

        <?php include('../footer.php') ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize and show the toast
                $('.toast').toast('show');
            });
        </script>

        <script src="include/js/jquery-3.3.1.min.js"></script>
        <script src="include/js/bootstrap.min.js"></script>
</body>

</html>