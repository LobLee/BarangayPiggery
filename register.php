<?php
// Start session
session_start();

// Database connection setup
include('connection/conn.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Capture and sanitize form data
  $first_name = $admin_conn->real_escape_string($_POST['first_name']);
  $middle_name = $admin_conn->real_escape_string($_POST['middle_name']);
  $last_name = $admin_conn->real_escape_string($_POST['last_name']);
  $email = $admin_conn->real_escape_string($_POST['email']);
  $username = $admin_conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];  // Store the plain password temporarily for validation
  $role = $admin_conn->real_escape_string($_POST['role']);
  $status = $admin_conn->real_escape_string($_POST['status']);

  // Basic validation (ensure required fields are filled)
  if (empty($first_name) || empty($last_name) || empty($username) || empty($password) || empty($role)) {
    $_SESSION['message'] = "Please fill in all required fields.";
    header("Location: register.php");
    exit;
  }

  // Check if username or email already exists
  $check_user_query = "SELECT * FROM registered_users WHERE username = '$username' OR email = '$email'";
  $result = $admin_conn->query($check_user_query);

  if ($result->num_rows > 0) {
    $_SESSION['message'] = "Username or Email already exists. Please choose another.";
    header("Location: register.php");
    exit;
  } else {
    // Hash the password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into database
    $insert_query = "INSERT INTO registered_users (first_name, middle_name, last_name, email, username, password, role, status)
                         VALUES ('$first_name', '$middle_name', '$last_name', '$email', '$username', '$hashed_password', '$role', '$status')";

    if ($admin_conn->query($insert_query) === TRUE) {
      $_SESSION['message'] = "Registration successful.";
      header("Location: register.php");
      exit;
    } else {
      $_SESSION['message'] = "Error: " . $admin_conn->error;
      header("Location: register.php");
      exit;
    }
  }
}

// Close database connection
$admin_conn->close();
?>


<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="include/fonts/icomoon/style.css">
  <link rel="stylesheet" href="include/css/owl.carousel.min.css">
  <link rel="stylesheet" href="include/css/bootstrap.min.css">
  <link rel="stylesheet" href="include/css/style.css">

  <title>Barangay Piggery Monitoring System - Registration</title>
</head>

<body>
  <div class="content">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <img src="include/images/undraw_remotely_2j6y.svg" alt="Image" class="img-fluid">
        </div>
        <div class="col-md-6 contents">
          <div class="row justify-content-center">
            <div class="col-md-8">
              <div class="mb-4">
                <h3>Register</h3>
                <?php if (isset($_SESSION['message'])): ?>
                  <div class="alert alert-info"><?php echo $_SESSION['message'];
                                                unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                <p class="mb-4"> Fill in details to create a new account.</p>
              </div>
              <form action="register.php" method="post">
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" required>
                  </div>

                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="username">User Name</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="email">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <h6>Role</h6>
                    <label for="role"></label>
                    <select class="form-control" id="role" name="role" required>
                      <option value="admin"></option>
                      <option value="owner"></option>
                      <option value="inspector"></option>
                    </select>
                  </div>
                  <div class="form-group col-md-6">
                    <h6>Status</h6>
                    <label for="status"></label>
                    <select class="form-control" id="status" name="status" required>
                      <option value="active"></option>
                      <option value="inactive"></option>
                    </select>
                  </div>
                </div>

                <input type="submit" value="Register" class="btn btn-block btn-primary">
                <div class="text-center mt-2">
                  <label for="login">Already have an account?</label><a href="index.php" class="text-decoration" style="color: blue"> Login</a>

                </div>
              </form>
            </div>

          </div>

        </div>
      </div>

    </div>
  </div>

  </div>

  <script src="include/js/jquery-3.3.1.min.js"></script>
  <script src="include/js/popper.min.js"></script>
  <script src="include/js/bootstrap.min.js"></script>
  <script src="include/js/main.js"></script>
</body>

</html>