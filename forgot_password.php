<?php
include('connection/conn.php');
session_start();

$modal_message = ""; // Variable to store the modal message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize inputs
  $username = mysqli_real_escape_string($admin_conn, $_POST['username']);
  $new_password = mysqli_real_escape_string($admin_conn, $_POST['new_password']);
  $confirm_password = mysqli_real_escape_string($admin_conn, $_POST['confirm_password']);

  // Check if passwords match
  if ($new_password !== $confirm_password) {
    $modal_message = "Passwords do not match. Please try again.";
  } else {
    // Check if user exists
    $query = "SELECT * FROM registered_users WHERE username='$username'";
    $result = $admin_conn->query($query);

    if ($result && $result->num_rows > 0) {
      // Hash the new password
      $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

      // Update the password in the database
      $update_query = "UPDATE registered_users SET password='$hashed_password' WHERE username='$username'";
      if ($admin_conn->query($update_query) === TRUE) {
        $modal_message = "Password has been successfully reset.";
      } else {
        $modal_message = "An error occurred while updating the password. Please try again.";
      }
    } else {
      $modal_message = "No account found with that username.";
    }
  }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="include/fonts/icomoon/style.css">
  <link rel="stylesheet" href="include/css/owl.carousel.min.css">
  <link rel="stylesheet" href="include/css/bootstrap.min.css">
  <link rel="stylesheet" href="include/css/style.css">
  <title>Reset Password - Barangay Piggery Monitoring System</title>

  <style>
    .form-control::placeholder {
      color: #999;
      transition: color 0.3s ease;
    }
    .form-control:focus::placeholder {
      color: transparent;
    }
  </style>
</head>

<body>
<div class="content">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <img src="include/images/undraw_remotely_2j6y.svg" alt="Reset Password Image" class="img-fluid">
      </div>
      <div class="col-md-6 contents">
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="mb-4">
              <h3>Reset Password</h3>
              <p class="mb-4">Enter your username and new password below to reset your password.</p>
            </div>
            <form action="forgot_password.php" method="post">
              <div class="form-group first">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
              </div>
              <div class="form-group last mb-4">
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
              </div>
              <div class="form-group last mb-4">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
              </div>

              <input type="submit" value="Reset Password" class="btn btn-block btn-primary">
              <div class="text-center mt-2">
              <label>Already have an Account? <a href="index.php" class="login" style="color:blue">Click Here</a></label>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Modal for Alerts -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alertModalLabel">Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo htmlspecialchars($modal_message); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <?php if ($modal_message === "Password has been successfully reset.") : ?>
          <a href="index.php" class="btn btn-primary">Go to Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="include/js/jquery-3.3.1.min.js"></script>
<script src="include/js/popper.min.js"></script>
<script src="include/js/bootstrap.min.js"></script>
<script src="include/js/main.js"></script>

<?php if (!empty($modal_message)) : ?>
<script>
  $(document).ready(function(){
    $('#alertModal').modal('show');
  });
</script>
<?php endif; ?>

</body>
</html>
