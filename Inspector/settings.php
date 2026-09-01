<?php
ob_start();
// Start session
session_start();
include('../sidebar/inspector_sidebar.php');
// Database connection setup
include('../connection/conn.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch the current user's data
$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session after login
$query = "SELECT * FROM registered_users WHERE user_id = $user_id";
$result = $admin_conn->query($query);
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Capture and sanitize form data
  $username = $admin_conn->real_escape_string($_POST['username']);
  $password = $admin_conn->real_escape_string($_POST['password']);
  $confirm_password = $admin_conn->real_escape_string($_POST['confirm_password']);

  // Check if passwords match
  if ($password !== $confirm_password) {
    $_SESSION['toast_message'] = "Passwords do not match!";
  } else {
    // Hash password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update only the username and password, leaving other fields intact
    // Make sure other columns not being updated are not affected by the query.
    $update_query = "UPDATE registered_users SET
                     username = '$username',
                     password = '$hashed_password'
                     WHERE user_id = $user_id";

    if ($admin_conn->query($update_query) === TRUE) {
      $_SESSION['toast_message'] = "Profile updated successfully.";
    } else {
      $_SESSION['toast_message'] = "Error updating profile: " . $admin_conn->error;
    }
  }

  // Refresh the page to show updated data
  header("Location: settings.php");
  exit;
}

ob_end_flush();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="include/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <title>Edit Profile</title>
  <style>
    .container-fluid{
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin-top: -100px;
    }

    .form-card {
      width: 100%;
      max-width: 500px;
    }

    .form-row {
      margin-bottom: 15px;
    }

    .eye-icon {
      cursor: pointer;
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="card form-card">
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

        <h3 class="text-center">Update Credentials</h3>

        <form action="settings.php" method="post">
          <div class="form-row">
            <div class="col-12">
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly required>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="col-6">
              <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" name="password" required>
                  <div class="input-group-append">
                    <span class="input-group-text eye-icon" id="toggle-password">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                  <div class="input-group-append">
                    <span class="input-group-text eye-icon" id="toggle-confirm-password">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-primary mt-6">Update Profile</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <?php include('../footer.php') ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle password visibility
      const togglePassword = document.getElementById('toggle-password');
      const passwordField = document.getElementById('password');

      togglePassword.addEventListener('click', function() {
        // Toggle password visibility
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;

        // Toggle eye icon
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
      });

      // Toggle confirm password visibility
      const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
      const confirmPasswordField = document.getElementById('confirm_password');

      toggleConfirmPassword.addEventListener('click', function() {
        // Toggle confirm password visibility
        const type = confirmPasswordField.type === 'password' ? 'text' : 'password';
        confirmPasswordField.type = type;

        // Toggle eye icon
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
      });

      // Initialize and show the toast
      $('.toast').toast('show');
    });
  </script>

  <script src="include/js/jquery-3.3.1.min.js"></script>
  <script src="include/js/bootstrap.min.js"></script>
</body>

</html>
